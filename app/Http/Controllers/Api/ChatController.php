<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreChatMessageRequest;
use App\Models\Rental;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Chat API controller for rental communication.
 */
class ChatController
{
    public function __construct(private readonly \App\Services\ChatService $service) {}

    /**
     * Get messages for a rental.
     */
    public function index(Request $request, Rental $rental): JsonResponse
    {
        $messages = $this->service->getMessages($rental, $request->user(), $request->integer('per_page', 50));

        return response()->json($messages);
    }

    /**
     * Send a new message.
     */
    public function store(Request $request, Rental $rental): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['nullable', 'in:text,image,file,location,system'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*.type' => ['required', 'in:image,file'],
            'attachments.*.url' => ['required', 'url', 'max:500'],
            'attachments.*.name' => ['nullable', 'string', 'max:255'],
            'attachments.*.size' => ['nullable', 'integer', 'min:0'],
        ]);

        $message = $this->service->sendMessage(
            $rental,
            $request->user(),
            $validated['message'],
            $validated['type'] ?? 'text',
            $validated['attachments'] ?? null
        );

        return response()->json([
            'message' => 'Message sent successfully.',
            'chat_message' => $message->load('sender:id,name,profile_photo_path'),
        ], 201);
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Rental $rental): JsonResponse
    {
        $this->service->markAsRead($rental, request()->user());

        return response()->json(['message' => 'Messages marked as read.']);
    }

    /**
     * Get unread message count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = app(\App\Services\ChatService::class)->getUnreadCount($request->user());

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Send typing indicator.
     */
    public function typing(Request $request, Rental $rental): JsonResponse
    {
        $validated = $request->validate([
            'is_typing' => ['required', 'boolean'],
        ]);

        app(\App\Services\ChatService::class)->sendTypingIndicator($rental, request()->user(), $validated['is_typing']);

        return response()->json(['message' => 'Typing indicator sent.']);
    }

    /**
     * Edit a message.
     */
    public function update(Request $request, ChatMessage $message): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        );

        $message = app(\App\Services\ChatService::class)->editMessage($message, request()->user(), $validated['message']);

        return response()->json([
            'message' => 'Message updated successfully.',
            'chat_message' => $message,
        ]);
    }

    /**
     * Delete a message.
     */
    public function destroy(ChatMessage $message): JsonResponse
    {
        app(\App\Services\ChatService::class)->deleteMessage($message, request()->user());

        return response()->json(['message' => 'Message deleted successfully.']);
    }

    /**
     * Toggle mute for a rental chat.
     */
    public function mute(Request $request, Rental $rental): JsonResponse
    {
        $validated = $request->validate([
            'muted' => ['required', 'boolean'],
            'muted_until' => ['nullable', 'date', 'after:now'],
        ]);

        app(\App\Services\ChatService::class)->toggleMute($rental, request()->user(), $validated['muted'], $validated['muted_until'] ?? null);

        return response()->json(['message' => $validated['muted'] ? 'Chat muted.' : 'Chat unmuted.']);
    }
}