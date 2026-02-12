<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user

// Get user input
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please type something!']);
    exit;
}

// ======================
// Gemini API Configuration
// ======================
$api_key = "YOUR_API_KEY_HERE";
// 🔑 Your API key

// Use correct model name (gemini-2.5-pro doesn't exist)
$model = "gemini-1.5-flash"; // or "gemini-1.5-pro" or "gemini-2.0-flash-exp"
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $api_key;

// Prepare data
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $userMessage]
            ]
        ]
    ]
];

// Use cURL instead of file_get_contents (more reliable)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle cURL errors
if ($response === false) {
    error_log("cURL Error: " . $curlError);
    echo json_encode(["reply" => "⚠️ Connection error: " . $curlError]);
    exit;
}

// Parse response
$json = json_decode($response, true);

// Log full response for debugging
error_log("API Response (HTTP $httpCode): " . $response);

// Extract reply safely
if (isset($json["candidates"][0]["content"]["parts"][0]["text"])) {
    $reply = $json["candidates"][0]["content"]["parts"][0]["text"];
} elseif (isset($json["error"]["message"])) {
    $reply = "❌ API Error: " . $json["error"]["message"];
    if (isset($json["error"]["status"])) {
        $reply .= " (Status: " . $json["error"]["status"] . ")";
    }
} else {
    $reply = "⚠️ Unexpected response format. HTTP Code: $httpCode";
}

echo json_encode(["reply" => $reply]);
?>
