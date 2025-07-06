# WhatsApp Integration for Kataria Travels

This document provides setup instructions and information about the WhatsApp integration implemented for the Kataria Travels backend system.

## Features Implemented

### ✅ Core Functionality

-   **WhatsApp Chat Interface**: Complete chat system with conversation management
-   **Multi-User Support**: Send messages to multiple users simultaneously
-   **Group Chat Support**: Handle group conversations (structure ready)
-   **Media File Support**: Send images, videos, audio, and documents
-   **Message History**: Load and display older chats with pagination
-   **Real-time Updates**: Webhook processing for incoming messages
-   **Message Status Tracking**: Track sent, delivered, and read status

### ✅ API Endpoints

-   `GET /api/whatsapp/conversations` - List all conversations
-   `GET /api/whatsapp/conversations/{id}/messages` - Get conversation messages
-   `POST /api/whatsapp/messages/text` - Send text message
-   `POST /api/whatsapp/messages/media` - Send media message
-   `POST /api/whatsapp/messages/bulk` - Send bulk messages
-   `GET /api/whatsapp/messages/{sid}/status` - Get message status
-   `POST /api/whatsapp/conversations/{id}/mark-read` - Mark as read
-   `POST /api/whatsapp/conversations/{id}/toggle-archive` - Archive/unarchive
-   `POST /api/whatsapp/conversations/{id}/toggle-mute` - Mute/unmute
-   `DELETE /api/whatsapp/conversations/{id}` - Delete conversation
-   `GET /api/whatsapp/statistics` - Get usage statistics
-   `POST /api/whatsapp/webhook` - Twilio webhook endpoint

## Setup Instructions

### 1. Environment Variables

Add the following variables to your `.env` file:

```env
# Twilio Configuration
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_WHATSAPP_NUMBER=whatsapp:+1234567890

# File Storage (for media files)
FILESYSTEM_DISK=public
```

### 2. Database Migration

Run the migrations to create the required tables:

```bash
php artisan migrate
```

This will create:

-   `whatsapp_conversations` table
-   `whatsapp_messages` table

### 3. Storage Setup

Create the storage link for media files:

```bash
php artisan storage:link
```

### 4. Queue Configuration

Configure your queue driver in `.env`:

```env
QUEUE_CONNECTION=database
```

Run the queue worker:

```bash
php artisan queue:work
```

### 5. Twilio Webhook Configuration

In your Twilio console, set the webhook URL for WhatsApp to:

```
https://your-domain.com/api/whatsapp/webhook
```

## Database Schema

### WhatsApp Conversations Table

```sql
CREATE TABLE whatsapp_conversations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(255) NOT NULL,
    name VARCHAR(255) NULL,
    profile_picture VARCHAR(255) NULL,
    type ENUM('individual', 'group') DEFAULT 'individual',
    group_id VARCHAR(255) NULL,
    group_name VARCHAR(255) NULL,
    participants JSON NULL,
    last_message_at TIMESTAMP NULL,
    last_message TEXT NULL,
    is_archived BOOLEAN DEFAULT FALSE,
    is_muted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_phone_number (phone_number),
    INDEX idx_type (type)
);
```

### WhatsApp Messages Table

```sql
CREATE TABLE whatsapp_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT NOT NULL,
    message_id VARCHAR(255) UNIQUE NOT NULL,
    direction ENUM('inbound', 'outbound') DEFAULT 'outbound',
    type ENUM('text', 'image', 'video', 'audio', 'document', 'location', 'contact') DEFAULT 'text',
    content TEXT NOT NULL,
    file_url VARCHAR(255) NULL,
    file_name VARCHAR(255) NULL,
    file_type VARCHAR(255) NULL,
    file_size INT NULL,
    metadata JSON NULL,
    status ENUM('sent', 'delivered', 'read', 'failed') DEFAULT 'sent',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (conversation_id) REFERENCES whatsapp_conversations(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_message_id (message_id),
    INDEX idx_direction (direction),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
);
```

## Frontend Integration

### Authentication

All API endpoints (except webhook) require authentication. Include the Bearer token in requests:

```javascript
const headers = {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
};
```

### Example API Calls

#### Get Conversations

```javascript
const response = await fetch('/api/whatsapp/conversations', {
    headers: headers
});
const data = await response.json();
```

#### Send Text Message

```javascript
const response = await fetch('/api/whatsapp/messages/text', {
    method: 'POST',
    headers: headers,
    body: JSON.stringify({
        phone_number: '+1234567890',
        message: 'Hello!'
    })
});
```

#### Send Media Message

```javascript
const formData = new FormData();
formData.append('phone_number', '+1234567890');
formData.append('media', file);
formData.append('caption', 'Check this out!');

const response = await fetch('/api/whatsapp/messages/media', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`
    },
    body: formData
});
```

#### Get Messages for Conversation

```javascript
const response = await fetch(`/api/whatsapp/conversations/${conversationId}/messages`, {
    headers: headers
});
```

## File Upload Support

### Supported File Types

-   **Images**: jpg, jpeg, png, gif
-   **Videos**: mp4, mov, avi
-   **Documents**: pdf, doc, docx, txt
-   **Audio**: mp3, wav, ogg

### File Size Limits

-   Maximum file size: 16MB
-   Files are stored in `storage/app/public/whatsapp-media/`

## Error Handling

### Common Error Responses

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

### HTTP Status Codes

-   `200`: Success
-   `201`: Created
-   `400`: Bad Request
-   `401`: Unauthorized
-   `404`: Not Found
-   `422`: Validation Error
-   `500`: Internal Server Error

## Testing

Run the test suite to verify functionality:

```bash
php artisan test --filter=WhatsAppApiTest
```

## Monitoring and Logging

### Log Files

-   Webhook processing: `storage/logs/laravel.log`
-   Twilio API errors: `storage/logs/laravel.log`

### Queue Monitoring

Monitor the queue for webhook processing:

```bash
php artisan queue:work --verbose
```

## Security Considerations

1. **HTTPS**: Always use HTTPS in production
2. **Authentication**: All endpoints (except webhook) require authentication
3. **File Validation**: All uploaded files are validated for type and size
4. **Rate Limiting**: API implements rate limiting
5. **Input Validation**: All inputs are validated and sanitized

## Troubleshooting

### Common Issues

1. **Webhook not receiving messages**

    - Check Twilio webhook URL configuration
    - Verify webhook endpoint is accessible
    - Check logs for errors

2. **Media files not uploading**

    - Verify storage link is created
    - Check file permissions
    - Ensure file size is within limits

3. **Messages not sending**

    - Verify Twilio credentials
    - Check phone number format
    - Review Twilio console for errors

4. **Queue not processing**
    - Ensure queue worker is running
    - Check queue configuration
    - Monitor queue logs

## Support

For issues or questions:

1. Check the logs in `storage/logs/laravel.log`
2. Verify environment variables are set correctly
3. Test with the provided test suite
4. Review the API documentation in `docs/WHATSAPP_API.md`
