<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Throwable;

class PrivatizeProfileMedia extends Command
{
    protected $signature = 'media:privatize-profiles
        {--id=* : Limit to these media IDs}
        {--dry-run : Verify available files without changing files or database rows}
        {--stage : Copy and update references but retain public files for other database copies}';

    protected $description = 'Move profile media and conversions out of public storage, verifying copies before cleanup';

    public function handle(): int
    {
        $ids = $this->option('id');
        foreach ($ids as $id) {
            if (! ctype_digit((string) $id) || (int) $id < 1) {
                $this->error('--id must contain positive numeric media IDs.');

                return self::FAILURE;
            }
        }

        $failed = 0;
        $count = 0;
        Media::query()
            ->whereIn('model_type', array_unique([Profile::class, (new Profile)->getMorphClass()]))
            ->when($ids, fn ($query) => $query->whereIn('id', $ids))
            ->orderBy('id')
            ->chunkById(100, function ($items) use (&$failed, &$count): void {
                foreach ($items as $media) {
                    $count++;
                    try {
                        $this->migrate($media);
                        $this->line("Media {$media->id}: verified.");
                    } catch (Throwable $exception) {
                        $failed++;
                        $this->error("Media {$media->id}: {$exception->getMessage()}");
                    }
                }
            });

        if ($this->option('stage')) {
            $this->warn('Staged only: public files remain exposed through static /storage URLs. Privacy migration is NOT complete.');
        }

        if ($failed > 0) {
            $this->error("{$failed} of {$count} media records incomplete. Do not treat public storage as closed; correct the failures and rerun.");

            return self::FAILURE;
        }

        $this->info($this->option('dry-run') ? "Verified {$count} records; no changes made." : "Processed {$count} profile media records.");

        return self::SUCCESS;
    }

    private function migrate(Media $media): void
    {
        $private = Storage::disk('profile-media');
        $public = Storage::disk('public');
        $generator = PathGeneratorFactory::create($media);

        // A custom shared directory cannot be safely swept per record.
        if (! $generator instanceof DefaultPathGenerator) {
            throw new RuntimeException('Custom path generator requires a separate migration; public files were not removed.');
        }

        $directory = trim($generator->getPath($media), '/');
        $segments = explode('/', $directory);
        if (in_array('..', $segments, true) || in_array('.', $segments, true) || end($segments) !== (string) $media->id) {
            throw new RuntimeException('Unsafe media directory; no files changed.');
        }
        if (! in_array($media->disk, ['public', 'profile-media'], true)
            || ! in_array($media->conversions_disk ?: $media->disk, ['public', 'profile-media'], true)) {
            throw new RuntimeException('Unsupported source disk; migrate it separately before public cleanup.');
        }

        $required = [$media->getPathRelativeToRoot()];
        foreach ($media->generated_conversions ?? [] as $conversion => $generated) {
            if ($generated) {
                $required[] = $media->getPathRelativeToRoot($conversion);
            }
        }
        $files = $public->allFiles($directory);

        foreach ($required as $file) {
            if (! $public->exists($file) && ! $private->exists($file)) {
                throw new RuntimeException("Required file missing: {$file}; source files retained.");
            }
        }
        $original = $required[0];
        $originalDisk = $public->exists($original) ? $public : $private;
        if ($originalDisk->size($original) !== (int) $media->size) {
            throw new RuntimeException('Original size differs from media metadata; source files retained.');
        }

        foreach ($files as $file) {
            $sourceHash = $this->digest($public, $file);
            if ($private->exists($file)) {
                if ($sourceHash !== $this->digest($private, $file)) {
                    throw new RuntimeException("Private destination conflict: {$file}; both copies retained.");
                }
                continue;
            }
            if ($this->option('dry-run')) {
                continue;
            }

            $stream = $public->readStream($file);
            if (! is_resource($stream)) {
                throw new RuntimeException("Cannot read {$file}; source retained.");
            }
            try {
                if (! $private->put($file, $stream, ['visibility' => 'private'])
                    || $this->digest($private, $file) !== $sourceHash) {
                    throw new RuntimeException("Copy verification failed: {$file}; source retained.");
                }
            } catch (Throwable $exception) {
                // This path did not exist beforehand; removing an incomplete
                // copy lets a retry start cleanly without touching the source.
                $private->delete($file);
                throw $exception;
            } finally {
                fclose($stream);
            }
        }

        if ($this->option('dry-run')) {
            return;
        }

        foreach ($required as $file) {
            if (! $private->exists($file)) {
                throw new RuntimeException("Private file missing after copy: {$file}; source retained.");
            }
        }

        DB::transaction(function () use ($media): void {
            $current = Media::query()->lockForUpdate()->findOrFail($media->id);
            if ($current->disk !== $media->disk || $current->conversions_disk !== $media->conversions_disk) {
                throw new RuntimeException('Media changed during migration; public files retained.');
            }
            $current->timestamps = false;
            $current->forceFill(['disk' => 'profile-media', 'conversions_disk' => 'profile-media'])->saveQuietly();
        });

        if ($this->option('stage')) {
            return;
        }

        // Re-read the list and hashes before deleting anything, including
        // responsive files or conversions created since the first listing.
        $files = $public->allFiles($directory);
        foreach ($files as $file) {
            if (! $private->exists($file) || $this->digest($public, $file) !== $this->digest($private, $file)) {
                throw new RuntimeException("Public cleanup incomplete: {$file} is not verified privately; rerun with uploads/queues paused.");
            }
        }
        // Keep the original until conversions have been cleaned successfully.
        usort($files, fn ($a, $b) => ($a === $original) <=> ($b === $original));
        foreach ($files as $file) {
            if (! $public->delete($file) || $public->exists($file)) {
                throw new RuntimeException("Public cleanup incomplete: {$file}; verified private copy remains recoverable.");
            }
        }
        if ($public->allFiles($directory) !== []) {
            throw new RuntimeException('Public files appeared during cleanup; pause uploads/queues and rerun.');
        }
    }

    private function digest(FilesystemAdapter $disk, string $file): string
    {
        $stream = $disk->readStream($file);
        if (! is_resource($stream)) {
            throw new RuntimeException("Cannot verify {$file}.");
        }
        try {
            $hash = hash_init('sha256');
            if (hash_update_stream($hash, $stream) !== $disk->size($file)) {
                throw new RuntimeException("Incomplete read while verifying {$file}.");
            }

            return hash_final($hash);
        } finally {
            fclose($stream);
        }
    }
}
