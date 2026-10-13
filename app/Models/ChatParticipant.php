<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Chat participant for rental chat rooms.
 */
class ChatParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat_participants';

    protected $fillable = [
        'rental_id',
        'user_id',
        'last_read_at',
        'muted',
        'muted_until',
    ];

    protected function casts(): array
    {
        return [
            'muted' => 'boolean',
            'muted_until' => 'datetime',
            'last_read_at' => 'datetime',
        ];
    }

    /** The rental this participant is in. */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /** The user who is a participant. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Check if participant is muted. */
    public function isMuted(): bool
    {
        if (! $this->muted) {
            return false;
        }

        if ($this->muted_until && $this->muted_until < now()) {
            return false;
        }

        return true;
    }

    /** Check if user has unread messages. */
    public function hasUnreadMessages(): bool
    {
        if (! $this->last_read_at) {
            return true;
        }

        return \App\Models\ChatMessage::where('rental_id', $this->rental_id)
            ->where('sender_id', '!=', $this->user_id)
            ->where('created_at', '>', $this->last_read_at)
            ->exists();
    }

    /** Mark messages as read up to a certain point. */
    public function markAsRead(?\Illuminate\Support\Carbon $timestamp = null): void
    {
        $this->update([
            'last_read_at' => $timestamp ?? now(),
        ]);
    }

    /** Mute the chat. */
    public function mute(?\Illuminate\Support\Carbon $until = null): void
    {
        $this->update([
            'muted' => true,
            'muted_until' => $until,
        ]);
    }

    /** Unmute the chat. */
    public function unmute(): void
    {
        $this->update([
            'muted' => false,
            'muted_until' => null,
        ]);
    }
}
