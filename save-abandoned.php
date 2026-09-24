<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://nasiroilexpert.com');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); exit; }

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$name  = htmlspecialchars(strip_tags(trim($data['name']  ?? '')), ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars(strip_tags(trim($data['phone'] ?? '')), ENT_QUOTES, 'UTF-8');

// Need at least phone or name
if (!$name && !$phone) { echo json_encode(['ok'=>false]); exit; }

$file = __DIR__ . '/abandoned-forms.json';
$list = file_exists($file) ? json_decode(file_get_contents($file), true) ?: [] : [];

// Deduplicate: same phone within 30 min = update existing
$now = time();
foreach ($list as &$item) {
    if ($phone && ($item['phone'] ?? '') === $phone && ($now - ($item['ts'] ?? 0)) < 1800) {
        $item['name']    = $name    ?: $item['name'];
        $item['product'] = htmlspecialchars(strip_tags(trim($data['product'] ?? '')), ENT_QUOTES, 'UTF-8') ?: $item['product'];
        $item['page']    = htmlspecialchars(strip_tags(trim($data['page']    ?? '')), ENT_QUOTES, 'UTF-8');
        $item['ts']      = $now;
        $item['date']    = date('d M Y, h:i A');
        file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['ok'=>true]); exit;
    }
}
unset($item);

array_unshift($list, [
    'id'      => 'AB-' . strtoupper(substr(md5(uniqid()), 0, 5)),
    'date'    => date('d M Y, h:i A'),
    'ts'      => $now,
    'name'    => $name,
    'phone'   => $phone,
    'product' => htmlspecialchars(strip_tags(trim($data['product'] ?? '')), ENT_QUOTES, 'UTF-8'),
    'page'    => htmlspecialchars(strip_tags(trim($data['page']    ?? '')), ENT_QUOTES, 'UTF-8'),
    'status'  => 'new',
]);

// Keep last 200 only
$list = array_slice($list, 0, 200);
file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok'=>true]);
