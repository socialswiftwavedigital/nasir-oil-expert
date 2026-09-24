<?php
require_once __DIR__ . '/config.php';

/* ── Auth ─────────────────────────────────────────────────────── */
session_start();
if ($_POST['pass'] ?? '' === ADMIN_PASS) $_SESSION['noe_admin'] = true;
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

/* ── Data ─────────────────────────────────────────────────────── */
$orders = readJson(__DIR__ . '/orders-data.json');
$stock  = readJson(__DIR__ . '/stock.json');
$page   = $_GET['p'] ?? 'dashboard';

/* ── POST Actions ─────────────────────────────────────────────── */
// Update order status
if ($_POST['action'] ?? '' === 'update_status') {
    $id  = $_POST['id'] ?? '';
    $st  = $_POST['status'] ?? 'pending';
    foreach ($orders as &$o) { if ($o['id'] === $id) { $o['status'] = $st; break; } }
    unset($o);
    writeJson(__DIR__ . '/orders-data.json', $orders);
    header('Location: admin-dashboard.php?p=orders'); exit;
}
// Update order note
if ($_POST['action'] ?? '' === 'update_note') {
    $id   = $_POST['id'] ?? '';
    $note = clean($_POST['note'] ?? '');
    foreach ($orders as &$o) { if ($o['id'] === $id) { $o['admin_note'] = $note; break; } }
    unset($o);
    writeJson(__DIR__ . '/orders-data.json', $orders);
    header('Location: admin-dashboard.php?p=orders'); exit;
}
// Update stock
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
    echo "Order ID,Date,Name,Phone,City,Address,Product,Price,Total,Status,Note\n";
    foreach ($orders as $o) {
        echo '"' . implode('","', [
            $o['id'] ?? '', $o['date'] ?? '', $o['name'] ?? '', $o['phone'] ?? '',
            $o['city'] ?? '', $o['address'] ?? '', $o['product'] ?? '',
            $o['price'] ?? '', $o['price'] ?? '', $o['status'] ?? '', $o['admin_note'] ?? ''
        ]) . '"' . "\n";
    }
    exit;
}

/* ── Stats Helpers ────────────────────────────────────────────── */
function orderStats($orders) {
    $today = date('d M Y');
    $week  = strtotime('-7 days');
    $stats = ['total'=>0,'pending'=>0,'done'=>0,'cancelled'=>0,'revenue'=>0,'today'=>0,'week_rev'=>0];
    foreach ($orders as $o) {
        $stats['total']++;
        $stats[$o['status'] ?? 'pending'] = ($stats[$o['status'] ?? 'pending'] ?? 0) + 1;
        $rev = (float)($o['price'] ?? 0);
        $stats['revenue'] += $rev;
        if (str_starts_with($o['date'] ?? '', $today)) $stats['today']++;
        $otime = strtotime($o['date'] ?? '');
        if ($otime && $otime >= $week) $stats['week_rev'] += $rev;
    }
    return $stats;
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
    $customers = [];
    foreach ($orders as $o) {
        $phone = $o['phone'] ?? '';
        if (!$phone) continue;
        if (!isset($customers[$phone])) {
            $customers[$phone] = ['name'=>$o['name']??'','phone'=>$phone,'city'=>$o['city']??'','orders'=>[],'total_spent'=>0];
        }
        $customers[$phone]['orders'][] = $o;
        $customers[$phone]['total_spent'] += (float)($o['price'] ?? 0);
    }
    uasort($customers, fn($a,$b) => count($b['orders']) - count($a['orders']));
    return $customers;
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

/* ── Filter for orders page ───────────────────────────────────── */
$filtered = $orders;
$q        = trim($_GET['q'] ?? '');
$fStatus  = $_GET['status'] ?? 'all';
$fProd    = $_GET['product'] ?? 'all';

if ($q) $filtered = array_filter($filtered, fn($o) =>
    str_contains(strtolower($o['name'] ?? ''), strtolower($q)) ||
    str_contains($o['phone'] ?? '', $q) ||
    str_contains($o['id'] ?? '', strtoupper($q))
);
if ($fStatus !== 'all') $filtered = array_filter($filtered, fn($o) => ($o['status'] ?? 'pending') === $fStatus);
if ($fProd   !== 'all') $filtered = array_filter($filtered, fn($o) => str_contains($o['product'] ?? '', $fProd));
$filtered = array_values($filtered);

/* ═══════════════════════════════════════════════════════════════
   HTML OUTPUT
═══════════════════════════════════════════════════════════════ */
function showLogin() { ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nasir Oil — Admin</title>
<style>*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#1B4332,#2D6A4F);min-height:100vh;display:flex;align-items:center;justify-content:center;}
.card{background:#fff;border-radius:20px;padding:48px 44px;width:min(400px,94vw);box-shadow:0 24px 60px rgba(0,0,0,.25);text-align:center;}
.logo{font-size:2.4rem;margin-bottom:10px;}
h2{font-size:1.2rem;color:#1B4332;margin-bottom:6px;}
p{color:#888;font-size:.82rem;margin-bottom:28px;}
input{width:100%;padding:12px 16px;border:1.5px solid #e0e0e0;border-radius:10px;font-size:.9rem;margin-bottom:14px;outline:none;transition:.2s;}
input:focus{border-color:#1B4332;}
button{width:100%;padding:13px;background:#1B4332;color:#fff;border:none;border-radius:10px;font-size:.9rem;font-weight:700;cursor:pointer;transition:.2s;}
button:hover{background:#2D6A4F;}
</style></head><body>
<div class="card">
  <div class="logo">🌿</div>
  <h2>Nasir Oil Expert</h2>
  <p>Admin Dashboard — Secure Login</p>
  <form method="post" action="admin-dashboard.php">
    <input type="password" name="pass" placeholder="Enter admin password" autofocus>
    <button type="submit">Login →</button>
  </form>
</div></body></html>
<?php }

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
<title>Nasir Oil — Admin Dashboard</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f4f1;color:#1a1a1a;display:flex;min-height:100vh;}

/* Sidebar */
.sidebar{width:220px;background:#1B4332;color:#fff;display:flex;flex-direction:column;min-height:100vh;flex-shrink:0;position:sticky;top:0;height:100vh;}
.sidebar-logo{padding:24px 20px 18px;border-bottom:1px solid rgba(255,255,255,.1);}
.sidebar-logo h1{font-size:.95rem;font-weight:800;letter-spacing:.04em;}
.sidebar-logo p{font-size:.68rem;color:#a8d5b5;margin-top:2px;}
.nav-link{display:flex;align-items:center;gap:10px;padding:12px 20px;font-size:.82rem;font-weight:600;color:rgba(255,255,255,.75);text-decoration:none;transition:.15s;border-left:3px solid transparent;}
.nav-link:hover{background:rgba(255,255,255,.08);color:#fff;}
.nav-link.active{background:rgba(255,255,255,.12);color:#fff;border-left-color:#74c69d;}
.nav-link .ic{font-size:1rem;width:20px;text-align:center;}
.sidebar-footer{margin-top:auto;padding:16px 20px;border-top:1px solid rgba(255,255,255,.1);}
.sidebar-footer a{color:rgba(255,255,255,.5);font-size:.72rem;text-decoration:none;}
.sidebar-footer a:hover{color:#fff;}

/* Main */
.main{flex:1;min-width:0;padding:28px 30px;}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.page-title{font-size:1.3rem;font-weight:800;color:#1B4332;}
.page-sub{font-size:.78rem;color:#888;margin-top:2px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:.78rem;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:.15s;}
.btn-primary{background:#1B4332;color:#fff;} .btn-primary:hover{background:#2D6A4F;}
.btn-outline{background:#fff;color:#1B4332;border:1.5px solid #1B4332;} .btn-outline:hover{background:#1B4332;color:#fff;}
.btn-sm{padding:5px 12px;font-size:.72rem;}
.btn-danger{background:#dc3545;color:#fff;} .btn-danger:hover{background:#c82333;}

/* Cards */
.card{background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);padding:22px 24px;}
.cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:24px;}
.stat-card{background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);padding:20px 22px;border-top:4px solid var(--c,#1B4332);}
.stat-num{font-size:2.1rem;font-weight:800;color:var(--c,#1B4332);}
.stat-label{font-size:.7rem;color:#888;text-transform:uppercase;letter-spacing:.07em;margin-top:3px;}
.stat-sub{font-size:.72rem;color:#aaa;margin-top:4px;}

/* Table */
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{background:#f6faf7;color:#1B4332;padding:11px 14px;font-size:.68rem;text-transform:uppercase;letter-spacing:.07em;text-align:left;font-weight:700;border-bottom:2px solid #e8f0ea;}
td{padding:12px 14px;font-size:.81rem;border-bottom:1px solid #f3f3f3;vertical-align:top;}
tr:hover td{background:#fafffe;}
tr:last-child td{border-bottom:none;}

/* Badges */
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-done{background:#d4edda;color:#155724;}
.badge-cancelled{background:#f8d7da;color:#721c24;}
.badge-low{background:#ffe0e0;color:#c0392b;}
.badge-ok{background:#d4edda;color:#155724;}

/* Filters */
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;align-items:center;}
.filter-bar input,.filter-bar select{padding:8px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:.8rem;outline:none;background:#fff;}
.filter-bar input:focus,.filter-bar select:focus{border-color:#1B4332;}

/* Stock cards */
.stock-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;}
.stock-card{background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);padding:20px;}
.stock-card .prod-name{font-weight:700;font-size:.9rem;color:#1B4332;margin-bottom:4px;}
.stock-card .sku{font-size:.68rem;color:#aaa;margin-bottom:14px;}
.stock-card label{display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#888;margin-bottom:5px;}
.stock-card input[type=number]{width:100%;padding:8px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:.9rem;font-weight:700;outline:none;margin-bottom:10px;}
.stock-card input:focus{border-color:#1B4332;}
.stock-num{font-size:2.5rem;font-weight:800;color:#1B4332;line-height:1;}

/* Progress bar */
.prog-bar{height:6px;background:#e8f0ea;border-radius:4px;overflow:hidden;margin:8px 0;}
.prog-fill{height:100%;border-radius:4px;background:var(--c,#1B4332);}

/* Meta cards */
.meta-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:24px;}
.meta-card{background:#fff;border-radius:12px;box-shadow:0 1px 8px rgba(0,0,0,.07);padding:18px;text-align:center;}
.meta-card .ev-name{font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;margin-bottom:6px;}
.meta-card .ev-num{font-size:2rem;font-weight:800;color:#1B4332;}

/* Customer */
.customer-card{border:1px solid #e8f0ea;border-radius:12px;padding:16px;margin-bottom:10px;display:flex;align-items:flex-start;gap:14px;}
.cust-avatar{width:42px;height:42px;background:#1B4332;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;flex-shrink:0;}
.cust-info .name{font-weight:700;font-size:.9rem;}
.cust-info .phone{color:#1B4332;font-size:.8rem;}
.cust-info .meta{color:#888;font-size:.72rem;margin-top:2px;}
.cust-right{margin-left:auto;text-align:right;}
.cust-right .spent{font-weight:800;font-size:1rem;color:#1B4332;}
.cust-right .orders{font-size:.72rem;color:#888;}

/* Two col */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;}

/* Section title */
.section-title{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#888;margin:0 0 14px;}

/* Alert */
.alert{padding:12px 16px;border-radius:10px;font-size:.82rem;margin-bottom:16px;}
.alert-warning{background:#fff3cd;color:#856404;border:1px solid #ffc107;}
.alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}

/* Responsive */
@media(max-width:768px){
  .sidebar{width:60px;} .sidebar-logo p,.nav-link span{display:none;}
  .sidebar-logo h1{font-size:0;} .nav-link{justify-content:center;padding:14px;border-left:none;}
  .nav-link.active{border-left:none;border-bottom:3px solid #74c69d;}
  .main{padding:16px;} .two-col{grid-template-columns:1fr;}
}

/* Settings form */
.settings-form label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#888;margin-bottom:5px;}
.settings-form input{width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:.85rem;outline:none;margin-bottom:16px;}
.settings-form input:focus{border-color:#1B4332;}

select.status-sel{padding:5px 8px;border:1px solid #ddd;border-radius:6px;font-size:.75rem;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-logo">
    <h1>🌿 Nasir Oil</h1>
    <p>Admin Panel</p>
  </div>
  <?php foreach ($nav as $key => $item): ?>
  <a href="?p=<?= $key ?>" class="nav-link <?= $page===$key?'active':'' ?>">
    <span class="ic"><?= $item['icon'] ?></span>
    <span><?= $item['label'] ?></span>
    <?php if ($key==='orders' && $stats['pending'] > 0): ?>
      <span style="margin-left:auto;background:#e74c3c;color:#fff;border-radius:20px;padding:1px 8px;font-size:.65rem;font-weight:700;"><?= $stats['pending'] ?></span>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>
  <div class="sidebar-footer">
    <a href="?logout=1">Logout</a> &nbsp;|&nbsp;
    <a href="/" target="_blank">View Site</a>
  </div>
</div>

<!-- Main -->
<div class="main">

<?php /* ════════════ DASHBOARD ════════════ */ if ($page === 'dashboard'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Dashboard</div>
    <div class="page-sub">Welcome back — <?= date('d M Y, h:i A') ?></div>
  </div>
  <a href="?p=orders&export=csv" class="btn btn-outline btn-sm">⬇ Export Orders</a>
</div>

<?php if ($stats['pending'] > 0): ?>
<div class="alert alert-warning">⚠️ You have <strong><?= $stats['pending'] ?> pending order<?= $stats['pending']>1?'s':'' ?></strong> waiting for processing.</div>
<?php endif; ?>

<div class="cards-grid">
  <div class="stat-card" style="--c:#1B4332">
    <div class="stat-num"><?= $stats['total'] ?></div>
    <div class="stat-label">Total Orders</div>
    <div class="stat-sub">+<?= $stats['today'] ?> today</div>
  </div>
  <div class="stat-card" style="--c:#e67e22">
    <div class="stat-num"><?= $stats['pending'] ?></div>
    <div class="stat-label">Pending</div>
    <div class="stat-sub">Need action</div>
  </div>
  <div class="stat-card" style="--c:#27ae60">
    <div class="stat-num"><?= $stats['done'] ?></div>
    <div class="stat-label">Completed</div>
    <div class="stat-sub">Delivered</div>
  </div>
  <div class="stat-card" style="--c:#B8860B">
    <div class="stat-num">Rs <?= number_format($stats['revenue']) ?></div>
    <div class="stat-label">Total Revenue</div>
    <div class="stat-sub">Rs <?= number_format($stats['week_rev']) ?> this week</div>
  </div>
  <div class="stat-card" style="--c:#8e44ad">
    <div class="stat-num"><?= count($customers) ?></div>
    <div class="stat-label">Customers</div>
    <div class="stat-sub">Unique buyers</div>
  </div>
  <div class="stat-card" style="--c:#e74c3c">
    <div class="stat-num"><?= $stats['cancelled'] ?></div>
    <div class="stat-label">Cancelled</div>
    <div class="stat-sub">This period</div>
  </div>
</div>

<div class="two-col">
  <!-- Product sales -->
  <div class="card">
    <div class="section-title">Sales by Product</div>
    <?php
    $maxCount = max(array_column($prodSales, 'count') ?: [1]);
    foreach ($prodSales as $pname => $ps):
      $pct = $maxCount > 0 ? round($ps['count']/$maxCount*100) : 0;
    ?>
    <div style="margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
        <span style="font-size:.8rem;font-weight:600;"><?= clean($pname) ?></span>
        <span style="font-size:.75rem;color:#888;"><?= $ps['count'] ?> orders — Rs <?= number_format($ps['revenue']) ?></span>
      </div>
      <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;"></div></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Stock status -->
  <div class="card">
    <div class="section-title">Stock Status</div>
    <?php foreach ($stock as $k => $p):
      $s = (int)$p['stock'];
      $low = $s <= 10;
    ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding:10px;background:#f6faf7;border-radius:8px;">
      <span style="font-size:.82rem;font-weight:600;"><?= clean($p['name']) ?></span>
      <span class="badge <?= $low?'badge-low':'badge-ok' ?>"><?= $s ?> units<?= $low?' ⚠️':'' ?></span>
    </div>
    <?php endforeach; ?>
    <a href="?p=stock" class="btn btn-outline btn-sm" style="margin-top:8px;">Manage Stock →</a>
  </div>
</div>

<!-- Recent Orders -->
<div class="card">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="section-title" style="margin:0;">Recent Orders</div>
    <a href="?p=orders" class="btn btn-outline btn-sm">View All</a>
  </div>
  <div class="table-wrap">
  <table>
    <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach (array_slice($orders, 0, 8) as $o): ?>
    <tr>
      <td><strong><?= clean($o['id'] ?? '') ?></strong></td>
      <td><?= clean($o['name'] ?? '') ?><br><span style="color:#1B4332;font-size:.72rem;">📞 <?= clean($o['phone'] ?? '') ?></span></td>
      <td><?= clean($o['product'] ?? '') ?></td>
      <td><strong>Rs <?= number_format((float)($o['price'] ?? 0)) ?></strong></td>
      <td><span class="badge badge-<?= $o['status'] ?? 'pending' ?>"><?= $o['status'] ?? 'pending' ?></span></td>
      <td style="font-size:.73rem;color:#888;"><?= clean($o['date'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?><tr><td colspan="6" style="text-align:center;color:#aaa;padding:40px;">No orders yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php /* ════════════ ORDERS ════════════ */ elseif ($page === 'orders'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Orders</div>
    <div class="page-sub"><?= count($filtered) ?> of <?= count($orders) ?> orders</div>
  </div>
  <a href="?p=orders&export=csv" class="btn btn-outline btn-sm">⬇ Export CSV</a>
</div>

<div class="card" style="margin-bottom:16px;">
<form method="get" action="admin-dashboard.php" class="filter-bar">
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
  <thead><tr><th>#</th><th>Order ID</th><th>Date</th><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th><th>Note</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($filtered as $i => $o): ?>
  <tr>
    <td><?= $i+1 ?></td>
    <td><strong><?= clean($o['id'] ?? '') ?></strong></td>
    <td style="font-size:.73rem;color:#888;white-space:nowrap;"><?= clean($o['date'] ?? '') ?></td>
    <td>
      <strong><?= clean($o['name'] ?? '') ?></strong><br>
      <a href="tel:<?= clean($o['phone'] ?? '') ?>" style="color:#1B4332;font-size:.75rem;">📞 <?= clean($o['phone'] ?? '') ?></a><br>
      <span style="color:#888;font-size:.72rem;">📍 <?= clean($o['city'] ?? '') ?></span>
      <?php if (!empty($o['address'])): ?><br><span style="color:#888;font-size:.7rem;"><?= clean($o['address'] ?? '') ?></span><?php endif; ?>
    </td>
    <td><?= clean($o['product'] ?? '') ?></td>
    <td><strong>Rs <?= number_format((float)($o['price'] ?? 0)) ?></strong></td>
    <td>
      <form method="post" action="admin-dashboard.php" style="display:inline;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" value="<?= clean($o['id'] ?? '') ?>">
        <select class="status-sel" name="status" onchange="this.form.submit()">
          <option value="pending"   <?= ($o['status']??'pending')==='pending'?'selected':'' ?>>Pending</option>
          <option value="done"      <?= ($o['status']??'')==='done'?'selected':'' ?>>Done</option>
          <option value="cancelled" <?= ($o['status']??'')==='cancelled'?'selected':'' ?>>Cancelled</option>
        </select>
      </form>
    </td>
    <td>
      <form method="post" action="admin-dashboard.php" style="display:flex;gap:4px;">
        <input type="hidden" name="action" value="update_note">
        <input type="hidden" name="id" value="<?= clean($o['id'] ?? '') ?>">
        <input type="text" name="note" value="<?= clean($o['admin_note'] ?? '') ?>" placeholder="Add note..." style="width:110px;padding:4px 8px;border:1px solid #ddd;border-radius:6px;font-size:.72rem;">
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
      </form>
    </td>
    <td>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $o['phone'] ?? '') ?>" target="_blank" class="btn btn-sm" style="background:#25D366;color:#fff;">WA</a>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (empty($filtered)): ?><tr><td colspan="9" style="text-align:center;color:#aaa;padding:40px;">No orders found.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
</div>

<?php /* ════════════ CUSTOMERS ════════════ */ elseif ($page === 'customers'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Customers</div>
    <div class="page-sub"><?= count($customers) ?> unique customers</div>
  </div>
</div>

<?php if (empty($customers)): ?>
<div class="card"><p style="text-align:center;color:#aaa;padding:40px;">No customers yet.</p></div>
<?php else: ?>
<div class="card">
<?php foreach ($customers as $phone => $c): ?>
<div class="customer-card">
  <div class="cust-avatar"><?= mb_substr($c['name'], 0, 1) ?></div>
  <div class="cust-info">
    <div class="name"><?= clean($c['name']) ?></div>
    <div class="phone">📞 <?= clean($phone) ?></div>
    <div class="meta">📍 <?= clean($c['city']) ?> &nbsp;|&nbsp; <?= count($c['orders']) ?> order<?= count($c['orders'])>1?'s':'' ?></div>
    <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:4px;">
      <?php foreach ($c['orders'] as $co): ?>
      <span class="badge badge-<?= $co['status']??'pending' ?>" title="<?= clean($co['date']??'') ?>"><?= clean($co['product']??'') ?></span>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="cust-right">
    <div class="spent">Rs <?= number_format($c['total_spent']) ?></div>
    <div class="orders">Total spent</div>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $phone) ?>" target="_blank" class="btn btn-sm" style="background:#25D366;color:#fff;margin-top:8px;">WhatsApp</a>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php /* ════════════ STOCK ════════════ */ elseif ($page === 'stock'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Stock Management</div>
    <div class="page-sub">Manage inventory for all 4 products</div>
  </div>
</div>

<?php
$lowStock = array_filter($stock, fn($p) => (int)$p['stock'] <= 10);
if (!empty($lowStock)):
?>
<div class="alert alert-warning">⚠️ Low stock alert: <?= implode(', ', array_column($lowStock, 'name')) ?></div>
<?php endif; ?>

<form method="post" action="admin-dashboard.php?p=stock">
<input type="hidden" name="action" value="update_stock">
<div class="stock-grid">
<?php foreach ($stock as $k => $p):
  $s = (int)$p['stock'];
  $low = $s <= 10;
  $pct = min(100, round($s / 100 * 100));
  $color = $low ? '#e74c3c' : ($s <= 25 ? '#e67e22' : '#1B4332');
?>
<div class="stock-card">
  <div class="prod-name"><?= clean($p['name']) ?></div>
  <div class="sku">SKU: <?= clean($p['sku']) ?></div>
  <div class="stock-num" style="color:<?= $color ?>;"><?= $s ?></div>
  <div style="font-size:.7rem;color:#888;margin-bottom:10px;">units in stock <?= $low?'⚠️ LOW':'' ?></div>
  <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;--c:<?= $color ?>;"></div></div>
  <label>Update Stock Qty</label>
  <input type="number" name="stock_<?= $k ?>" value="<?= $s ?>" min="0">
  <label>Price (Rs)</label>
  <input type="number" name="price_<?= $k ?>" value="<?= (int)$p['price'] ?>" min="0">
</div>
<?php endforeach; ?>
</div>
<div style="margin-top:16px;">
  <button type="submit" class="btn btn-primary">💾 Save All Stock & Prices</button>
</div>
</form>

<?php /* ════════════ META ADS ════════════ */ elseif ($page === 'meta'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Meta Ads & Pixel</div>
    <div class="page-sub">Last 30 days — Pixel ID: <?= META_PIXEL_ID ?></div>
  </div>
  <a href="https://business.facebook.com/events_manager2/list/dataset/<?= META_PIXEL_ID ?>" target="_blank" class="btn btn-primary btn-sm">Open Events Manager ↗</a>
</div>

<?php
$metaStats = fetchMetaStats(META_ACCESS_TOKEN, META_PIXEL_ID);
$eventColors = ['Purchase'=>'#B8860B','ViewContent'=>'#1B4332','AddToCart'=>'#27ae60','InitiateCheckout'=>'#8e44ad','Contact'=>'#25D366'];
$eventIcons  = ['Purchase'=>'💳','ViewContent'=>'👁','AddToCart'=>'🛒','InitiateCheckout'=>'📋','Contact'=>'📲'];
?>

<?php if ($metaStats): ?>
<div class="meta-grid">
<?php foreach ($metaStats as $ev => $count): ?>
<div class="meta-card">
  <div style="font-size:1.8rem;margin-bottom:6px;"><?= $eventIcons[$ev] ?? '📌' ?></div>
  <div class="ev-name"><?= $ev ?></div>
  <div class="ev-num" style="color:<?= $eventColors[$ev] ?? '#1B4332' ?>;"><?= number_format($count) ?></div>
</div>
<?php endforeach; ?>
</div>

<div class="two-col">
  <div class="card">
    <div class="section-title">Event Breakdown</div>
    <?php
    $totalEvents = array_sum($metaStats);
    foreach ($metaStats as $ev => $count):
      $pct = $totalEvents > 0 ? round($count/$totalEvents*100) : 0;
    ?>
    <div style="margin-bottom:12px;">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
        <span style="font-size:.8rem;font-weight:600;"><?= $eventIcons[$ev]??'' ?> <?= $ev ?></span>
        <span style="font-size:.75rem;color:#888;"><?= number_format($count) ?> (<?= $pct ?>%)</span>
      </div>
      <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;--c:<?= $eventColors[$ev]??'#1B4332' ?>;"></div></div>
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
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
      <div>
        <div style="font-size:.8rem;font-weight:600;"><?= $label ?></div>
        <div style="font-size:.7rem;color:#888;"><?= $rate ?>% conversion</div>
      </div>
      <div style="font-size:1.5rem;font-weight:800;color:#1B4332;"><?= number_format($count) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php else: ?>
<div class="card">
  <div style="text-align:center;padding:40px;">
    <div style="font-size:2rem;margin-bottom:10px;">📡</div>
    <p style="color:#888;">Unable to fetch Meta stats. Check your access token in config.php</p>
    <a href="https://business.facebook.com/events_manager2/list/dataset/<?= META_PIXEL_ID ?>" target="_blank" class="btn btn-primary" style="margin-top:16px;display:inline-flex;">Open Events Manager ↗</a>
  </div>
</div>
<?php endif; ?>

<div class="card" style="margin-top:16px;">
  <div class="section-title">Quick Links</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px;">
    <a href="https://business.facebook.com/events_manager2/list/dataset/<?= META_PIXEL_ID ?>/test_events" target="_blank" class="btn btn-outline btn-sm">🧪 Test Events</a>
    <a href="https://adsmanager.facebook.com" target="_blank" class="btn btn-outline btn-sm">📊 Ads Manager</a>
    <a href="https://analytics.google.com" target="_blank" class="btn btn-outline btn-sm">📈 Google Analytics</a>
    <a href="https://business.facebook.com" target="_blank" class="btn btn-outline btn-sm">💼 Business Manager</a>
  </div>
</div>

<?php /* ════════════ SETTINGS ════════════ */ elseif ($page === 'settings'): ?>
<div class="page-header">
  <div class="page-title">Settings</div>
</div>
<div class="two-col">
  <div class="card">
    <div class="section-title">Admin Info</div>
    <div class="settings-form">
      <label>Admin URL</label>
      <input type="text" value="https://nasiroilexpert.com/admin-dashboard.php" readonly>
      <label>Pixel ID</label>
      <input type="text" value="<?= META_PIXEL_ID ?>" readonly>
      <label>Business Email</label>
      <input type="text" value="info@nasiroilexpert.com" readonly>
    </div>
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
