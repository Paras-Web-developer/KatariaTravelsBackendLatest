# Twilio WhatsApp Webhook Setup Guide

This guide will walk you through setting up Twilio webhooks to receive WhatsApp messages in your Laravel application.

## Prerequisites

1. **Twilio Account**: You need a Twilio account with WhatsApp capabilities
2. **Public Domain**: Your Laravel application must be accessible via HTTPS
3. **Laravel Application**: The WhatsApp API must be properly configured

## Step 1: Twilio Console Setup

### 1.1 Access Twilio Console

1. Go to [Twilio Console](https://console.twilio.com/)
2. Sign in to your Twilio account
3. Navigate to **Messaging** → **Try it out** → **Send a WhatsApp message**

### 1.2 WhatsApp Sandbox (For Testing)

If you're using Twilio's WhatsApp sandbox for testing:

1. In the Twilio Console, you'll see a sandbox number
2. Send the provided code to the sandbox number via WhatsApp
3. This will activate your sandbox for testing

### 1.3 Production WhatsApp Business API

For production use:

1. Apply for WhatsApp Business API access
2. Complete the verification process
3. Get your approved WhatsApp Business number

## Step 2: Configure Webhook URL

### 2.1 For Sandbox Testing

1. In Twilio Console, go to **Messaging** → **Try it out** → **Send a WhatsApp message**
2. Look for **Webhook Configuration** section
3. Set the webhook URL to:
    ```
    https://your-domain.com/api/whatsapp/webhook
    ```
4. Set **HTTP Method** to `POST`
5. Save the configuration

### 2.2 For Production

1. Go to **Messaging** → **Settings** → **Messaging Service**
2. Create a new Messaging Service or select existing one
3. Go to **Inbound Settings**
4. Set the **Webhook URL** to:
    ```
    https://your-domain.com/api/whatsapp/webhook
    ```
5. Set **HTTP Method** to `POST`
6. Save the configuration

## Step 3: Verify Your Laravel Application

### 3.1 Check Environment Variables

Ensure these variables are set in your `.env` file:

```env
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_WHATSAPP_NUMBER=whatsapp:+1234567890
```

### 3.2 Verify Routes

Check that your webhook routes are accessible:

```bash
php artisan route:list | grep whatsapp
```

You should see:

-   `POST /api/whatsapp/webhook`
-   `POST /api/whatsapp/webhook-test`

### 3.3 Test Webhook Endpoint

Run the test script to verify your webhook:

```bash
php test-webhook.php
```

## Step 4: Monitor Webhook Activity

### 4.1 Check Laravel Logs

Monitor your Laravel logs for webhook activity:

```bash
tail -f storage/logs/laravel.log
```

### 4.2 Check Twilio Console

In Twilio Console, go to **Monitor** → **Logs** → **Error Logs** to see any webhook delivery issues.

### 4.3 Check Queue Processing

Monitor your queue for webhook processing:

```bash
php artisan queue:work --verbose
```

## Step 5: Troubleshooting

### 5.1 Common Issues

#### Issue: Webhook not receiving messages

**Solutions:**

1. Verify webhook URL is accessible via HTTPS
2. Check that your Laravel application is running
3. Ensure the webhook route is not blocked by firewall
4. Verify Twilio webhook configuration in console

#### Issue: 404 errors

**Solutions:**

1. Check that the webhook route exists
2. Verify the URL path is correct
3. Ensure Laravel application is running
4. Check for any middleware blocking the request

#### Issue: 500 errors

**Solutions:**

1. Check Laravel logs for errors
2. Verify database connection
3. Ensure all required environment variables are set
4. Check that migrations have been run

#### Issue: Messages not being processed

**Solutions:**

1. Check queue worker is running
2. Verify job processing in logs
3. Check database for conversation/message records
4. Ensure TwilioService is properly configured

### 5.2 Debug Steps

1. **Test webhook endpoint manually:**

    ```bash
    curl -X POST https://your-domain.com/api/whatsapp/webhook-test \
      -H "Content-Type: application/x-www-form-urlencoded" \
      -d "From=whatsapp:+1234567890&Body=Test message"
    ```

2. **Check webhook logs:**

    ```bash
    grep "WhatsApp Webhook" storage/logs/laravel.log
    ```

3. **Verify queue processing:**

    ```bash
    php artisan queue:work --once --verbose
    ```

4. **Check database records:**
    ```sql
    SELECT * FROM whatsapp_conversations;
    SELECT * FROM whatsapp_messages;
    ```

## Step 6: Production Considerations

### 6.1 Security

1. **HTTPS Only**: Always use HTTPS in production
2. **Authentication**: Webhook endpoint doesn't require auth (Twilio handles this)
3. **Rate Limiting**: Implement rate limiting if needed
4. **Input Validation**: All webhook data is validated

### 6.2 Performance

1. **Queue Processing**: Webhooks are processed asynchronously
2. **Database Indexing**: Proper indexes are in place
3. **Error Handling**: Comprehensive error handling implemented

### 6.3 Monitoring

1. **Log Monitoring**: Monitor Laravel logs for errors
2. **Queue Monitoring**: Monitor queue processing
3. **Database Monitoring**: Monitor database performance
4. **Twilio Monitoring**: Monitor webhook delivery in Twilio console

## Step 7: Testing Your Setup

### 7.1 Send Test Message

1. Send a WhatsApp message to your Twilio number
2. Check Laravel logs for webhook receipt
3. Verify conversation and message are created in database
4. Check that the message appears in your frontend

### 7.2 Test Media Messages

1. Send an image or document via WhatsApp
2. Verify media file is processed correctly
3. Check that file URL is stored in database
4. Ensure media is accessible via your application

### 7.3 Test Bulk Messages

1. Use the bulk message API to send to multiple numbers
2. Verify all messages are sent successfully
3. Check message status tracking

## Step 8: Advanced Configuration

### 8.1 Custom Webhook Headers

If needed, you can add custom headers to your webhook:

```php
// In your webhook middleware
public function handle(Request $request, Closure $next)
{
    // Add custom headers if needed
    $request->headers->set('X-Custom-Header', 'value');

    return $next($request);
}
```

### 8.2 Webhook Signature Verification

For additional security, verify Twilio webhook signatures:

```php
// In your webhook controller
public function webhook(Request $request)
{
    // Verify Twilio signature
    $signature = $request->header('X-Twilio-Signature');
    $url = $request->fullUrl();
    $params = $request->all();

    if (!$this->verifyTwilioSignature($signature, $url, $params)) {
        return response()->json(['error' => 'Invalid signature'], 403);
    }

    // Process webhook
    return $this->twilioService->handleWebhook($request);
}
```

### 8.3 Error Handling

Implement comprehensive error handling:

```php
// In your webhook job
public function handle()
{
    try {
        // Process webhook
    } catch (\Exception $e) {
        Log::error('Webhook processing failed', [
            'error' => $e->getMessage(),
            'webhook_data' => $this->webhookData
        ]);

        // Retry the job
        $this->release(60); // Retry in 1 minute
    }
}
```

## Step 9: Monitoring and Alerts

### 9.1 Set Up Monitoring

1. **Log Monitoring**: Use tools like Papertrail or Loggly
2. **Queue Monitoring**: Monitor queue processing
3. **Database Monitoring**: Monitor database performance
4. **Uptime Monitoring**: Monitor webhook endpoint availability

### 9.2 Set Up Alerts

1. **Error Alerts**: Alert on webhook processing errors
2. **Queue Alerts**: Alert on queue failures
3. **Performance Alerts**: Alert on slow response times
4. **Availability Alerts**: Alert on webhook endpoint downtime

## Step 10: Maintenance

### 10.1 Regular Checks

1. **Daily**: Check webhook logs for errors
2. **Weekly**: Review queue processing statistics
3. **Monthly**: Review webhook delivery rates
4. **Quarterly**: Review and update security measures

### 10.2 Backup and Recovery

1. **Database Backups**: Regular backups of conversation and message data
2. **Configuration Backups**: Backup webhook configurations
3. **Recovery Procedures**: Document recovery procedures

## Support

If you encounter issues:

1. **Check Logs**: Review Laravel logs for errors
2. **Test Endpoint**: Use the test script to verify webhook
3. **Twilio Support**: Contact Twilio support for webhook issues
4. **Documentation**: Refer to Twilio webhook documentation

## Additional Resources

-   [Twilio Webhook Documentation](https://www.twilio.com/docs/messaging/guides/webhook)
-   [WhatsApp Business API Documentation](https://developers.facebook.com/docs/whatsapp)
-   [Laravel Queue Documentation](https://laravel.com/docs/queues)
-   [Laravel Logging Documentation](https://laravel.com/docs/logging)
