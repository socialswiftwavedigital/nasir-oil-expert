<?php
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

$name    = clean($data['name']);
$phone   = clean($data['phone']);
$purpose = clean($data['purpose'] ?? 'order');
$city    = clean($data['city'] ?? '');
$address = clean($data['address'] ?? '');
$note    = clean($data['note'] ?? '');
$body    = $data['body'] ?? '';

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
echo json_encode(['ok' => (bool)$sent]);
