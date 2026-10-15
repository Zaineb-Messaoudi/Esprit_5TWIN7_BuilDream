<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Chat message for rental communication.
 */
class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat_messages';

    protected $fillable = [
        'rental_id',
        'sender_id',
        'message',
        'attachments',
        'type',
        'read_at',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'read_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    /** The rental this message belongs to. */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /** The user who sent the message. */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** Check if message is read. */
    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }

    /** Check if message was edited. */
    public function isEdited(): bool
    {
        return ! is_null($this->edited_at);
    }

    /** Check if message has attachments. */
    public function hasAttachments(): bool
    {
        return ! empty($this->attachments);
    }

    /** Scope for unread messages. */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /** Scope for messages in a rental. */
    public function scopeForRental($query, int $rentalId)
    {
        return $query->where('rental_id', $rentalId);
    }
}
