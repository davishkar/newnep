<?php
header('Content-Type: application/json');
// Allow CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$apiKey = getenv('GOOGLE_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'server_missing_api_key', 'message' => 'Set GOOGLE_API_KEY in the environment on the server.']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? [];
$text = $input['text'] ?? $input['message'] ?? '';
if (!$text) {
    http_response_code(400);
    echo json_encode(['error' => 'no_text_provided', 'message' => "POST JSON must include a 'text' field."]);
    exit;
}

$model = 'gemini-2.5-pro';
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

$payload = json_encode([
    'contents' => [
        ['parts' => [['text' => $text]]]
    ]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($response === false) {
    $err = curl_error($ch);
    curl_close($ch);
    http_response_code(502);
    echo json_encode(['error' => 'request_exception', 'message' => $err]);
    exit;
}
curl_close($ch);

if ($httpcode >= 400) {
    http_response_code(502);
    echo json_encode(['error' => 'upstream_error', 'status_code' => $httpcode, 'raw' => $response]);
    exit;
}

$rd = json_decode($response, true);
$candidate_text = null;
if (!empty($rd['candidates']) && is_array($rd['candidates'])) {
    $first = $rd['candidates'][0];
    if (isset($first['content']['parts'][0]['text'])) {
        $candidate_text = $first['content']['parts'][0]['text'];
    }
}

if (!$candidate_text) {
    if (isset($rd['output']) && is_array($rd['output']) && isset($rd['output']['text'])) {
        $candidate_text = $rd['output']['text'];
    }
}

if (!$candidate_text) {
    http_response_code(502);
    echo json_encode(['error' => 'no_text_in_response', 'raw' => $rd]);
    exit;
}

echo json_encode(['text' => $candidate_text]);

?>