<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$name    = htmlspecialchars(trim($_POST['name']    ?? ''));
$phone   = htmlspecialchars(trim($_POST['phone']   ?? ''));
$city    = htmlspecialchars(trim($_POST['city']    ?? ''));
$address = htmlspecialchars(trim($_POST['address'] ?? ''));
$product = htmlspecialchars(trim($_POST['product'] ?? ''));
$price   = htmlspecialchars(trim($_POST['price']   ?? ''));
$source  = htmlspecialchars(trim($_POST['source']  ?? 'direct'));

if (!$name || !$phone || !$city || !$address || !$product) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$orderId = date('ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));

$order = [
    'id'      => $orderId,
    'date'    => date('d M Y, h:i A'),
    'name'    => $name,
    'phone'   => $phone,
    'city'    => $city,
    'address' => $address,
    'product' => $product,
    'price'   => $price,
    'source'  => $source,
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
    'status'  => 'pending',
];

// Save to orders.json
$file   = __DIR__ . '/orders-data.json';
$orders = [];
if (file_exists($file)) {
    $raw = file_get_contents($file);
    $orders = json_decode($raw, true) ?: [];
}
array_unshift($orders, $order); // newest first
file_put_contents($file, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Send email notification
$to      = 'info@nasiroilexpert.com';
$subject = "New COD Order #$orderId — $product";
$body    = "=== NEW ORDER RECEIVED ===\n\n";
$body   .= "Order ID : $orderId\n";
$body   .= "Date     : " . date('d M Y, h:i A') . "\n\n";
$body   .= "CUSTOMER\n";
$body   .= "--------\n";
$body   .= "Name     : $name\n";
$body   .= "Phone    : $phone\n";
$body   .= "City     : $city\n";
$body   .= "Address  : $address\n\n";
$body   .= "PRODUCT\n";
$body   .= "-------\n";
$body   .= "Product  : $product\n";
$body   .= "Price    : Rs $price\n";
$body   .= "Payment  : Cash on Delivery\n\n";
$body   .= "Ordered from: $source\n";
$body   .= "\nView all orders: https://nasiroilexpert.com/admin-orders.php";

$headers  = "From: orders@nasiroilexpert.com\r\n";
$headers .= "Reply-To: info@nasiroilexpert.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

mail($to, $subject, $body, $headers);

echo json_encode(['success' => true, 'order_id' => $orderId]);
