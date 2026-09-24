<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://nasiroilexpert.com');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false]); exit; }

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['name']) || empty($data['phone'])) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Missing fields']);
    exit;
}

function clean($v) { return htmlspecialchars(strip_tags(trim($v ?? '')), ENT_QUOTES, 'UTF-8'); }

function sendCAPIEvent($event_id, $product, $value, $phone, $client_ip, $client_ua) {
    $token = META_ACCESS_TOKEN;
    if (empty($token)) return; // Skip until token is configured

    $phone_clean = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone_clean, 0, 1) === '0') $phone_clean = '92' . substr($phone_clean, 1);

    $payload = json_encode([
        'data' => [[
            'event_name'       => 'Purchase',
            'event_time'       => time(),
            'event_id'         => $event_id,
            'action_source'    => 'website',
            'event_source_url' => 'https://nasiroilexpert.com',
            'user_data' => [
                'ph'         => [hash('sha256', $phone_clean)],
                'client_ip_address' => $client_ip,
                'client_user_agent' => $client_ua,
            ],
            'custom_data' => [
                'value'        => (float)$value,
                'currency'     => 'PKR',
                'content_name' => $product,
                'content_type' => 'product',
                'num_items'    => 1,
            ],
        ]],
    ]);

    $url = 'https://graph.facebook.com/v19.0/' . META_PIXEL_ID . '/events?access_token=' . urlencode($token);
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_POST,          true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,    $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER,    ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_TIMEOUT,       5);
    curl_exec($ch);
    curl_close($ch);
}

$name       = clean($data['name']);
$phone      = clean($data['phone']);
$purpose    = clean($data['purpose'] ?? 'order');
$city       = clean($data['city'] ?? '');
$address    = clean($data['address'] ?? '');
$note       = clean($data['note'] ?? '');
$body       = $data['body'] ?? '';
$source_page = clean($data['source_page'] ?? '');

$labels  = ['order'=>'New Order','inquiry'=>'Inquiry','complaint'=>'Complaint','return'=>'Return/Refund'];
$label   = $labels[$purpose] ?? 'Message';

$subject = ($purpose === 'order' ? '[ORDER] ' : '[' . strtoupper($purpose) . '] ') . $name . ' — ' . ($city ?: $phone);

$msg  = "===== " . strtoupper($label) . " =====\n\n";
$msg .= $body . "\n\n";
$msg .= "-------------------------------\n";
$msg .= "Name    : $name\n";
$msg .= "Phone   : $phone\n";
if ($city)    $msg .= "City    : $city\n";
if ($address) $msg .= "Address : $address\n";
if ($note)    $msg .= "Notes   : $note\n";
$msg .= "-------------------------------\n";
$msg .= "Sent from nasiroilexpert.com\n";

$to      = 'info@nasiroilexpert.com';
$headers = implode("\r\n", [
    'From: Nasir Oil Expert <info@nasiroilexpert.com>',
    'Reply-To: ' . $name . ' <' . $phone . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
]);

$sent = mail($to, $subject, $msg, $headers);

// Save COD order to orders-data.json for admin panel
if ($purpose === 'order') {
    $orderId  = date('ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));
    $prodName = clean($data['product'] ?? '');
    $price    = (float)($data['value'] ?? 0);
    $order    = [
        'id'      => $orderId,
        'date'    => date('d M Y, h:i A'),
        'name'    => $name,
        'phone'   => $phone,
        'city'    => $city,
        'address' => $address,
        'product' => $prodName ?: $body,
        'price'       => $price > 0 ? $price : '',
        'source'      => 'COD',
        'source_page' => $source_page,
        'status'      => 'pending',
    ];
    $jsonFile = __DIR__ . '/orders-data.json';
    $existing = [];
    if (file_exists($jsonFile)) {
        $existing = json_decode(file_get_contents($jsonFile), true) ?: [];
    }
    array_unshift($existing, $order);
    file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Fire CAPI Purchase event (server-side, for deduplication with browser Pixel)
if ($purpose === 'order') {
    $event_id  = clean($data['event_id'] ?? '');
    $value     = (float)($data['value']    ?? 0);
    $product   = clean($data['product']   ?? '');
    $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR']
                 ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]
                 : ($_SERVER['REMOTE_ADDR'] ?? '');
    $client_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($event_id && $value > 0) {
        sendCAPIEvent($event_id, $product, $value, $phone, trim($client_ip), $client_ua);
    }
}

echo json_encode(['ok' => (bool)$sent]);
