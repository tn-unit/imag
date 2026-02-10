<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$baseUrl = rtrim((string)($input['baseUrl'] ?? ''), '/');
$endpoint = '/' . ltrim((string)($input['endpoint'] ?? ''), '/');
$token = trim((string)($input['token'] ?? ''));
$nip = trim((string)($input['nip'] ?? ''));
$payload = $input['payload'] ?? [];

if ($baseUrl === '' || $endpoint === '/') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'baseUrl and endpoint are required']);
    exit;
}

$url = $baseUrl . $endpoint;

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
];

// Nagłówki mogą się różnić zależnie od endpointu i wersji API.
if ($token !== '') {
    $headers[] = 'Authorization: Bearer ' . $token;
}
if ($nip !== '') {
    $headers[] = 'NIP: ' . $nip;
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 60,
]);

$raw = curl_exec($ch);
$curlErr = curl_error($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'cURL error', 'details' => $curlErr]);
    exit;
}

$decoded = json_decode($raw, true);
$responseBody = json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;

http_response_code($status >= 100 ? $status : 200);
echo json_encode([
    'ok' => $status >= 200 && $status < 300,
    'status' => $status,
    'response' => $responseBody,
], JSON_UNESCAPED_UNICODE);
