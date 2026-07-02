<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PhotosManager extends Component
{
    use WithFileUploads;

    // Image upload properties
    public $images = [];
    public $existingImages = [];

    // Video upload properties
    public $video = null;
    public $existingVideo = null;

    // Profile state
    public $hasProfile = false;

    // Main photo (first photo or selected main)
    public $mainPhoto = null;

    // Other photos (all except main)
    public $otherPhotos = [];

    // Verification status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    public function mount()
    {
        // Get user with profile relationship loaded
        $user = \App\Models\User::with('profile')->find(Auth::id());
        $profile = $user->profile;

        // Set profile state
        $this->hasProfile = !is_null($profile);

        // Load existing images if profile exists
        if ($profile) {
            $this->loadImages($profile);
            $this->loadVideo($profile);
        }
    }

    protected function loadImages($profile)
    {
        $allImages = $profile->getMedia('profile-images');
        $this->existingImages = $allImages;

        // Find main photo (marked as main or first image)
        $this->mainPhoto = $allImages->first(function ($media) {
            return $media->getCustomProperty('is_main', false);
        }) ?? $allImages->first();

        // Other photos are all except main
        $this->otherPhotos = $allImages->filter(function ($media) {
            return $this->mainPhoto ? $media->id !== $this->mainPhoto->id : true;
        });
    }

    public function setAsMainPhoto($mediaId)
    {
        $user = Auth::user();
        if (!$user->profile) {
            return;
        }

        // Remove main flag from all photos
        $user->profile->getMedia('profile-images')->each(function ($media) {
            $media->setCustomProperty('is_main', false);
            $media->save();
        });

        // Set new main photo
        $media = $user->profile->getMedia('profile-images')->where('id', $mediaId)->first();
        if ($media) {
            $media->setCustomProperty('is_main', true);
            $media->save();
        }

        // Reload images
        $this->loadImages($user->profile->fresh());
        session()->flash('message', __('front.profiles.photos.main_set'));
    }

    public function requestVerification()
    {
        $user = Auth::user();
        if (!$user->profile || !$this->mainPhoto) {
            return;
        }

        // Check if main photo is already verified or pending
        $currentStatus = $this->mainPhoto->getCustomProperty('verification_status');
        if ($currentStatus === self::STATUS_VERIFIED) {
            session()->flash('info', __('front.profiles.photos.already_verified'));
            return;
        }

        if ($currentStatus === self::STATUS_PENDING) {
            session()->flash('info', __('front.profiles.photos.verification_pending'));
            return;
        }

        // Set verification status to pending
        $this->mainPhoto->setCustomProperty('verification_status', self::STATUS_PENDING);
        $this->mainPhoto->setCustomProperty('verification_requested_at', now()->toISOString());
        $this->mainPhoto->save();

        // Reload images
        $this->loadImages($user->profile->fresh());
        session()->flash('message', __('front.profiles.photos.verification_requested'));
    }

    public function getVerificationStatus(): ?string
    {
        if (!$this->mainPhoto) {
            return null;
        }
        return $this->mainPhoto->getCustomProperty('verification_status');
    }

    public function isProfileVerified(): bool
    {
        return $this->getVerificationStatus() === self::STATUS_VERIFIED;
    }

    public function isVerificationPending(): bool
    {
        return $this->getVerificationStatus() === self::STATUS_PENDING;
    }

    public function isVerificationRejected(): bool
    {
        return $this->getVerificationStatus() === self::STATUS_REJECTED;
    }

    public function removeExistingImage($mediaId)
    {
        $user = Auth::user();
        if ($user->profile) {
            $media = $user->profile->getMedia('profile-images')->where('id', $mediaId)->first();
            if ($media) {
                $media->delete();
                // Refresh existing images
                $this->loadImages($user->profile->fresh());
                session()->flash('message', __('front.profiles.photos.deleted'));
            }
        }
    }

    public function removeUploadedImage($index)
    {
        if (isset($this->images[$index])) {
            unset($this->images[$index]);
            $this->images = array_values($this->images); // Reindex array
        }
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*' => 'image|max:20480', // Max 20MB per image
        ]);

        $user = Auth::user();
        
        // Profile must exist (enforced by middleware)
        if (!$user->profile) {
            return;
        }

        $profile = $user->profile;

        // Handle image uploads
        if (!empty($this->images)) {
            $isFirst = $profile->getMedia('profile-images')->isEmpty();
            
            foreach ($this->images as $index => $image) {
                if ($image instanceof UploadedFile) {
                    // Add each file individually to the media collection
                    $media = $profile->addMedia($image->path())
                        ->usingName($image->getClientOriginalName())
                        ->usingFileName($image->hashName())
                        ->withCustomProperties([
                            'verification_status' => null,
                            'is_main' => $isFirst && $index === 0,
                        ])
                        ->toMediaCollection('profile-images');
                }
            }
            
            // Clear uploaded images after saving
            $this->images = [];
            
            // Refresh existing images
            $this->loadImages($profile->fresh());
            
            session()->flash('message', __('front.profiles.photos.success'));
        }
    }

    protected function loadVideo($profile)
    {
        $this->existingVideo = $profile->getFirstMedia('profile-video');
    }

    public function uploadVideo()
    {
        $this->validate([
            'video' => 'required|mimes:mp4,webm,mov|max:153600', // Max 150MB
        ], [
            'video.max' => __('front.profiles.photos.video_too_large'),
            'video.mimes' => __('front.profiles.photos.video_invalid_format'),
            'video.required' => __('front.profiles.photos.video_required'),
        ]);

        $user = Auth::user();
        
        // Profile must exist (enforced by middleware)
        if (!$user->profile) {
            return;
        }

        $profile = $user->profile;

        // Remove existing video first (singleFile collection handles this, but let's be explicit)
        $profile->clearMediaCollection('profile-video');

        // Add the new video
        $profile->addMedia($this->video->path())
            ->usingName($this->video->getClientOriginalName())
            ->usingFileName($this->video->hashName())
            ->toMediaCollection('profile-video');

        // Clear uploaded video after saving
        $this->video = null;
        
        // Refresh existing video
        $this->loadVideo($profile->fresh());
        
        session()->flash('message', __('front.profiles.photos.video_success'));
    }

    public function removeVideo()
    {
        $user = Auth::user();
        if ($user->profile) {
            $user->profile->clearMediaCollection('profile-video');
            $this->existingVideo = null;
            session()->flash('message', __('front.profiles.photos.video_deleted'));
        }
    }

    public function render()
    {
        return view('livewire.photos-manager');
    }
}