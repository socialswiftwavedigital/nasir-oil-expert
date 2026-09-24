<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

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

/* ── Stock decrement (MySQL) ───────────────────────────────────── */
function decrementStock($prodName, $db) {
    $rows = $db->query("SELECT slug, name, qty, sku FROM stock")->fetchAll();
    foreach ($rows as $r) {
        if (stripos($prodName, $r['name']) !== false || stripos($r['name'], $prodName) !== false) {
            if ($r['qty'] > 0) {
                $db->prepare("UPDATE stock SET qty = qty - 1 WHERE slug = ?")->execute([$r['slug']]);
                $newQty = $r['qty'] - 1;
                if ($newQty <= 10) {
                    $subj = '[LOW STOCK ⚠️] ' . $r['name'] . ' — ' . $newQty . ' units left';
                    $msg  = "Stock Alert — Nasir Oil Expert\n\nProduct: " . $r['name']
                          . "\nSKU: " . $r['sku'] . "\nStock: $newQty units remaining\n\nRefill soon.";
                    mail('info@nasiroilexpert.com', $subj, $msg, 'From: Nasir Oil Expert <info@nasiroilexpert.com>');
                }
            }
            break;
        }
    }
}

/* ── Duplicate detection (MySQL) ──────────────────────────────── */
function isDuplicate($phone, $db) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE phone = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
    $stmt->execute([$phone]);
    return $stmt->fetchColumn() > 0;
}

/* ── CAPI ──────────────────────────────────────────────────────── */
function sendCAPIEvent($event_id, $product, $value, $phone, $client_ip, $client_ua) {
    $token = META_ACCESS_TOKEN;
    if (empty($token)) return;
    $phone_clean = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone_clean, 0, 1) === '0') $phone_clean = '92' . substr($phone_clean, 1);
    $payload = json_encode(['data' => [[
        'event_name'       => 'Purchase',
        'event_time'       => time(),
        'event_id'         => $event_id,
        'action_source'    => 'website',
        'event_source_url' => 'https://nasiroilexpert.com',
        'user_data' => [
            'ph'                 => [hash('sha256', $phone_clean)],
            'client_ip_address'  => $client_ip,
            'client_user_agent'  => $client_ua,
        ],
        'custom_data' => [
            'value'        => (float)$value,
            'currency'     => 'PKR',
            'content_name' => $product,
            'content_type' => 'product',
            'num_items'    => 1,
        ],
    ]]]);
    $url = 'https://graph.facebook.com/v19.0/' . META_PIXEL_ID . '/events?access_token=' . urlencode($token);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>5]);
    curl_exec($ch); curl_close($ch);
}

/* ── Parse request ─────────────────────────────────────────────── */
$name        = clean($data['name']);
$phone       = clean($data['phone']);
$purpose     = clean($data['purpose'] ?? 'order');
$city        = clean($data['city'] ?? '');
$address     = clean($data['address'] ?? '');
$note        = clean($data['note'] ?? '');
$body        = $data['body'] ?? '';
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

/* ── Save COD order (MySQL) ────────────────────────────────────── */
if ($purpose === 'order') {
    $db       = getDB();
    $orderId  = date('ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));
    $prodName = clean($data['product'] ?? '');
    $price    = (float)($data['value'] ?? 0);
    $duplicate = isDuplicate($phone, $db) ? 1 : 0;
    $timeline  = json_encode([['status'=>'pending','time'=>date('d M Y, h:i A'),'note'=>'Order received']]);

    $db->prepare("INSERT INTO orders
        (id,created_at,name,phone,city,address,product,price,source_page,status,duplicate,timeline)
        VALUES (?,NOW(),?,?,?,?,?,?,?,'pending',?,?)")
       ->execute([$orderId, $name, $phone, $city, $address, $prodName ?: $body,
                  $price > 0 ? $price : 0, $source_page, $duplicate, $timeline]);

    if ($prodName) decrementStock($prodName, $db);
}

/* ── CAPI ──────────────────────────────────────────────────────── */
if ($purpose === 'order') {
    $event_id  = clean($data['event_id'] ?? '');
    $value     = (float)($data['value'] ?? 0);
    $product   = clean($data['product'] ?? '');
    $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR']
                 ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]
                 : ($_SERVER['REMOTE_ADDR'] ?? '');
    $client_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($event_id && $value > 0) {
        sendCAPIEvent($event_id, $product, $value, $phone, trim($client_ip), $client_ua);
    }
}

echo json_encode(['ok' => (bool)$sent]);
