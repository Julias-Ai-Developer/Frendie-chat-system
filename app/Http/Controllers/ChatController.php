<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    /**
     * Return users available for direct chat.
     */
    public function users(Request $request): JsonResponse
    {
        $authId = (int) $request->user()->id;

        $messages = ChatMessage::query()
            ->where(function ($query) use ($authId) {
                $query
                    ->where('sender_id', $authId)
                    ->orWhere('recipient_id', $authId);
            })
            ->latest('id')
            ->get([
                'id',
                'sender_id',
                'recipient_id',
                'body',
                'message_type',
                'attachment_name',
                'view_once',
                'opened_at',
                'metadata',
                'created_at',
                'read_at',
            ]);

        $latestByPartner = [];
        $unreadByPartner = [];

        foreach ($messages as $message) {
            $partnerId = $message->sender_id === $authId
                ? $message->recipient_id
                : $message->sender_id;

            if (! isset($latestByPartner[$partnerId])) {
                $latestByPartner[$partnerId] = $message;
            }

            if ($message->recipient_id === $authId && $message->read_at === null) {
                $unreadByPartner[$partnerId] = ($unreadByPartner[$partnerId] ?? 0) + 1;
            }
        }

        $users = User::query()
            ->whereKeyNot($authId)
            ->get(['id', 'name', 'email'])
            ->map(function (User $user) use ($authId, $latestByPartner, $unreadByPartner) {
                $lastMessage = $latestByPartner[$user->id] ?? null;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initials' => $user->initials(),
                    'unread_count' => $unreadByPartner[$user->id] ?? 0,
                    'last_message' => $lastMessage ? $this->messagePreview($lastMessage, $authId) : null,
                    'last_message_is_mine' => $lastMessage !== null
                        ? $lastMessage->sender_id === $authId
                        : false,
                    'last_message_at' => $lastMessage?->created_at?->toIso8601String(),
                    'sort_timestamp' => $lastMessage?->created_at?->timestamp ?? 0,
                ];
            })
            ->sort(function (array $a, array $b) {
                if ($a['sort_timestamp'] === $b['sort_timestamp']) {
                    return strcmp($a['name'], $b['name']);
                }

                return $b['sort_timestamp'] <=> $a['sort_timestamp'];
            })
            ->values()
            ->map(function (array $user) {
                unset($user['sort_timestamp']);

                return $user;
            });

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Return messages exchanged with the selected user.
     */
    public function conversation(Request $request, User $user): JsonResponse
    {
        $authId = (int) $request->user()->id;

        if ($user->id === $authId) {
            return response()->json([
                'message' => 'You cannot open a conversation with yourself.',
            ], 422);
        }

        $after = max((int) $request->integer('after', 0), 0);

        $query = ChatMessage::query()
            ->where(function ($conversation) use ($authId, $user) {
                $conversation
                    ->where(function ($direct) use ($authId, $user) {
                        $direct
                            ->where('sender_id', $authId)
                            ->where('recipient_id', $user->id);
                    })
                    ->orWhere(function ($direct) use ($authId, $user) {
                        $direct
                            ->where('sender_id', $user->id)
                            ->where('recipient_id', $authId);
                    });
            });

        if ($after > 0) {
            $query->where('id', '>', $after);
        }

        $messages = $query
            ->orderBy('id')
            ->get([
                'id',
                'sender_id',
                'recipient_id',
                'body',
                'message_type',
                'attachment_path',
                'attachment_name',
                'attachment_mime',
                'attachment_size',
                'view_once',
                'opened_at',
                'metadata',
                'created_at',
                'read_at',
            ]);

        ChatMessage::query()
            ->where('sender_id', $user->id)
            ->where('recipient_id', $authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'initials' => $user->initials(),
            ],
            'messages' => $messages->map(fn (ChatMessage $message) => $this->formatMessage($message, $authId)),
        ]);
    }

    /**
     * Store a new direct message.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'body' => ['nullable', 'string', 'max:2000'],
            'view_once' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'max:25600'],
        ]);

        $authId = (int) $request->user()->id;
        $recipientId = (int) $validated['recipient_id'];

        if ($recipientId === $authId) {
            return response()->json([
                'message' => 'You cannot send a message to yourself.',
            ], 422);
        }

        $body = trim((string) ($validated['body'] ?? ''));
        $attachment = $request->file('attachment');

        if ($body === '' && ! $attachment) {
            return response()->json([
                'message' => 'Please type a message or attach a file.',
            ], 422);
        }

        $messageType = 'text';
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($attachment) {
            $attachmentMime = (string) $attachment->getMimeType();
            $attachmentName = $attachment->getClientOriginalName();
            $attachmentSize = (int) $attachment->getSize();

            if (str_starts_with($attachmentMime, 'audio/')) {
                $messageType = 'audio';
            } elseif (str_starts_with($attachmentMime, 'video/')) {
                $messageType = 'video';
            } elseif (str_starts_with($attachmentMime, 'image/')) {
                $messageType = 'image';
            } else {
                $messageType = 'file';
            }

            $attachmentPath = $attachment->store('chat-attachments');
        }

        $message = ChatMessage::create([
            'sender_id' => $authId,
            'recipient_id' => $recipientId,
            'body' => $body,
            'message_type' => $messageType,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'attachment_size' => $attachmentSize,
            'view_once' => $request->boolean('view_once', false),
            'metadata' => null,
        ]);

        return response()->json([
            'message' => $this->formatMessage($message, $authId),
        ], 201);
    }

    /**
     * Start an audio or video call by sending an invite message.
     */
    public function startCall(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'mode' => ['required', 'in:audio,video'],
        ]);

        $authId = (int) $request->user()->id;
        $recipientId = (int) $validated['recipient_id'];

        if ($recipientId === $authId) {
            return response()->json([
                'message' => 'You cannot call yourself.',
            ], 422);
        }

        $mode = $validated['mode'];
        $room = 'frendie-'.Str::uuid();
        $callUrl = 'https://meet.jit.si/'.$room.'#config.prejoinPageEnabled=false&config.disableDeepLinking=true';

        if ($mode === 'audio') {
            $callUrl .= '&config.startAudioOnly=true&config.startWithVideoMuted=true';
        }

        $message = ChatMessage::create([
            'sender_id' => $authId,
            'recipient_id' => $recipientId,
            'body' => ucfirst($mode).' call invite',
            'message_type' => 'call',
            'metadata' => [
                'mode' => $mode,
                'room' => $room,
                'url' => $callUrl,
            ],
        ]);

        return response()->json([
            'message' => $this->formatMessage($message, $authId),
        ], 201);
    }

    /**
     * Stream an attachment to an authorized chat participant.
     */
    public function attachment(Request $request, ChatMessage $message): StreamedResponse
    {
        $authId = (int) $request->user()->id;

        if (! $this->isParticipant($message, $authId)) {
            abort(403);
        }

        if (! $message->attachment_path) {
            abort(404);
        }

        if (
            $message->view_once
            && $message->recipient_id === $authId
            && $message->opened_at !== null
        ) {
            abort(403);
        }

        if (! Storage::exists($message->attachment_path)) {
            abort(404);
        }

        return Storage::response(
            $message->attachment_path,
            $message->attachment_name ?? basename($message->attachment_path),
            [
                'Content-Type' => $message->attachment_mime ?: 'application/octet-stream',
            ]
        );
    }

    /**
     * Mark a view-once message as opened by its recipient.
     */
    public function consumeViewOnce(Request $request, ChatMessage $message): JsonResponse
    {
        $authId = (int) $request->user()->id;

        if (! $this->isParticipant($message, $authId)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (! $message->view_once) {
            return response()->json(['message' => 'Message is not view once.'], 422);
        }

        if ($message->recipient_id !== $authId) {
            return response()->json(['message' => 'Only recipient can consume this message.'], 403);
        }

        if ($message->opened_at === null) {
            $message->forceFill([
                'opened_at' => now(),
                'read_at' => $message->read_at ?? now(),
            ])->save();
        }

        return response()->json([
            'message' => 'View once message marked as opened.',
        ]);
    }

    /**
     * Update typing status for current user in one conversation.
     */
    public function updateTyping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'typing' => ['required', 'boolean'],
        ]);

        $authId = (int) $request->user()->id;
        $recipientId = (int) $validated['recipient_id'];

        if ($recipientId === $authId) {
            return response()->json(['typing' => false]);
        }

        $key = $this->typingKey($authId, $recipientId);

        if ($validated['typing']) {
            Cache::put($key, true, now()->addSeconds(6));
        } else {
            Cache::forget($key);
        }

        return response()->json([
            'typing' => (bool) $validated['typing'],
        ]);
    }

    /**
     * Get typing status of a user for the current conversation.
     */
    public function typingStatus(Request $request, User $user): JsonResponse
    {
        $authId = (int) $request->user()->id;

        return response()->json([
            'typing' => Cache::has($this->typingKey($user->id, $authId)),
        ]);
    }

    /**
     * Format message payload for frontend.
     */
    private function formatMessage(ChatMessage $message, int $authId): array
    {
        $metadata = is_array($message->metadata) ? $message->metadata : [];

        $isMine = $message->sender_id === $authId;
        $viewerIsRecipient = $message->recipient_id === $authId;
        $isViewOnceConsumed = (bool) (
            $message->view_once
            && $viewerIsRecipient
            && $message->opened_at !== null
        );
        $canConsumeViewOnce = (bool) (
            $message->view_once
            && $viewerIsRecipient
            && $message->opened_at === null
        );

        $attachmentUrl = null;
        if ($message->attachment_path && ! $isViewOnceConsumed) {
            $attachmentUrl = route('chat.message.attachment', ['message' => $message->id], false);
        }

        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'recipient_id' => $message->recipient_id,
            'body' => $isViewOnceConsumed ? null : $message->body,
            'message_type' => $message->message_type ?? 'text',
            'is_mine' => $isMine,
            'created_at' => $message->created_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
            'attachment_url' => $attachmentUrl,
            'attachment_name' => $message->attachment_name,
            'attachment_mime' => $message->attachment_mime,
            'attachment_size' => $message->attachment_size,
            'view_once' => (bool) $message->view_once,
            'opened_at' => $message->opened_at?->toIso8601String(),
            'can_consume_view_once' => $canConsumeViewOnce,
            'is_view_once_consumed' => $isViewOnceConsumed,
            'call_mode' => $metadata['mode'] ?? null,
            'call_url' => $metadata['url'] ?? null,
        ];
    }

    /**
     * Build a last-message preview for chats list.
     */
    private function messagePreview(ChatMessage $message, int $authId): string
    {
        $viewerIsRecipient = $message->recipient_id === $authId;
        $isConsumed = $message->view_once && $viewerIsRecipient && $message->opened_at !== null;

        if ($isConsumed) {
            return 'View once message';
        }

        return match ($message->message_type) {
            'audio' => 'Audio message',
            'video' => 'Video message',
            'image' => 'Photo',
            'file' => 'File: '.($message->attachment_name ?: 'attachment'),
            'call' => ucfirst((string) data_get($message->metadata, 'mode', 'audio')).' call invite',
            default => trim((string) $message->body) !== '' ? $message->body : 'Message',
        };
    }

    /**
     * Determine if user participates in a message.
     */
    private function isParticipant(ChatMessage $message, int $authId): bool
    {
        return $message->sender_id === $authId || $message->recipient_id === $authId;
    }

    /**
     * Cache key for typing status.
     */
    private function typingKey(int $senderId, int $recipientId): string
    {
        return 'chat:typing:'.$senderId.':'.$recipientId;
    }
}
