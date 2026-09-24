<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://nasiroilexpert.com');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); exit; }

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$name  = htmlspecialchars(strip_tags(trim($data['name']    ?? '')), ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars(strip_tags(trim($data['phone']   ?? '')), ENT_QUOTES, 'UTF-8');
$prod  = htmlspecialchars(strip_tags(trim($data['product'] ?? '')), ENT_QUOTES, 'UTF-8');
$page  = htmlspecialchars(strip_tags(trim($data['page']    ?? '')), ENT_QUOTES, 'UTF-8');

if (!$name && !$phone) { echo json_encode(['ok'=>false]); exit; }

$db = getDB();

// Deduplicate: same phone within 30 min → update existing
if ($phone) {
    $existing = $db->prepare("SELECT id FROM abandoned_forms WHERE phone=? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) LIMIT 1");
    $existing->execute([$phone]);
    $row = $existing->fetch();
    if ($row) {
        $db->prepare("UPDATE abandoned_forms SET name=?, product=?, page=?, created_at=NOW() WHERE id=?")
           ->execute([$name ?: '', $prod, $page, $row['id']]);
        echo json_encode(['ok'=>true]); exit;
    }
}

// Insert new record (keep last 200 — MySQL handles volume fine, skip trim)
$id = 'AB-' . strtoupper(substr(md5(uniqid()), 0, 5));
$db->prepare("INSERT INTO abandoned_forms (id,created_at,name,phone,product,page,status) VALUES (?,NOW(),?,?,?,?,'new')")
   ->execute([$id, $name, $phone, $prod, $page]);

echo json_encode(['ok'=>true]);
