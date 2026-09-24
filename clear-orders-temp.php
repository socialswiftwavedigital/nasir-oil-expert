<?php
// ONE-TIME USE — delete this file after running
require_once __DIR__ . '/config.php';
if (($_GET['key'] ?? '') !== ADMIN_PASS) { die('Unauthorized'); }
file_put_contents(__DIR__ . '/orders-data.json', '[]');
echo 'Done — all orders cleared. Delete this file now.';
