# WhatsApp API Documentation

This document describes the WhatsApp API endpoints for the Kataria Travels backend system.

## Base URL

```
https://your-domain.com/api/whatsapp
```

## Authentication

Most endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

## Endpoints

### 1. Get Conversations

Retrieve all WhatsApp conversations with optional filtering.

**GET** `/conversations`

**Query Parameters:**

-   `type` (optional): Filter by conversation type (`individual` or `group`)
-   `archived` (optional): Filter by archived status (`true` or `false`)
-   `search` (optional): Search by phone number, name, or group name
-   `page` (optional): Page number for pagination

**Response:**

```json
{
  "success": true,
  "data": {
    "conversations": [
      {
        "id": 1,
        "phone_number": "+1234567890",
        "name": "John Doe",
        "profile_picture": null,
        "type": "individual",
        "group_id": null,
        "group_name": null,
        "participants": null,
        "last_message_at": "2024-01-15T10:30:00.000000Z",
        "last_message": "Hello!",
        "is_archived": false,
        "is_muted": false,
        "unread_count": 2,
        "created_at": "2024-01-15T09:00:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 100
    }
  }
}
```

### 2. Get Messages

Retrieve messages for a specific conversation.

**GET** `/conversations/{conversationId}/messages`

**Path Parameters:**

-   `conversationId`: The ID of the conversation

**Query Parameters:**

-   `type` (optional): Filter by message type (`text`, `image`, `video`, `audio`, `document`)
-   `direction` (optional): Filter by message direction (`inbound` or `outbound`)
-   `page` (optional): Page number for pagination

**Response:**

```json
{
  "success": true,
  "data": {
    "conversation": {
      "id": 1,
      "phone_number": "+1234567890",
      "name": "John Doe",
      "type": "individual"
    },
    "messages": [
      {
        "id": 1,
        "conversation_id": 1,
        "message_id": "MG1234567890",
        "direction": "inbound",
        "type": "text",
        "content": "Hello!",
        "file_url": null,
        "file_name": null,
        "file_type": null,
        "file_size": null,
        "metadata": {},
        "status": "delivered",
        "sent_at": null,
        "delivered_at": "2024-01-15T10:30:00.000000Z",
        "read_at": null,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z",
        "is_media": false,
        "is_text": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 50,
      "total": 150
    }
  }
}
```

### 3. Send Text Message

Send a text message to a WhatsApp number.

**POST** `/messages/text`

**Request Body:**

```json
{
  "phone_number": "+1234567890",
  "message": "Hello! How can I help you today?"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Message sent successfully",
    "message_sid": "MG1234567890",
    "conversation_id": 1,
    "message_record": {
      "id": 1,
      "conversation_id": 1,
      "message_id": "MG1234567890",
      "direction": "outbound",
      "type": "text",
      "content": "Hello! How can I help you today?",
      "status": "sent",
      "sent_at": "2024-01-15T10:30:00.000000Z"
    }
  }
}
```

### 4. Send Media Message

Send a media message (image, video, audio, document) to a WhatsApp number.

**POST** `/messages/media`

**Request Body (multipart/form-data):**

-   `phone_number`: WhatsApp phone number
-   `media`: Media file (jpg, jpeg, png, gif, mp4, mov, avi, pdf, doc, docx, txt)
-   `caption` (optional): Caption for the media

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Media message sent successfully",
    "message_sid": "MG1234567890",
    "conversation_id": 1,
    "message_record": {
      "id": 1,
      "conversation_id": 1,
      "message_id": "MG1234567890",
      "direction": "outbound",
      "type": "image",
      "content": "Check out this image!",
      "file_url": "https://your-domain.com/storage/whatsapp-media/image.jpg",
      "file_name": "image.jpg",
      "file_type": "image/jpeg",
      "file_size": 1024000,
      "status": "sent",
      "sent_at": "2024-01-15T10:30:00.000000Z"
    }
  }
}
```

### 5. Send Bulk Message

Send the same message to multiple WhatsApp numbers.

**POST** `/messages/bulk`

**Request Body (multipart/form-data):**

-   `phone_numbers[]`: Array of WhatsApp phone numbers
-   `message`: Text message
-   `media` (optional): Media file

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Bulk message sent",
    "results": [
      {
        "phone_number": "+1234567890",
        "success": true,
        "message_sid": "MG1234567890"
      },
      {
        "phone_number": "+0987654321",
        "success": false,
        "error": "Invalid phone number"
      }
    ]
  }
}
```

### 6. Get Message Status

Get the delivery status of a specific message.

**GET** `/messages/{messageSid}/status`

**Path Parameters:**

-   `messageSid`: Twilio message SID

**Response:**

```json
{
  "success": true,
  "data": {
    "sid": "MG1234567890",
    "status": "delivered",
    "direction": "outbound",
    "from": "whatsapp:+1234567890",
    "to": "whatsapp:+0987654321",
    "body": "Hello!",
    "dateCreated": "2024-01-15T10:30:00.000Z",
    "dateSent": "2024-01-15T10:30:05.000Z",
    "dateUpdated": "2024-01-15T10:30:10.000Z"
  }
}
```

### 7. Mark Conversation as Read

Mark all messages in a conversation as read.

**POST** `/conversations/{conversationId}/mark-read`

**Path Parameters:**

-   `conversationId`: The ID of the conversation

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Conversation marked as read"
  }
}
```

### 8. Toggle Archive Status

Archive or unarchive a conversation.

**POST** `/conversations/{conversationId}/toggle-archive`

**Path Parameters:**

-   `conversationId`: The ID of the conversation

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Conversation archived",
    "is_archived": true
  }
}
```

### 9. Toggle Mute Status

Mute or unmute a conversation.

**POST** `/conversations/{conversationId}/toggle-mute`

**Path Parameters:**

-   `conversationId`: The ID of the conversation

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Conversation muted",
    "is_muted": true
  }
}
```

### 10. Delete Conversation

Delete a conversation and all its messages.

**DELETE** `/conversations/{conversationId}`

**Path Parameters:**

-   `conversationId`: The ID of the conversation

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Conversation deleted successfully"
  }
}
```

### 11. Get Statistics

Get WhatsApp usage statistics.

**GET** `/statistics`

**Response:**

```json
{
  "success": true,
  "data": {
    "total_conversations": 150,
    "total_messages": 2500,
    "unread_messages": 45,
    "today_messages": 25
  }
}
```

### 12. Webhook Endpoint

Webhook endpoint for receiving WhatsApp messages from Twilio.

**POST** `/webhook`

**Note:** This endpoint does not require authentication.

**Request Body:**
Twilio sends webhook data in the following format:

```
From=whatsapp%3A%2B1234567890&To=whatsapp%3A%2B0987654321&MessageSid=MG1234567890&Body=Hello&NumMedia=0
```

**Response:**

```json
{
  "success": true,
  "message": "Webhook queued for processing"
}
```

## Error Responses

All endpoints return error responses in the following format:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

Common HTTP status codes:

-   `200`: Success
-   `201`: Created
-   `400`: Bad Request
-   `401`: Unauthorized
-   `404`: Not Found
-   `422`: Validation Error
-   `500`: Internal Server Error

## Environment Variables

Make sure to set the following environment variables:

```env
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_WHATSAPP_NUMBER=whatsapp:+1234567890
```

## Webhook Configuration

To receive incoming WhatsApp messages, configure your Twilio webhook URL to point to:

```
https://your-domain.com/api/whatsapp/webhook
```

## File Upload Limits

-   Maximum file size: 16MB
-   Supported file types: jpg, jpeg, png, gif, mp4, mov, avi, pdf, doc, docx, txt
-   Files are stored in the `storage/app/public/whatsapp-media` directory

## Rate Limiting

The API implements rate limiting to prevent abuse. Limits are applied per user and endpoint.

## Security Notes

1. Always use HTTPS in production
2. Validate phone numbers before sending messages
3. Implement proper error handling in your frontend
4. Store sensitive data securely
5. Monitor webhook processing for errors
