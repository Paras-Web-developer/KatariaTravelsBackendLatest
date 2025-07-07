<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WhatsAppApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Mock Twilio service
        $this->mock(\App\Services\TwilioService::class, function ($mock) {
            $mock->shouldReceive('sendWhatsAppMessage')
                ->andReturn('MG1234567890');
            $mock->shouldReceive('sendWhatsAppMedia')
                ->andReturn('MG1234567890');
            $mock->shouldReceive('uploadMedia')
                ->andReturn('https://example.com/media.jpg');
        });
    }

    public function test_can_get_conversations()
    {
        $conversation = WhatsAppConversation::create([
            'phone_number' => '+1234567890',
            'name' => 'Test User',
            'type' => 'individual',
            'last_message_at' => now(),
            'last_message' => 'Hello'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/whatsapp/conversations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'conversations',
                    'pagination'
                ]
            ]);
    }

    public function test_can_send_text_message()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/whatsapp/messages/text', [
                'phone_number' => '+1234567890',
                'message' => 'Hello World!'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'Message sent successfully'
                ]
            ]);
    }

    public function test_can_send_media_message()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($this->user)
            ->post('/api/whatsapp/messages/media', [
                'phone_number' => '+1234567890',
                'media' => $file,
                'caption' => 'Test image'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'Media message sent successfully'
                ]
            ]);
    }

    public function test_can_get_messages()
    {
        $conversation = WhatsAppConversation::create([
            'phone_number' => '+1234567890',
            'name' => 'Test User',
            'type' => 'individual'
        ]);

        $message = WhatsAppMessage::create([
            'conversation_id' => $conversation->id,
            'message_id' => 'MG1234567890',
            'direction' => 'inbound',
            'type' => 'text',
            'content' => 'Hello',
            'status' => 'delivered',
            'delivered_at' => now()
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/whatsapp/conversations/{$conversation->id}/messages");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'conversation',
                    'messages',
                    'pagination'
                ]
            ]);
    }

    public function test_can_mark_conversation_as_read()
    {
        $conversation = WhatsAppConversation::create([
            'phone_number' => '+1234567890',
            'name' => 'Test User',
            'type' => 'individual'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/whatsapp/conversations/{$conversation->id}/mark-read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'Conversation marked as read'
                ]
            ]);
    }

    public function test_can_toggle_archive_status()
    {
        $conversation = WhatsAppConversation::create([
            'phone_number' => '+1234567890',
            'name' => 'Test User',
            'type' => 'individual',
            'is_archived' => false
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/whatsapp/conversations/{$conversation->id}/toggle-archive");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_archived' => true
                ]
            ]);
    }

    public function test_can_get_statistics()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/whatsapp/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_conversations',
                    'total_messages',
                    'unread_messages',
                    'today_messages'
                ]
            ]);
    }

    public function test_webhook_endpoint_works()
    {
        $webhookData = [
            'From' => 'whatsapp:+1234567890',
            'To' => 'whatsapp:+0987654321',
            'MessageSid' => 'MG1234567890',
            'Body' => 'Hello from webhook',
            'NumMedia' => '0'
        ];

        $response = $this->postJson('/api/whatsapp/webhook', $webhookData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Webhook queued for processing'
            ]);
    }

    public function test_validation_errors_for_invalid_phone_number()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/whatsapp/messages/text', [
                'phone_number' => 'invalid',
                'message' => 'Hello'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    public function test_validation_errors_for_empty_message()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/whatsapp/messages/text', [
                'phone_number' => '+1234567890',
                'message' => ''
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }
}