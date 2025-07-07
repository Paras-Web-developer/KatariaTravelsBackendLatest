<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\TwilioService;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppController extends BaseController
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Get all conversations
     */
    public function getConversations(Request $request)
    {
        try {
            $query = WhatsAppConversation::with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }]);

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by archived status
            if ($request->has('archived')) {
                $query->where('is_archived', $request->boolean('archived'));
            }

            // Search by phone number or name
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('phone_number', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('group_name', 'like', "%{$search}%");
                });
            }

            $conversations = $query->latest('last_message_at')->paginate(20);

            return $this->successResponse([
                'conversations' => $conversations->items(),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'per_page' => $conversations->perPage(),
                    'total' => $conversations->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching conversations: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch conversations', 500);
        }
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages(Request $request, $conversationId)
    {
        try {
            $conversation = WhatsAppConversation::findOrFail($conversationId);

            $query = $conversation->messages();

            // Filter by message type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by direction
            if ($request->has('direction')) {
                $query->where('direction', $request->direction);
            }

            $messages = $query->latest()->paginate(50);

            // Mark messages as read if they are inbound
            $conversation->markAsRead();

            return $this->successResponse([
                'conversation' => $conversation,
                'messages' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching messages: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch messages', 500);
        }
    }

    /**
     * Send text message
     */
    public function sendTextMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|string',
                'message' => 'required|string|max:4096',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $phoneNumber = $request->phone_number;
            $message = $request->message;

            // Send message via Twilio
            $messageSid = $this->twilioService->sendWhatsAppMessage($phoneNumber, $message);

            if (is_string($messageSid) && !str_starts_with($messageSid, 'MG')) {
                return $this->errorResponse('Failed to send message: ' . $messageSid, 500);
            }

            // Find or create conversation
            $conversation = WhatsAppConversation::firstOrCreate(
                ['phone_number' => $phoneNumber],
                [
                    'type' => 'individual',
                    'last_message_at' => now(),
                    'last_message' => $message
                ]
            );

            // Create message record
            $messageRecord = WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'message_id' => $messageSid,
                'direction' => 'outbound',
                'type' => 'text',
                'content' => $message,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Update conversation
            $conversation->update([
                'last_message_at' => now(),
                'last_message' => $message
            ]);

            return $this->successResponse([
                'message' => 'Message sent successfully',
                'message_sid' => $messageSid,
                'conversation_id' => $conversation->id,
                'message_record' => $messageRecord
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending text message: ' . $e->getMessage());
            return $this->errorResponse('Failed to send message', 500);
        }
    }

    /**
     * Send media message
     */
    public function sendMediaMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|string',
                'media' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf,doc,docx,txt|max:16384', // 16MB max
                'caption' => 'nullable|string|max:1024',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $phoneNumber = $request->phone_number;
            $caption = $request->caption;
            $file = $request->file('media');

            // Upload file to storage
            $fileUrl = $this->twilioService->uploadMedia($file);

            // Send media message via Twilio
            $messageSid = $this->twilioService->sendWhatsAppMedia($phoneNumber, $fileUrl, $caption);

            if (is_string($messageSid) && !str_starts_with($messageSid, 'MG')) {
                return $this->errorResponse('Failed to send media message: ' . $messageSid, 500);
            }

            // Find or create conversation
            $conversation = WhatsAppConversation::firstOrCreate(
                ['phone_number' => $phoneNumber],
                [
                    'type' => 'individual',
                    'last_message_at' => now(),
                    'last_message' => $caption ?: 'Media message'
                ]
            );

            // Determine message type
            $messageType = $this->determineMessageType($file->getMimeType());

            // Create message record
            $messageRecord = WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'message_id' => $messageSid,
                'direction' => 'outbound',
                'type' => $messageType,
                'content' => $caption ?: 'Media message',
                'file_url' => $fileUrl,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Update conversation
            $conversation->update([
                'last_message_at' => now(),
                'last_message' => $caption ?: 'Media message'
            ]);

            return $this->successResponse([
                'message' => 'Media message sent successfully',
                'message_sid' => $messageSid,
                'conversation_id' => $conversation->id,
                'message_record' => $messageRecord
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending media message: ' . $e->getMessage());
            return $this->errorResponse('Failed to send media message', 500);
        }
    }

    /**
     * Send message to multiple recipients
     */
    public function sendBulkMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_numbers' => 'required|array|min:1',
                'phone_numbers.*' => 'required|string',
                'message' => 'required|string|max:4096',
                'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf,doc,docx,txt|max:16384',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $phoneNumbers = $request->phone_numbers;
            $message = $request->message;
            $file = $request->file('media');

            $results = [];
            $fileUrl = null;

            // Upload media if provided
            if ($file) {
                $fileUrl = $this->twilioService->uploadMedia($file);
            }

            foreach ($phoneNumbers as $phoneNumber) {
                try {
                    $messageSid = null;

                    if ($fileUrl) {
                        $messageSid = $this->twilioService->sendWhatsAppMedia($phoneNumber, $fileUrl, $message);
                    } else {
                        $messageSid = $this->twilioService->sendWhatsAppMessage($phoneNumber, $message);
                    }

                    $results[] = [
                        'phone_number' => $phoneNumber,
                        'success' => true,
                        'message_sid' => $messageSid
                    ];

                } catch (\Exception $e) {
                    $results[] = [
                        'phone_number' => $phoneNumber,
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $this->successResponse([
                'message' => 'Bulk message sent',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending bulk message: ' . $e->getMessage());
            return $this->errorResponse('Failed to send bulk message', 500);
        }
    }

    /**
     * Get message status
     */
    public function getMessageStatus($messageSid)
    {
        try {
            $status = $this->twilioService->getMessageStatus($messageSid);

            if (!$status) {
                return $this->errorResponse('Message not found', 404);
            }

            return $this->successResponse($status);

        } catch (\Exception $e) {
            Log::error('Error getting message status: ' . $e->getMessage());
            return $this->errorResponse('Failed to get message status', 500);
        }
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead($conversationId)
    {
        try {
            $conversation = WhatsAppConversation::findOrFail($conversationId);
            $conversation->markAsRead();

            return $this->successResponse([
                'message' => 'Conversation marked as read'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking conversation as read: ' . $e->getMessage());
            return $this->errorResponse('Failed to mark conversation as read', 500);
        }
    }

    /**
     * Archive/Unarchive conversation
     */
    public function toggleArchive(Request $request, $conversationId)
    {
        try {
            $conversation = WhatsAppConversation::findOrFail($conversationId);
            $conversation->update(['is_archived' => !$conversation->is_archived]);

            return $this->successResponse([
                'message' => $conversation->is_archived ? 'Conversation archived' : 'Conversation unarchived',
                'is_archived' => $conversation->is_archived
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling archive: ' . $e->getMessage());
            return $this->errorResponse('Failed to toggle archive', 500);
        }
    }

    /**
     * Mute/Unmute conversation
     */
    public function toggleMute(Request $request, $conversationId)
    {
        try {
            $conversation = WhatsAppConversation::findOrFail($conversationId);
            $conversation->update(['is_muted' => !$conversation->is_muted]);

            return $this->successResponse([
                'message' => $conversation->is_muted ? 'Conversation muted' : 'Conversation unmuted',
                'is_muted' => $conversation->is_muted
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling mute: ' . $e->getMessage());
            return $this->errorResponse('Failed to toggle mute', 500);
        }
    }

    /**
     * Delete conversation
     */
    public function deleteConversation($conversationId)
    {
        try {
            $conversation = WhatsAppConversation::findOrFail($conversationId);
            $conversation->delete();

            return $this->successResponse([
                'message' => 'Conversation deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting conversation: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete conversation', 500);
        }
    }

    /**
     * Test webhook endpoint
     */
    public function testWebhook(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Webhook endpoint is working',
            'received_data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Webhook endpoint for Twilio
     */
    public function webhook(Request $request)
    {
        try {
            $result = $this->twilioService->handleWebhook($request);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 500);
            }

        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Get conversation statistics
     */
    public function getStatistics()
    {
        try {
            $totalConversations = WhatsAppConversation::count();
            $totalMessages = WhatsAppMessage::count();
            $unreadMessages = WhatsAppMessage::where('direction', 'inbound')
                ->where('status', '!=', 'read')
                ->count();
            $todayMessages = WhatsAppMessage::whereDate('created_at', today())->count();

            return $this->successResponse([
                'total_conversations' => $totalConversations,
                'total_messages' => $totalMessages,
                'unread_messages' => $unreadMessages,
                'today_messages' => $todayMessages
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting statistics: ' . $e->getMessage());
            return $this->errorResponse('Failed to get statistics', 500);
        }
    }

    private function determineMessageType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } elseif (str_starts_with($mimeType, 'application/')) {
            return 'document';
        }

        return 'text';
    }
}