<?php
/* ══════════════════════════════════════════════════
   Nasir Oil Expert — One-Time Database Setup Script
   Run once: nasiroilexpert.com/setup-db.php?key=nasir@admin2024
   DELETE this file from Hostinger after running!
═══════════════════════════════════════════════════ */
require_once __DIR__ . '/config.php';

if (($_GET['key'] ?? '') !== ADMIN_PASS) {
    http_response_code(403);
    die('<h2>403 Unauthorized</h2><p>?key=ADMIN_PASS required.</p>');
}

if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
    die('<h2>DB credentials missing</h2><p>config.php mein DB_HOST, DB_NAME, DB_USER, DB_PASS define karein.</p>');
}

require_once __DIR__ . '/db.php';
$db = getDB();

$log = [];

/* ── Create Tables ── */
$db->exec("CREATE TABLE IF NOT EXISTS orders (
    id          VARCHAR(20)     NOT NULL PRIMARY KEY,
    created_at  DATETIME        NOT NULL,
    name        VARCHAR(100)    DEFAULT '',
    phone       VARCHAR(25)     DEFAULT '',
    city        VARCHAR(80)     DEFAULT '',
    address     TEXT,
    product     VARCHAR(150)    DEFAULT '',
    price       DECIMAL(10,2)   DEFAULT 0,
    source_page VARCHAR(200)    DEFAULT '',
    tracking    VARCHAR(100)    DEFAULT '',
    status      VARCHAR(20)     DEFAULT 'pending',
    admin_note  TEXT,
    duplicate   TINYINT(1)      DEFAULT 0,
    timeline    TEXT,
    INDEX idx_status (status),
    INDEX idx_phone  (phone),
    INDEX idx_date   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$log[] = '✅ Table `orders` ready';

$db->exec("CREATE TABLE IF NOT EXISTS abandoned_forms (
    id          VARCHAR(10)     NOT NULL PRIMARY KEY,
    created_at  DATETIME        NOT NULL,
    name        VARCHAR(100)    DEFAULT '',
    phone       VARCHAR(25)     DEFAULT '',
    product     VARCHAR(150)    DEFAULT '',
    page        VARCHAR(200)    DEFAULT '',
    status      VARCHAR(20)     DEFAULT 'new',
    INDEX idx_status (status),
    INDEX idx_phone  (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$log[] = '✅ Table `abandoned_forms` ready';

$db->exec("CREATE TABLE IF NOT EXISTS stock (
    slug    VARCHAR(40)     NOT NULL PRIMARY KEY,
    name    VARCHAR(100)    DEFAULT '',
    price   INT             DEFAULT 0,
    qty     INT             DEFAULT 0,
    sku     VARCHAR(20)     DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$log[] = '✅ Table `stock` ready';

/* ── Helper ── */
function readJsonFile($f) {
    if (!file_exists($f)) return [];
    return json_decode(file_get_contents($f), true) ?: [];
}

/* ── Import Orders ── */
$orders  = readJsonFile(__DIR__ . '/orders-data.json');
$ordCnt  = 0;
if ($orders) {
    $stmt = $db->prepare("INSERT IGNORE INTO orders
        (id,created_at,name,phone,city,address,product,price,source_page,tracking,status,admin_note,duplicate,timeline)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($orders as $o) {
        $ts = strtotime($o['date'] ?? '') ?: time();
        $stmt->execute([
            $o['id']         ?? '',
            date('Y-m-d H:i:s', $ts),
            $o['name']       ?? '',
            $o['phone']      ?? '',
            $o['city']       ?? '',
            $o['address']    ?? '',
            $o['product']    ?? '',
            (float)($o['price']  ?? 0),
            $o['source_page'] ?? '',
            $o['tracking']   ?? '',
            $o['status']     ?? 'pending',
            $o['admin_note'] ?? '',
            (int)(!empty($o['duplicate'])),
            json_encode($o['timeline'] ?? []),
        ]);
        $ordCnt++;
    }
}
$log[] = "📦 Orders imported: $ordCnt";

/* ── Import Abandoned Forms ── */
$abandoned = readJsonFile(__DIR__ . '/abandoned-forms.json');
$abCnt = 0;
if ($abandoned) {
    $stmt2 = $db->prepare("INSERT IGNORE INTO abandoned_forms
        (id,created_at,name,phone,product,page,status)
        VALUES (?,?,?,?,?,?,?)");
    foreach ($abandoned as $a) {
        $ts = strtotime($a['date'] ?? '') ?: time();
        $stmt2->execute([
            $a['id']      ?? '',
            date('Y-m-d H:i:s', $ts),
            $a['name']    ?? '',
            $a['phone']   ?? '',
            $a['product'] ?? '',
            $a['page']    ?? '',
            $a['status']  ?? 'new',
        ]);
        $abCnt++;
    }
}
$log[] = "🔔 Abandoned forms imported: $abCnt";

/* ── Import / Upsert Stock ── */
$stock   = readJsonFile(__DIR__ . '/stock.json');
$stCnt   = 0;
if ($stock) {
    $stmt3 = $db->prepare("INSERT INTO stock (slug,name,price,qty,sku)
        VALUES (?,?,?,?,?)
        ON DUPLICATE KEY UPDATE name=VALUES(name),price=VALUES(price),qty=VALUES(qty),sku=VALUES(sku)");
    foreach ($stock as $slug => $p) {
        $stmt3->execute([$slug, $p['name'], (int)$p['price'], (int)$p['stock'], $p['sku']]);
        $stCnt++;
    }
}
$log[] = "🏪 Stock rows upserted: $stCnt";

/* ── Verify ── */
$ordTotal = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$abTotal  = $db->query("SELECT COUNT(*) FROM abandoned_forms")->fetchColumn();
$stTotal  = $db->query("SELECT COUNT(*) FROM stock")->fetchColumn();
$log[] = "--- DB totals: orders=$ordTotal | abandoned=$abTotal | stock=$stTotal ---";
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>NOE Setup</title>
<style>body{font-family:monospace;background:#0d1117;color:#58a6ff;padding:40px;}
h1{color:#3fb950;margin-bottom:20px;}
.log{background:#161b22;border:1px solid #30363d;border-radius:8px;padding:20px;line-height:2;}
.warn{color:#f0883e;margin-top:24px;font-size:1.1rem;font-weight:bold;}
a{color:#58a6ff;}
</style></head><body>
<h1>✅ Nasir Oil Expert — Database Setup Complete</h1>
<div class="log">
<?php foreach ($log as $l) echo htmlspecialchars($l) . '<br>'; ?>
</div>
<p class="warn">⚠️ IMPORTANT: Ye file ab DELETE karo Hostinger File Manager se!</p>
<p style="margin-top:12px;color:#8b949e;">
  Admin panel: <a href="admin-dashboard.php">admin-dashboard.php</a><br>
  Client panel: <a href="client-panel.php">client-panel.php</a>
</p>
</body></html>
