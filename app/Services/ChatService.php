<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function __construct(private readonly RealtimeService $realtime) {}

    /**
     * Get or create chat participants for a rental.
     */
    public function getOrCreateParticipants(Rental $rental): array
    {
        $participants = [];

        foreach ([$rental->user_id, $rental->equipment->owner_id] as $userId) {
            $participant = ChatParticipant::firstOrCreate([
                'rental_id' => $rental->id,
                'user_id' => $userId,
            ]);
            $participants[] = $participant;
        }

        return $participants;
    }

    /**
     * Send a message in a rental chat.
     */
    public function sendMessage(
        Rental $rental,
        User $sender,
        string $message,
        string $type = 'text',
        ?array $attachments = null
    ): ChatMessage {
        // Verify sender is part of the rental
        abort_unless(
            in_array($sender->id, [$rental->user_id, $rental->equipment->owner_id]),
            403,
            'Only rental participants can send messages.'
        );

        return DB::transaction(function () use ($rental, $sender, $message, $type, $attachments) {
            $message = ChatMessage::create([
                'rental_id' => $rental->id,
                'sender_id' => $sender->id,
                'message' => $message,
                'type' => $type,
                'attachments' => $attachments,
            ]);

            // Update sender's read timestamp
            $senderParticipant = ChatParticipant::where('rental_id', $rental->id)
                ->where('user_id', $sender->id)
                ->first();
            if ($senderParticipant) {
                $senderParticipant->markAsRead();
            }

            // Broadcast to rental channel
            $this->realtime->broadcastToRental($rental->id, 'message.new', [
                'message' => $message->load('sender:id,name,profile_photo_path'),
                'rental_id' => $rental->id,
            ]);

            Log::info("Chat message sent in rental {$rental->reference} by user {$sender->id}");

            return $message;
        });
    }

    /**
     * Get messages for a rental with pagination.
     */
    public function getMessages(Rental $rental, User $user, int $perPage = 50): LengthAwarePaginator
    {
        // Verify user is part of the rental
        abort_unless(
            in_array($user->id, [$rental->user_id, $rental->equipment->owner_id]),
            403
        );

        return ChatMessage::forRental($rental->id)
            ->with('sender:id,name,profile_photo_path')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Rental $rental, User $user): void
    {
        $participant = ChatParticipant::where('rental_id', $rental->id)
            ->where('user_id', $user->id)
            ->first();

        if ($participant) {
            $participant->markAsRead();

            // Broadcast read receipts for unread messages
            $unreadMessages = ChatMessage::forRental($rental->id)
                ->where('sender_id', '!=', $user->id)
                ->unread()
                ->get();

            foreach ($unreadMessages as $message) {
                $message->update(['read_at' => now()]);
                $this->realtime->broadcastReadReceipt($rental->id, $message->id, $user->id);
            }
        }
    }

    /**
     * Get unread message count for a user across all rentals.
     */
    public function getUnreadCount(User $user): int
    {
        $rentalIds = \App\Models\Rental::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('equipment', fn ($q) => $q->where('owner_id', $user->id));
        })->pluck('id');

        if ($rentalIds->isEmpty()) {
            return 0;
        }

        $participant = ChatParticipant::where('rental_id', $rentalIds)
            ->where('user_id', $user->id)
            ->first();

        if (! $participant || ! $participant->last_read_at) {
            return ChatMessage::whereIn('rental_id', $rentalIds)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();
        }

        return ChatMessage::whereIn('rental_id', $rentalIds)
            ->where('sender_id', '!=', $user->id)
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }

    /**
     * Send typing indicator.
     */
    public function sendTypingIndicator(Rental $rental, User $user, bool $isTyping): void
    {
        abort_unless(
            in_array($user->id, [$rental->user_id, $rental->equipment->owner_id]),
            403
        );

        $this->realtime->broadcastTyping($rental->id, $user->id, $isTyping);
    }

    /**
     * Edit a message.
     */
    public function editMessage(ChatMessage $message, User $user, string $newMessage): ChatMessage
    {
        abort_unless($message->sender_id === $user->id, 403, 'You can only edit your own messages.');
        abort_unless($message->type === 'text', 400, 'Only text messages can be edited.');

        $message->update([
            'message' => $newMessage,
            'edited_at' => now(),
        ]);

        $this->realtime->broadcastToRental($message->rental_id, 'message.edited', [
            'message_id' => $message->id,
            'message' => $newMessage,
            'edited_at' => $message->edited_at->toISOString(),
        ]);

        return $message;
    }

    /**
     * Delete a message (soft delete).
     */
    public function deleteMessage(ChatMessage $message, User $user): void
    {
        abort_unless($message->sender_id === $user->id, 403, 'You can only delete your own messages.');

        $message->delete();

        $this->realtime->broadcastToRental($message->rental_id, 'message.deleted', [
            'message_id' => $message->id,
        ]);
    }

    /**
     * Mute/unmute chat for a user.
     */
    public function toggleMute(Rental $rental, User $user, bool $mute, ?\Illuminate\Support\Carbon $until = null): void
    {
        $participant = ChatParticipant::where('rental_id', $rental->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($mute) {
            $participant->mute($until);
        } else {
            $participant->unmute();
        }
    }

    /**
     * Get chat participants for a rental.
     */
    public function getParticipants(Rental $rental): \Illuminate\Database\Eloquent\Collection
    {
        return ChatParticipant::where('rental_id', $rental->id)
            ->with('user:id,name,profile_photo_path')
            ->get();
    }
}
