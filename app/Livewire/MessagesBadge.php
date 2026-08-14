<?php

namespace App\Livewire;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * The unread-message counter in the header.
 *
 * The navbar printed a literal "654" here, on desktop and in the mobile menu,
 * regardless of the account or how many messages it actually had. The real
 * number was always one query away — MessageController@inbox already computes
 * the same per-conversation figure.
 */
class MessagesBadge extends Component
{
    /**
     * Rendered inside the mobile menu list instead of as a standalone button.
     */
    public bool $inline = false;

    public function mount(bool $inline = false): void
    {
        $this->inline = $inline;
    }

    public function getUnreadCountProperty(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            return 0;
        }

        // Message uses SoftDeletes, so deleted threads are already excluded.
        return Message::query()
            ->where('to_user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function render()
    {
        return view('livewire.messages-badge');
    }
}
