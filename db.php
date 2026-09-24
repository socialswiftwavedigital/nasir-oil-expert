<?php
function getDB() {
    static $pdo = null;
    if ($pdo) return $pdo;
    try {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"]
        );
    } catch (PDOException $e) {
        error_log('NOE DB Error: ' . $e->getMessage());
        die('<h2>Database connection failed.</h2><p>config.php mein DB credentials check karein.</p>');
    }
    return $pdo;
}

/* Fetch all orders with formatted date */
function dbOrders($db, $where = '', $params = []) {
    $sql = "SELECT *, DATE_FORMAT(created_at,'%d %b %Y, %h:%i %p') as `date` FROM orders" . ($where ? " WHERE $where" : '') . " ORDER BY created_at DESC";
    $s = $db->prepare($sql);
    $s->execute($params);
    return $s->fetchAll();
}

/* Fetch stock as keyed array matching legacy $stock structure */
function dbStock($db) {
    $rows = $db->query("SELECT * FROM stock ORDER BY name")->fetchAll();
    $out  = [];
    foreach ($rows as $r) {
        $out[$r['slug']] = ['name'=>$r['name'], 'price'=>$r['price'], 'stock'=>$r['qty'], 'sku'=>$r['sku']];
    }
    return $out;
}
