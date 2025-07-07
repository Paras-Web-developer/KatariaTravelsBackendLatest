<?php
/**
 * Test script to verify WhatsApp webhook functionality
 * Run this script to test if your webhook endpoint is working
 */

// Configuration
$webhookUrl = 'https://api.katariatravel.ca/api/whatsapp/webhook'; // Replace with your actual domain
$testWebhookUrl = 'https://api.katariatravel.ca/api/whatsapp/webhook-test';

// Sample Twilio webhook data
$webhookData = [
    'From' => 'whatsapp:+1234567890',
    'To' => 'whatsapp:+0987654321',
    'MessageSid' => 'MG' . uniqid(),
    'Body' => 'Hello from test webhook!',
    'NumMedia' => '0',
    'ProfileName' => 'Test User',
    'WaId' => '1234567890'
];

echo "Testing WhatsApp Webhook...\n";
echo "Webhook URL: $webhookUrl\n";
echo "Test Webhook URL: $testWebhookUrl\n\n";

// Function to send POST request
function sendWebhookTest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: Twilio-Webhook-Test'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Test 1: Test webhook endpoint
echo "=== Test 1: Testing webhook endpoint ===\n";
$result1 = sendWebhookTest($webhookUrl, $webhookData);
echo "HTTP Code: " . $result1['http_code'] . "\n";
echo "Response: " . $result1['response'] . "\n";
if ($result1['error']) {
    echo "Error: " . $result1['error'] . "\n";
}
echo "\n";

// Test 2: Test webhook with media
echo "=== Test 2: Testing webhook with media ===\n";
$webhookDataWithMedia = array_merge($webhookData, [
    'NumMedia' => '1',
    'MediaUrl0' => 'https://example.com/image.jpg',
    'MediaContentType0' => 'image/jpeg',
    'MediaFileName0' => 'test.jpg',
    'MediaSize0' => '1024000'
]);
$result2 = sendWebhookTest($webhookUrl, $webhookDataWithMedia);
echo "HTTP Code: " . $result2['http_code'] . "\n";
echo "Response: " . $result2['response'] . "\n";
if ($result2['error']) {
    echo "Error: " . $result2['error'] . "\n";
}
echo "\n";

// Test 3: Test debug endpoint
echo "=== Test 3: Testing debug endpoint ===\n";
$result3 = sendWebhookTest($testWebhookUrl, $webhookData);
echo "HTTP Code: " . $result3['http_code'] . "\n";
echo "Response: " . $result3['response'] . "\n";
if ($result3['error']) {
    echo "Error: " . $result3['error'] . "\n";
}
echo "\n";

echo "=== Summary ===\n";
if ($result1['http_code'] == 200) {
    echo "✅ Main webhook endpoint is working\n";
} else {
    echo "❌ Main webhook endpoint failed (HTTP: " . $result1['http_code'] . ")\n";
}

if ($result3['http_code'] == 200) {
    echo "✅ Debug webhook endpoint is working\n";
} else {
    echo "❌ Debug webhook endpoint failed (HTTP: " . $result3['http_code'] . ")\n";
}

echo "\nInstructions:\n";
echo "1. Replace 'your-domain.com' with your actual domain\n";
echo "2. Make sure your Laravel application is running\n";
echo "3. Ensure the webhook routes are accessible\n";
echo "4. Check your Laravel logs for any errors\n";
?>