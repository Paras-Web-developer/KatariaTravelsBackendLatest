# Twilio WhatsApp Webhook Setup Checklist

## ✅ Pre-Setup Verification

-   [ ] Laravel application is running and accessible
-   [ ] Database migrations have been run successfully
-   [ ] Environment variables are configured:
    -   [ ] `TWILIO_SID`
    -   [ ] `TWILIO_AUTH_TOKEN`
    -   [ ] `TWILIO_WHATSAPP_NUMBER`
-   [ ] Queue worker is running: `php artisan queue:work`
-   [ ] Storage link is created: `php artisan storage:link`

## ✅ Twilio Console Setup

### For Sandbox Testing:

-   [ ] Go to [Twilio Console](https://console.twilio.com/)
-   [ ] Navigate to **Messaging** → **Try it out** → **Send a WhatsApp message**
-   [ ] Note your sandbox number
-   [ ] Send the provided code to the sandbox number via WhatsApp
-   [ ] Look for **Webhook Configuration** section
-   [ ] Set webhook URL to: `https://your-domain.com/api/whatsapp/webhook`
-   [ ] Set HTTP Method to `POST`
-   [ ] Save configuration

### For Production:

-   [ ] Go to **Messaging** → **Settings** → **Messaging Service**
-   [ ] Create new Messaging Service or select existing one
-   [ ] Go to **Inbound Settings**
-   [ ] Set webhook URL to: `https://your-domain.com/api/whatsapp/webhook`
-   [ ] Set HTTP Method to `POST`
-   [ ] Save configuration

## ✅ Testing Your Setup

### Test 1: Manual Webhook Test

-   [ ] Replace `your-domain.com` in `test-webhook.php` with your actual domain
-   [ ] Run: `php test-webhook.php`
-   [ ] Verify all tests pass (HTTP 200 responses)

### Test 2: Send Test WhatsApp Message

-   [ ] Send a WhatsApp message to your Twilio number
-   [ ] Check Laravel logs: `tail -f storage/logs/laravel.log`
-   [ ] Verify webhook is received and processed
-   [ ] Check database for new conversation and message records

### Test 3: Test Media Messages

-   [ ] Send an image or document via WhatsApp
-   [ ] Verify media is processed correctly
-   [ ] Check that file URL is stored in database
-   [ ] Ensure media is accessible via your application

## ✅ Monitoring Setup

### Log Monitoring:

-   [ ] Monitor Laravel logs for webhook activity
-   [ ] Check for any error messages
-   [ ] Verify queue processing is working

### Twilio Console Monitoring:

-   [ ] Check **Monitor** → **Logs** → **Error Logs** for webhook delivery issues
-   [ ] Verify webhook configuration is correct
-   [ ] Check message delivery status

### Database Monitoring:

-   [ ] Verify conversations are being created: `SELECT * FROM whatsapp_conversations;`
-   [ ] Verify messages are being stored: `SELECT * FROM whatsapp_messages;`
-   [ ] Check message status and timestamps

## ✅ Production Checklist

### Security:

-   [ ] HTTPS is enabled and working
-   [ ] Webhook endpoint is accessible
-   [ ] No firewall blocking webhook requests
-   [ ] Environment variables are secure

### Performance:

-   [ ] Queue worker is running continuously
-   [ ] Database indexes are in place
-   [ ] File upload limits are configured
-   [ ] Error handling is comprehensive

### Monitoring:

-   [ ] Set up log monitoring (Papertrail, Loggly, etc.)
-   [ ] Set up queue monitoring
-   [ ] Set up database monitoring
-   [ ] Set up uptime monitoring for webhook endpoint

## ✅ Troubleshooting Common Issues

### If webhook is not receiving messages:

-   [ ] Verify webhook URL is correct and accessible
-   [ ] Check that Laravel application is running
-   [ ] Ensure webhook route is not blocked
-   [ ] Verify Twilio webhook configuration

### If messages are not being processed:

-   [ ] Check queue worker is running
-   [ ] Verify job processing in logs
-   [ ] Check database for conversation/message records
-   [ ] Ensure TwilioService is properly configured

### If getting 404 errors:

-   [ ] Check that webhook route exists
-   [ ] Verify URL path is correct
-   [ ] Ensure Laravel application is running
-   [ ] Check for middleware blocking requests

### If getting 500 errors:

-   [ ] Check Laravel logs for errors
-   [ ] Verify database connection
-   [ ] Ensure all environment variables are set
-   [ ] Check that migrations have been run

## ✅ Final Verification

### API Endpoints:

-   [ ] `POST /api/whatsapp/webhook` - Working
-   [ ] `POST /api/whatsapp/webhook-test` - Working
-   [ ] All other WhatsApp API endpoints - Working

### Database:

-   [ ] `whatsapp_conversations` table - Created and accessible
-   [ ] `whatsapp_messages` table - Created and accessible
-   [ ] Proper indexes - In place

### Queue Processing:

-   [ ] `ProcessWhatsAppWebhook` job - Working
-   [ ] Queue worker - Running
-   [ ] Job processing - Successful

### Frontend Integration:

-   [ ] API authentication - Working
-   [ ] Conversation listing - Working
-   [ ] Message sending - Working
-   [ ] Media upload - Working
-   [ ] Real-time updates - Working

## ✅ Documentation

-   [ ] API documentation is complete (`docs/WHATSAPP_API.md`)
-   [ ] Setup guide is complete (`docs/TWILIO_WEBHOOK_SETUP.md`)
-   [ ] README is updated (`README_WHATSAPP.md`)
-   [ ] Test suite is working (`tests/Feature/WhatsAppApiTest.php`)

## ✅ Support Resources

-   [ ] Twilio webhook documentation reviewed
-   [ ] Laravel queue documentation reviewed
-   [ ] Error handling procedures documented
-   [ ] Monitoring procedures established

---

## Quick Commands for Testing

```bash
# Check routes
php artisan route:list | findstr whatsapp

# Test webhook
php test-webhook.php

# Monitor logs
tail -f storage/logs/laravel.log

# Check queue
php artisan queue:work --verbose

# Check database
php artisan tinker
>>> App\Models\WhatsAppConversation::count()
>>> App\Models\WhatsAppMessage::count()
```

## Emergency Contacts

-   **Twilio Support**: [Twilio Support](https://support.twilio.com/)
-   **Laravel Documentation**: [Laravel Docs](https://laravel.com/docs)
-   **WhatsApp Business API**: [WhatsApp Business API](https://developers.facebook.com/docs/whatsapp)
