<?php
require_once __DIR__ . '/config.php';

/* ── Auth ─────────────────────────────────────────────────────── */
session_start();
if (($_POST['pass'] ?? '') === ADMIN_PASS) $_SESSION['noe_admin'] = true;
if (($_GET['logout'] ?? '') === '1') { session_destroy(); header('Location: admin-dashboard.php'); exit; }
if (!($_SESSION['noe_admin'] ?? false)) { showLogin(); exit; }

/* ── Helpers ──────────────────────────────────────────────────── */
function readJson($f) {
    if (!file_exists($f)) return [];
    return json_decode(file_get_contents($f), true) ?: [];
}
function writeJson($f, $d) {
    file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function clean($v) { return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function waNumber($phone) {
    $n = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($n) < 7) return '';
    if (substr($n, 0, 2) === '92') return $n;
    if (substr($n, 0, 1) === '0')  return '92' . substr($n, 1);
    if (strlen($n) === 10)          return '92' . $n;
    return '92' . $n;
}
function pageLabel($path) {
    $map = [
        'golden-oil'     => '🟡 Golden Hair Oil',
        'black-rose-oil' => '🌹 Black Rose Oil',
        'herbal-shampoo' => '🌿 Herbal Shampoo',
        'bundle-kit'     => '📦 Bundle Kit',
        'index'          => '🏠 Home',
        'products'       => '🛒 Products',
    ];
    foreach ($map as $key => $label) {
        if (str_contains($path, $key)) return $label;
    }
    return $path ?: '—';
}

/* ── Data ─────────────────────────────────────────────────────── */
$orders = readJson(__DIR__ . '/orders-data.json');
$stock  = readJson(__DIR__ . '/stock.json');
$page   = $_GET['p'] ?? 'dashboard';

/* ── POST Actions ─────────────────────────────────────────────── */
if ($_POST['action'] ?? '' === 'update_status') {
    $id = $_POST['id'] ?? '';
    $st = $_POST['status'] ?? 'pending';
    foreach ($orders as &$o) { if ($o['id'] === $id) { $o['status'] = $st; break; } }
    unset($o);
    writeJson(__DIR__ . '/orders-data.json', $orders);
    header('Location: admin-dashboard.php?p=orders'); exit;
}
if ($_POST['action'] ?? '' === 'update_note') {
    $id   = $_POST['id'] ?? '';
    $note = clean($_POST['note'] ?? '');
    foreach ($orders as &$o) { if ($o['id'] === $id) { $o['admin_note'] = $note; break; } }
    unset($o);
    writeJson(__DIR__ . '/orders-data.json', $orders);
    header('Location: admin-dashboard.php?p=orders'); exit;
}
if ($_POST['action'] ?? '' === 'update_stock') {
    foreach ($stock as $k => &$p) {
        if (isset($_POST["stock_$k"])) $p['stock'] = (int)$_POST["stock_$k"];
        if (isset($_POST["price_$k"])) $p['price'] = (int)$_POST["price_$k"];
    }
    unset($p);
    writeJson(__DIR__ . '/stock.json', $stock);
    header('Location: admin-dashboard.php?p=stock'); exit;
}

/* ── CSV Export ───────────────────────────────────────────────── */
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="nasir-orders-' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF";
    echo "Order ID,Date,Name,Phone,City,Address,Product,Price,Status,Source Page,Note\n";
    foreach ($orders as $o) {
        echo '"' . implode('","', [
            $o['id'] ?? '', $o['date'] ?? '', $o['name'] ?? '', $o['phone'] ?? '',
            $o['city'] ?? '', $o['address'] ?? '', $o['product'] ?? '',
            $o['price'] ?? '', $o['status'] ?? '', $o['source_page'] ?? '', $o['admin_note'] ?? ''
        ]) . '"' . "\n";
    }
    exit;
}

/* ── Stats ────────────────────────────────────────────────────── */
function orderStats($orders) {
    $today = date('d M Y');
    $week  = strtotime('-7 days');
    $s = ['total'=>0,'pending'=>0,'done'=>0,'cancelled'=>0,'revenue'=>0,'today'=>0,'week_rev'=>0];
    foreach ($orders as $o) {
        $s['total']++;
        $st = $o['status'] ?? 'pending';
        $s[$st] = ($s[$st] ?? 0) + 1;
        $rev = (float)($o['price'] ?? 0);
        $s['revenue'] += $rev;
        if (str_starts_with($o['date'] ?? '', $today)) $s['today']++;
        $ot = strtotime($o['date'] ?? '');
        if ($ot && $ot >= $week) $s['week_rev'] += $rev;
    }
    return $s;
}
function productSales($orders) {
    $map = [];
    foreach ($orders as $o) {
        $p = $o['product'] ?? 'Unknown';
        if (!isset($map[$p])) $map[$p] = ['count'=>0,'revenue'=>0];
        $map[$p]['count']++;
        $map[$p]['revenue'] += (float)($o['price'] ?? 0);
    }
    arsort($map);
    return $map;
}
function getCustomers($orders) {
    $c = [];
    foreach ($orders as $o) {
        $phone = $o['phone'] ?? '';
        if (!$phone) continue;
        if (!isset($c[$phone])) {
            $c[$phone] = ['name'=>$o['name']??'','phone'=>$phone,'city'=>$o['city']??'','orders'=>[],'total_spent'=>0];
        }
        $c[$phone]['orders'][] = $o;
        $c[$phone]['total_spent'] += (float)($o['price'] ?? 0);
    }
    uasort($c, fn($a,$b) => count($b['orders']) - count($a['orders']));
    return $c;
}
function fetchMetaStats($token, $pixel_id) {
    if (!$token) return null;
    $events = ['Purchase','ViewContent','AddToCart','InitiateCheckout','Contact'];
    $results = [];
    $start = strtotime('-30 days');
    $end   = time();
    foreach ($events as $ev) {
        $url = "https://graph.facebook.com/v19.0/{$pixel_id}/stats?aggregation=event_source&event={$ev}&start_time={$start}&end_time={$end}&access_token=" . urlencode($token);
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res, true);
        $count = 0;
        if (isset($data['data'])) foreach ($data['data'] as $row) $count += (int)($row['count'] ?? 0);
        $results[$ev] = $count;
    }
    return $results;
}

$stats     = orderStats($orders);
$prodSales = productSales($orders);
$customers = getCustomers($orders);

/* ── Filters ─────────────────────────────────────────────────── */
$filtered = $orders;
$q        = trim($_GET['q'] ?? '');
$fStatus  = $_GET['status'] ?? 'all';
$fProd    = $_GET['product'] ?? 'all';
if ($q)              $filtered = array_filter($filtered, fn($o) =>
    str_contains(strtolower($o['name']??''), strtolower($q)) ||
    str_contains($o['phone']??'', $q) ||
    str_contains($o['id']??'', strtoupper($q))
);
if ($fStatus !== 'all') $filtered = array_filter($filtered, fn($o) => ($o['status']??'pending') === $fStatus);
if ($fProd   !== 'all') $filtered = array_filter($filtered, fn($o) => str_contains($o['product']??'', $fProd));
$filtered = array_values($filtered);

/* ═══════════════════ LOGIN PAGE ════════════════════════════ */
function showLogin() { ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nasir Oil Expert — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#1B4332 0%,#2D6A4F 50%,#1a3a2a 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;}
.card{background:#fff;border-radius:24px;padding:48px 44px;width:min(400px,94vw);box-shadow:0 32px 80px rgba(0,0,0,.3);text-align:center;}
.logo-wrap{margin-bottom:20px;}
.logo-wrap img{width:74px;height:74px;object-fit:contain;border-radius:14px;}
h1{font-family:'Poppins',sans-serif;font-size:1.8rem;font-weight:700;color:#1B4332;margin-bottom:4px;}
p{color:#999;font-size:.78rem;margin-bottom:28px;font-weight:500;}
.divider{height:1px;background:#f0f0f0;margin:0 0 22px;}
input{width:100%;padding:13px 16px;border:1.5px solid #e8e8e8;border-radius:12px;font-family:'Poppins',sans-serif;font-size:.85rem;margin-bottom:14px;outline:none;transition:.2s;color:#333;}
input:focus{border-color:#1B4332;box-shadow:0 0 0 3px rgba(27,67,50,.08);}
button{width:100%;padding:14px;background:#1B4332;color:#fff;border:none;border-radius:12px;font-family:'Poppins',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;letter-spacing:.04em;transition:.2s;}
button:hover{background:#2D6A4F;transform:translateY(-1px);box-shadow:0 6px 20px rgba(27,67,50,.3);}
.hint{font-size:.68rem;color:#ccc;margin-top:16px;}
</style></head><body>
<div class="card">
  <div class="logo-wrap"><img src="images/logo.png" alt="Nasir Oil Expert"></div>
  <h1>Nasir Oil Expert</h1>
  <p>Admin Dashboard — Secure Access</p>
  <div class="divider"></div>
  <form method="post" action="admin-dashboard.php">
    <input type="password" name="pass" placeholder="Enter admin password" autofocus>
    <button type="submit">Login →</button>
  </form>
  <div class="hint">nasiroilexpert.com</div>
</div></body></html>
<?php }

/* ═══════════════════ NAV CONFIG ════════════════════════════ */
$nav = [
    'dashboard' => ['icon'=>'📊','label'=>'Dashboard'],
    'orders'    => ['icon'=>'📦','label'=>'Orders'],
    'customers' => ['icon'=>'👥','label'=>'Customers'],
    'stock'     => ['icon'=>'🏪','label'=>'Stock'],
    'meta'      => ['icon'=>'📈','label'=>'Meta Ads'],
    'settings'  => ['icon'=>'⚙️','label'=>'Settings'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — Nasir Oil Expert</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;background:#eef2ef;color:#1a1a1a;display:flex;min-height:100vh;}

/* ── Sidebar ── */
.sidebar{width:230px;background:#1B4332;color:#fff;display:flex;flex-direction:column;min-height:100vh;flex-shrink:0;position:sticky;top:0;height:100vh;overflow:hidden;}
.sidebar-brand{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:12px;}
.sidebar-brand img{width:42px;height:42px;object-fit:contain;border-radius:10px;flex-shrink:0;}
.sidebar-brand-text h2{font-family:'Poppins',sans-serif;font-size:1.05rem;font-weight:700;line-height:1.2;color:#fff;}
.sidebar-brand-text p{font-size:.6rem;color:#74c69d;font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-top:2px;}
.nav-section{padding:14px 0 6px 20px;font-size:.58rem;font-weight:700;color:rgba(255,255,255,.35);letter-spacing:.12em;text-transform:uppercase;}
.nav-link{display:flex;align-items:center;gap:10px;padding:11px 20px;font-size:.78rem;font-weight:600;color:rgba(255,255,255,.7);text-decoration:none;transition:.15s;border-left:3px solid transparent;}
.nav-link:hover{background:rgba(255,255,255,.07);color:#fff;}
.nav-link.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:#74c69d;}
.nav-link .ic{font-size:.95rem;width:18px;text-align:center;flex-shrink:0;}
.badge-pill{margin-left:auto;background:#e74c3c;color:#fff;border-radius:20px;padding:1px 7px;font-size:.6rem;font-weight:700;}
.sidebar-footer{margin-top:auto;padding:14px 20px;border-top:1px solid rgba(255,255,255,.08);display:flex;gap:12px;}
.sidebar-footer a{color:rgba(255,255,255,.45);font-size:.7rem;text-decoration:none;font-weight:500;}
.sidebar-footer a:hover{color:#fff;}

/* ── Main ── */
.main{flex:1;min-width:0;padding:28px 30px;}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap;}
.page-title{font-family:'Poppins',sans-serif;font-size:1.9rem;font-weight:700;color:#1B4332;line-height:1.1;}
.page-sub{font-size:.72rem;color:#999;margin-top:3px;font-weight:500;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-family:'Poppins',sans-serif;font-size:.72rem;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:.15s;letter-spacing:.02em;}
.btn-primary{background:#1B4332;color:#fff;} .btn-primary:hover{background:#2D6A4F;}
.btn-outline{background:#fff;color:#1B4332;border:1.5px solid #1B4332;} .btn-outline:hover{background:#1B4332;color:#fff;}
.btn-sm{padding:6px 13px;font-size:.68rem;}
.btn-danger{background:#e74c3c;color:#fff;border:none;} .btn-danger:hover{background:#c0392b;}
.btn-wa{background:#25D366;color:#fff;border:none;padding:5px 12px;font-size:.68rem;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}

/* ── Cards ── */
.card{background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.04);padding:22px 24px;}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:14px;margin-bottom:22px;}
.stat-card{background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.04);padding:20px 22px;border-top:3px solid var(--c,#1B4332);position:relative;overflow:hidden;}
.stat-card::before{content:'';position:absolute;right:-10px;top:-10px;width:60px;height:60px;background:var(--c,#1B4332);opacity:.05;border-radius:50%;}
.stat-num{font-family:'Poppins',sans-serif;font-size:2.2rem;font-weight:700;color:var(--c,#1B4332);line-height:1;}
.stat-label{font-size:.65rem;color:#999;text-transform:uppercase;letter-spacing:.08em;margin-top:5px;font-weight:600;}
.stat-sub{font-size:.7rem;color:#bbb;margin-top:3px;}

/* ── Table ── */
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{background:#f4f8f5;color:#1B4332;padding:10px 14px;font-size:.63rem;text-transform:uppercase;letter-spacing:.08em;text-align:left;font-weight:700;border-bottom:2px solid #e2ede5;white-space:nowrap;}
td{padding:11px 14px;font-size:.8rem;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafffe;}

/* ── Badges ── */
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-done{background:#d4edda;color:#155724;}
.badge-cancelled{background:#f8d7da;color:#721c24;}
.badge-low{background:#ffe0e0;color:#c0392b;}
.badge-ok-stock{background:#e8f5e9;color:#2e7d32;}

/* ── Filters ── */
.filter-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center;}
.filter-bar input,.filter-bar select{padding:8px 12px;border:1.5px solid #e4e4e4;border-radius:8px;font-family:'Poppins',sans-serif;font-size:.78rem;outline:none;background:#fff;color:#333;}
.filter-bar input:focus,.filter-bar select:focus{border-color:#1B4332;}

/* ── Stock ── */
.stock-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px;}
.stock-card{background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.05);padding:22px;}
.stock-prod-name{font-family:'Poppins',sans-serif;font-size:1.1rem;font-weight:700;color:#1B4332;margin-bottom:2px;}
.stock-sku{font-size:.65rem;color:#aaa;margin-bottom:14px;font-weight:500;}
.stock-num{font-family:'Poppins',sans-serif;font-size:2.8rem;font-weight:700;color:#1B4332;line-height:1;}
.stock-card label{display:block;font-size:.63rem;font-weight:700;text-transform:uppercase;color:#888;margin-bottom:5px;letter-spacing:.06em;}
.stock-card input[type=number]{width:100%;padding:9px 12px;border:1.5px solid #e4e4e4;border-radius:8px;font-family:'Poppins',sans-serif;font-size:.88rem;font-weight:600;outline:none;margin-bottom:10px;color:#1B4332;}
.stock-card input:focus{border-color:#1B4332;}
.prog-bar{height:5px;background:#e8f0ea;border-radius:4px;overflow:hidden;margin:8px 0 14px;}
.prog-fill{height:100%;border-radius:4px;background:var(--c,#1B4332);transition:.3s;}

/* ── Meta ── */
.meta-ev-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:12px;margin-bottom:22px;}
.meta-ev-card{background:#fff;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);padding:20px;text-align:center;}
.meta-ev-icon{font-size:1.6rem;margin-bottom:8px;}
.meta-ev-label{font-size:.63rem;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;}
.meta-ev-num{font-family:'Poppins',sans-serif;font-size:2.2rem;font-weight:700;}

/* ── Customer cards ── */
.cust-card{background:#fff;border:1px solid #eaf2ec;border-radius:16px;margin-bottom:14px;overflow:hidden;}
.cust-header{display:flex;align-items:center;gap:14px;padding:18px 20px;border-bottom:1px solid #f0f7f2;}
.cust-avatar{width:48px;height:48px;background:#1B4332;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Poppins',sans-serif;font-size:1.4rem;font-weight:700;flex-shrink:0;}
.cust-name{font-weight:700;font-size:.95rem;color:#1B4332;}
.cust-meta{font-size:.73rem;color:#888;margin-top:2px;}
.cust-right{margin-left:auto;text-align:right;flex-shrink:0;}
.cust-total{font-family:'Poppins',sans-serif;font-size:1.5rem;font-weight:700;color:#B8860B;}
.cust-total-label{font-size:.63rem;color:#aaa;text-transform:uppercase;letter-spacing:.06em;}
.cust-orders-table{padding:0 20px 16px;}
.cust-orders-table table{margin-top:12px;}
.cust-orders-table th{background:#f8fbf9;font-size:.6rem;}
.cust-orders-table td{font-size:.75rem;padding:9px 12px;}

/* ── Two col ── */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;}
.section-title{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#999;margin:0 0 14px;}

/* ── Alert ── */
.alert{padding:12px 16px;border-radius:10px;font-size:.8rem;margin-bottom:16px;font-weight:500;}
.alert-warning{background:#fff9e6;color:#7a5c00;border:1px solid #f5d76e;}

/* ── Responsive ── */
@media(max-width:768px){
  .sidebar{width:58px;}
  .sidebar-brand img{width:34px;height:34px;}
  .sidebar-brand-text,.nav-link span:not(.ic),.nav-section,.sidebar-footer a:last-child,.badge-pill{display:none;}
  .sidebar-brand{justify-content:center;padding:16px 0;}
  .nav-link{justify-content:center;padding:14px;border-left:none;}
  .nav-link.active{border-left:none;}
  .main{padding:14px;}
  .two-col{grid-template-columns:1fr;}
}

select.status-sel{padding:5px 9px;border:1px solid #ddd;border-radius:6px;font-size:.73rem;font-family:'Poppins',sans-serif;outline:none;}
select.status-sel:focus{border-color:#1B4332;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-brand">
    <img src="images/logo.png" alt="Logo">
    <div class="sidebar-brand-text">
      <h2>Nasir Oil Expert</h2>
      <p>Admin Panel</p>
    </div>
  </div>
  <div class="nav-section">Main Menu</div>
  <?php foreach ($nav as $key => $item): ?>
  <a href="?p=<?= $key ?>" class="nav-link <?= $page===$key?'active':'' ?>">
    <span class="ic"><?= $item['icon'] ?></span>
    <span><?= $item['label'] ?></span>
    <?php if ($key==='orders' && ($stats['pending']??0) > 0): ?>
      <span class="badge-pill"><?= $stats['pending'] ?></span>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>
  <div class="sidebar-footer">
    <a href="?logout=1">🚪 Logout</a>
    <a href="/" target="_blank">🌐 Site</a>
  </div>
</div>

<!-- Main Content -->
<div class="main">

<?php /* ══════ DASHBOARD ══════ */ if ($page === 'dashboard'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Dashboard</div>
    <div class="page-sub">Good <?= (date('H')<12?'morning':(date('H')<17?'afternoon':'evening')) ?> — <?= date('d M Y, h:i A') ?></div>
  </div>
  <a href="?p=orders&export=csv" class="btn btn-outline btn-sm">⬇ Export CSV</a>
</div>

<?php if (($stats['pending']??0) > 0): ?>
<div class="alert alert-warning">⚠️ <strong><?= $stats['pending'] ?> pending order<?= $stats['pending']>1?'s':'' ?></strong> waiting — <a href="?p=orders&status=pending" style="color:#7a5c00;font-weight:700;">View now →</a></div>
<?php endif; ?>

<div class="stat-grid">
  <div class="stat-card" style="--c:#1B4332">
    <div class="stat-num"><?= $stats['total'] ?></div>
    <div class="stat-label">Total Orders</div>
    <div class="stat-sub">+<?= $stats['today'] ?> today</div>
  </div>
  <div class="stat-card" style="--c:#e67e22">
    <div class="stat-num"><?= $stats['pending'] ?></div>
    <div class="stat-label">Pending</div>
    <div class="stat-sub">Need processing</div>
  </div>
  <div class="stat-card" style="--c:#27ae60">
    <div class="stat-num"><?= $stats['done'] ?></div>
    <div class="stat-label">Delivered</div>
    <div class="stat-sub">Completed</div>
  </div>
  <div class="stat-card" style="--c:#B8860B">
    <div class="stat-num" style="font-size:1.5rem;">Rs <?= number_format($stats['revenue']) ?></div>
    <div class="stat-label">Total Revenue</div>
    <div class="stat-sub">Rs <?= number_format($stats['week_rev']) ?> this week</div>
  </div>
  <div class="stat-card" style="--c:#8e44ad">
    <div class="stat-num"><?= count($customers) ?></div>
    <div class="stat-label">Unique Customers</div>
    <div class="stat-sub">Total buyers</div>
  </div>
  <div class="stat-card" style="--c:#e74c3c">
    <div class="stat-num"><?= $stats['cancelled'] ?></div>
    <div class="stat-label">Cancelled</div>
    <div class="stat-sub">This period</div>
  </div>
</div>

<div class="two-col">
  <div class="card">
    <div class="section-title">Sales by Product</div>
    <?php
    $maxC = max(array_column($prodSales, 'count') ?: [1]);
    foreach ($prodSales as $pname => $ps):
      $pct = $maxC > 0 ? round($ps['count']/$maxC*100) : 0;
    ?>
    <div style="margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
        <span style="font-size:.8rem;font-weight:600;"><?= clean($pname) ?></span>
        <span style="font-size:.72rem;color:#888;"><?= $ps['count'] ?> orders · Rs <?= number_format($ps['revenue']) ?></span>
      </div>
      <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;--c:#1B4332;"></div></div>
    </div>
    <?php endforeach; if (empty($prodSales)): ?>
    <p style="color:#ccc;text-align:center;padding:20px;font-size:.8rem;">No orders yet.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-title">Inventory Status</div>
    <?php foreach ($stock as $k => $p):
      $s = (int)$p['stock'];
      $low = $s <= 10;
    ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding:10px 12px;background:#f6faf7;border-radius:10px;">
      <div>
        <div style="font-size:.82rem;font-weight:600;"><?= clean($p['name']) ?></div>
        <div style="font-size:.67rem;color:#999;margin-top:1px;">Rs <?= number_format($p['price']) ?></div>
      </div>
      <span class="badge <?= $low?'badge-low':'badge-ok-stock' ?>"><?= $s ?> units<?= $low?' ⚠️':'' ?></span>
    </div>
    <?php endforeach; ?>
    <a href="?p=stock" class="btn btn-outline btn-sm" style="margin-top:10px;">Manage Stock →</a>
  </div>
</div>

<div class="card">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="section-title" style="margin:0;">Recent Orders</div>
    <a href="?p=orders" class="btn btn-outline btn-sm">View All →</a>
  </div>
  <div class="table-wrap">
  <table>
    <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach (array_slice($orders, 0, 8) as $o): ?>
    <tr>
      <td><strong><?= clean($o['id']??'') ?></strong></td>
      <td>
        <?= clean($o['name']??'') ?><br>
        <span style="color:#1B4332;font-size:.7rem;">📞 <?= clean($o['phone']??'') ?></span>
        <?php if ($o['city']??''): ?><span style="color:#aaa;font-size:.7rem;"> · <?= clean($o['city']) ?></span><?php endif; ?>
      </td>
      <td><?= clean($o['product']??'') ?></td>
      <td><strong>Rs <?= number_format((float)($o['price']??0)) ?></strong></td>
      <td><span class="badge badge-<?= $o['status']??'pending' ?>"><?= $o['status']??'pending' ?></span></td>
      <td style="font-size:.7rem;color:#aaa;white-space:nowrap;"><?= clean($o['date']??'') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?><tr><td colspan="6" style="text-align:center;color:#ccc;padding:40px;">No orders yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php /* ══════ ORDERS ══════ */ elseif ($page === 'orders'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Orders</div>
    <div class="page-sub"><?= count($filtered) ?> of <?= count($orders) ?> total</div>
  </div>
  <a href="?p=orders&export=csv" class="btn btn-outline btn-sm">⬇ Export CSV</a>
</div>

<div class="card" style="margin-bottom:14px;padding:16px 20px;">
<form method="get" class="filter-bar">
  <input type="hidden" name="p" value="orders">
  <input type="text" name="q" value="<?= clean($q) ?>" placeholder="Search name, phone, order ID...">
  <select name="status">
    <option value="all" <?= $fStatus==='all'?'selected':'' ?>>All Status</option>
    <option value="pending"   <?= $fStatus==='pending'?'selected':'' ?>>Pending</option>
    <option value="done"      <?= $fStatus==='done'?'selected':'' ?>>Done</option>
    <option value="cancelled" <?= $fStatus==='cancelled'?'selected':'' ?>>Cancelled</option>
  </select>
  <select name="product">
    <option value="all">All Products</option>
    <?php foreach (array_keys($prodSales) as $pn): ?>
    <option value="<?= clean($pn) ?>" <?= $fProd===$pn?'selected':'' ?>><?= clean($pn) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  <a href="?p=orders" class="btn btn-outline btn-sm">Clear</a>
</form>
</div>

<div class="card">
<div class="table-wrap">
<table>
  <thead><tr><th>#</th><th>Order ID</th><th>Date</th><th>Name</th><th>Phone</th><th>City</th><th>Address</th><th>Product</th><th>Price</th><th>Page</th><th>Status</th><th>Note</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($filtered as $i => $o): ?>
  <tr>
    <td style="color:#ccc;"><?= $i+1 ?></td>
    <td><strong style="font-size:.73rem;"><?= clean($o['id']??'') ?></strong></td>
    <td style="font-size:.68rem;color:#aaa;white-space:nowrap;"><?= clean($o['date']??'') ?></td>
    <td><strong><?= clean($o['name']??'') ?></strong></td>
    <td style="color:#1B4332;font-size:.78rem;white-space:nowrap;">📞 <?= clean($o['phone']??'') ?></td>
    <td style="font-size:.78rem;">📍 <?= clean($o['city']??'—') ?></td>
    <td style="font-size:.72rem;color:#888;max-width:130px;"><?= clean($o['address']??'—') ?></td>
    <td style="font-size:.8rem;font-weight:600;"><?= clean($o['product']??'') ?></td>
    <td><strong>Rs <?= number_format((float)($o['price']??0)) ?></strong></td>
    <td style="font-size:.68rem;color:#888;white-space:nowrap;"><?= pageLabel($o['source_page']??'') ?></td>
    <td>
      <form method="post" style="display:inline;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" value="<?= clean($o['id']??'') ?>">
        <select class="status-sel" name="status" onchange="this.form.submit()">
          <option value="pending"   <?= ($o['status']??'pending')==='pending'?'selected':'' ?>>⏳ Pending</option>
          <option value="done"      <?= ($o['status']??'')==='done'?'selected':'' ?>>✅ Done</option>
          <option value="cancelled" <?= ($o['status']??'')==='cancelled'?'selected':'' ?>>❌ Cancelled</option>
        </select>
      </form>
    </td>
    <td>
      <form method="post" style="display:flex;gap:4px;min-width:140px;">
        <input type="hidden" name="action" value="update_note">
        <input type="hidden" name="id" value="<?= clean($o['id']??'') ?>">
        <input type="text" name="note" value="<?= clean($o['admin_note']??'') ?>" placeholder="Note..." style="flex:1;min-width:0;padding:5px 8px;border:1px solid #e4e4e4;border-radius:6px;font-size:.72rem;font-family:'Poppins',sans-serif;outline:none;">
        <button type="submit" class="btn btn-primary btn-sm">✓</button>
      </form>
    </td>
    <td>
      <?php $wa = waNumber($o['phone']??''); ?>
      <?php if ($wa): ?>
      <a href="https://wa.me/<?= $wa ?>" target="_blank" class="btn-wa">📲 WA</a>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (empty($filtered)): ?><tr><td colspan="13" style="text-align:center;color:#ccc;padding:40px;">No orders found.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
</div>

<?php /* ══════ CUSTOMERS ══════ */ elseif ($page === 'customers'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Customers</div>
    <div class="page-sub"><?= count($customers) ?> unique buyers — full order history per customer</div>
  </div>
</div>

<?php if (empty($customers)): ?>
<div class="card"><p style="text-align:center;color:#ccc;padding:40px;">No customers yet. Orders will appear here.</p></div>
<?php else: foreach ($customers as $phone => $c): $wa = waNumber($phone); ?>
<div class="cust-card">
  <div class="cust-header">
    <div class="cust-avatar"><?= mb_strtoupper(mb_substr($c['name'], 0, 1)) ?></div>
    <div>
      <div class="cust-name"><?= clean($c['name']) ?></div>
      <div class="cust-meta">
        📞 <?= clean($phone) ?>
        <?php if ($c['city']): ?> &nbsp;·&nbsp; 📍 <?= clean($c['city']) ?><?php endif; ?>
        &nbsp;·&nbsp; <strong><?= count($c['orders']) ?> order<?= count($c['orders'])>1?'s':'' ?></strong>
      </div>
    </div>
    <div class="cust-right">
      <div class="cust-total">Rs <?= number_format($c['total_spent']) ?></div>
      <div class="cust-total-label">Total Spent</div>
      <?php if ($wa): ?>
      <a href="https://wa.me/<?= $wa ?>" target="_blank" class="btn-wa" style="margin-top:8px;display:inline-flex;">📲 WhatsApp</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="cust-orders-table">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Date</th>
          <th>Product Bought</th>
          <th>Delivery Address</th>
          <th>Price Paid</th>
          <th>Ordered From Page</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($c['orders'] as $co): ?>
      <tr>
        <td><strong><?= clean($co['id']??'') ?></strong></td>
        <td style="color:#aaa;white-space:nowrap;"><?= clean($co['date']??'') ?></td>
        <td><strong><?= clean($co['product']??'') ?></strong></td>
        <td>
          <?php if ($co['address']??''): ?>
            <?= clean($co['address']) ?><?php if ($co['city']??''): ?>, <?= clean($co['city']) ?><?php endif; ?>
          <?php else: ?><span style="color:#ccc;">—</span><?php endif; ?>
        </td>
        <td><strong style="color:#B8860B;">Rs <?= number_format((float)($co['price']??0)) ?></strong></td>
        <td><?= pageLabel($co['source_page']??'') ?></td>
        <td><span class="badge badge-<?= $co['status']??'pending' ?>"><?= $co['status']??'pending' ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; endif; ?>

<?php /* ══════ STOCK ══════ */ elseif ($page === 'stock'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Stock Management</div>
    <div class="page-sub">Update inventory for all 4 products</div>
  </div>
</div>

<?php
$lowStock = array_filter($stock, fn($p) => (int)$p['stock'] <= 10);
if (!empty($lowStock)):
?>
<div class="alert alert-warning">⚠️ Low stock: <strong><?= implode(', ', array_column($lowStock, 'name')) ?></strong> — refill soon.</div>
<?php endif; ?>

<form method="post" action="admin-dashboard.php?p=stock">
<input type="hidden" name="action" value="update_stock">
<div class="stock-grid">
<?php foreach ($stock as $k => $p):
  $s = (int)$p['stock'];
  $low = $s <= 10;
  $pct = min(100, $s);
  $color = $low ? '#e74c3c' : ($s <= 25 ? '#e67e22' : '#1B4332');
?>
<div class="stock-card">
  <div class="stock-prod-name"><?= clean($p['name']) ?></div>
  <div class="stock-sku">SKU: <?= clean($p['sku']) ?></div>
  <div class="stock-num" style="color:<?= $color ?>;"><?= $s ?></div>
  <div style="font-size:.68rem;color:#999;margin:2px 0 4px;"><?= $low?'⚠️ LOW STOCK':'units in stock' ?></div>
  <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;--c:<?= $color ?>;"></div></div>
  <label>Update Quantity</label>
  <input type="number" name="stock_<?= $k ?>" value="<?= $s ?>" min="0">
  <label>Price (Rs)</label>
  <input type="number" name="price_<?= $k ?>" value="<?= (int)$p['price'] ?>" min="0">
</div>
<?php endforeach; ?>
</div>
<div style="margin-top:18px;">
  <button type="submit" class="btn btn-primary">💾 Save All Changes</button>
</div>
</form>

<?php /* ══════ META ADS ══════ */ elseif ($page === 'meta'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Meta Ads & Pixel</div>
    <div class="page-sub">Last 30 days · Pixel ID: <?= META_PIXEL_ID ?></div>
  </div>
  <a href="https://business.facebook.com/events_manager2/list/dataset/<?= META_PIXEL_ID ?>" target="_blank" class="btn btn-primary btn-sm">Open Events Manager ↗</a>
</div>

<?php
$metaStats = fetchMetaStats(META_ACCESS_TOKEN, META_PIXEL_ID);
$evColors = ['Purchase'=>'#B8860B','ViewContent'=>'#1B4332','AddToCart'=>'#27ae60','InitiateCheckout'=>'#8e44ad','Contact'=>'#25D366'];
$evIcons  = ['Purchase'=>'💳','ViewContent'=>'👁','AddToCart'=>'🛒','InitiateCheckout'=>'📋','Contact'=>'📲'];
?>
<?php if ($metaStats): ?>
<div class="meta-ev-grid">
<?php foreach ($metaStats as $ev => $count): ?>
<div class="meta-ev-card">
  <div class="meta-ev-icon"><?= $evIcons[$ev]??'📌' ?></div>
  <div class="meta-ev-label"><?= $ev ?></div>
  <div class="meta-ev-num" style="color:<?= $evColors[$ev]??'#1B4332' ?>;"><?= number_format($count) ?></div>
</div>
<?php endforeach; ?>
</div>

<div class="two-col">
  <div class="card">
    <div class="section-title">Event Breakdown</div>
    <?php $tot = array_sum($metaStats); foreach ($metaStats as $ev => $count):
      $pct = $tot > 0 ? round($count/$tot*100) : 0; ?>
    <div style="margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
        <span style="font-size:.8rem;font-weight:600;"><?= $evIcons[$ev]??'' ?> <?= $ev ?></span>
        <span style="font-size:.73rem;color:#888;"><?= number_format($count) ?> (<?= $pct ?>%)</span>
      </div>
      <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;--c:<?= $evColors[$ev]??'#1B4332' ?>;"></div></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <div class="section-title">Conversion Funnel</div>
    <?php
    $funnel = ['ViewContent'=>'Product Views','AddToCart'=>'Add to Cart','InitiateCheckout'=>'Checkout Started','Purchase'=>'Purchases'];
    $prev = null;
    foreach ($funnel as $ev => $label):
      $count = $metaStats[$ev] ?? 0;
      $rate = ($prev && $prev > 0) ? round($count/$prev*100) : 100;
      $prev = $count;
    ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f5f5f5;">
      <div>
        <div style="font-size:.82rem;font-weight:600;"><?= $evIcons[$ev]??'' ?> <?= $label ?></div>
        <div style="font-size:.68rem;color:#aaa;"><?= $rate ?>% conversion rate</div>
      </div>
      <div style="font-family:'Poppins',sans-serif;font-size:1.7rem;font-weight:700;color:#1B4332;"><?= number_format($count) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php else: ?>
<div class="card"><div style="text-align:center;padding:40px;">
  <div style="font-size:2.5rem;margin-bottom:12px;">📡</div>
  <p style="color:#aaa;font-size:.85rem;">Unable to fetch Meta stats.<br>Check your access token in config.php on Hostinger.</p>
  <a href="https://business.facebook.com/events_manager2/list/dataset/<?= META_PIXEL_ID ?>" target="_blank" class="btn btn-primary" style="margin-top:16px;">Open Events Manager ↗</a>
</div></div>
<?php endif; ?>

<div class="card" style="margin-top:14px;">
  <div class="section-title">Quick Links</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px;">
    <a href="https://business.facebook.com/events_manager2/list/dataset/<?= META_PIXEL_ID ?>/test_events" target="_blank" class="btn btn-outline btn-sm">🧪 Test Events</a>
    <a href="https://adsmanager.facebook.com" target="_blank" class="btn btn-outline btn-sm">📊 Ads Manager</a>
    <a href="https://analytics.google.com" target="_blank" class="btn btn-outline btn-sm">📈 Google Analytics</a>
    <a href="https://business.facebook.com" target="_blank" class="btn btn-outline btn-sm">💼 Business Manager</a>
  </div>
</div>

<?php /* ══════ SETTINGS ══════ */ elseif ($page === 'settings'): ?>
<div class="page-header">
  <div class="page-title">Settings</div>
</div>
<div class="two-col">
  <div class="card">
    <div class="section-title">Site Info</div>
    <?php foreach ([
      'Site URL'       => 'https://nasiroilexpert.com',
      'Meta Pixel ID'  => META_PIXEL_ID,
      'Admin Email'    => 'info@nasiroilexpert.com',
      'Hosting'        => 'Hostinger (PHP)',
      'GitHub Repo'    => 'socialswiftwavedigital/nasir-oil-expert',
    ] as $label => $val): ?>
    <div style="margin-bottom:14px;">
      <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;color:#aaa;margin-bottom:4px;letter-spacing:.06em;"><?= $label ?></div>
      <div style="font-size:.82rem;color:#333;background:#f6f6f6;padding:8px 12px;border-radius:8px;word-break:break-all;"><?= clean($val) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <div class="section-title">Quick Actions</div>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <a href="?p=orders&export=csv" class="btn btn-outline">⬇ Export All Orders (CSV)</a>
      <a href="https://nasiroilexpert.com" target="_blank" class="btn btn-outline">🌐 View Live Site</a>
      <a href="https://business.facebook.com/events_manager2/list/dataset/<?= META_PIXEL_ID ?>/test_events" target="_blank" class="btn btn-outline">📡 Meta Test Events</a>
      <a href="?logout=1" class="btn btn-danger">🚪 Logout</a>
    </div>
  </div>
</div>
<?php endif; ?>

</div><!-- /main -->
</body></html>

