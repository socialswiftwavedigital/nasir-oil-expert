<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/* ── Session + Security ───────────────────────────────────────── */
session_start();

// Session timeout: 30 minutes idle = auto logout
define('SESSION_TIMEOUT', 1800);
if (isset($_SESSION['noe_admin'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: admin-dashboard.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// Login rate limiting (5 attempts → 10 min block)
$_ATTEMPTS_FILE = __DIR__ . '/.login_attempts.json';
function getAttempts($file) {
    if (!file_exists($file)) return [];
    $d = json_decode(file_get_contents($file), true) ?: [];
    // purge expired
    foreach ($d as $ip => $v) { if (time() - $v['t'] > 600) unset($d[$ip]); }
    return $d;
}
function saveAttempts($file, $d) { file_put_contents($file, json_encode($d)); }
$_CLIENT_IP = $_SERVER['HTTP_X_FORWARDED_FOR'] ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : ($_SERVER['REMOTE_ADDR'] ?? '');
$_CLIENT_IP = trim($_CLIENT_IP);
$_ATTEMPTS  = getAttempts($_ATTEMPTS_FILE);
$_BLOCKED   = isset($_ATTEMPTS[$_CLIENT_IP]) && $_ATTEMPTS[$_CLIENT_IP]['c'] >= 5
              ? max(0, 600 - (time() - $_ATTEMPTS[$_CLIENT_IP]['t'])) : 0;
$_LOGIN_ERR = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
    if ($_BLOCKED > 0) {
        $_LOGIN_ERR = "Too many attempts. Try again in " . ceil($_BLOCKED/60) . " min.";
    } elseif ($_POST['pass'] === ADMIN_PASS) {
        unset($_ATTEMPTS[$_CLIENT_IP]);
        saveAttempts($_ATTEMPTS_FILE, $_ATTEMPTS);
        $_SESSION['noe_admin'] = true;
        $_SESSION['last_activity'] = time();
        header('Location: admin-dashboard.php'); exit;
    } else {
        if (!isset($_ATTEMPTS[$_CLIENT_IP]) || time() - $_ATTEMPTS[$_CLIENT_IP]['t'] > 600) {
            $_ATTEMPTS[$_CLIENT_IP] = ['c' => 1, 't' => time()];
        } else {
            $_ATTEMPTS[$_CLIENT_IP]['c']++;
        }
        saveAttempts($_ATTEMPTS_FILE, $_ATTEMPTS);
        $left = 5 - $_ATTEMPTS[$_CLIENT_IP]['c'];
        $_LOGIN_ERR = $left > 0 ? "Wrong password. $left attempt" . ($left>1?'s':'') . " left." : "Blocked for 10 minutes.";
    }
}

if (($_GET['logout'] ?? '') === '1') { session_destroy(); header('Location: admin-dashboard.php'); exit; }
if (!($_SESSION['noe_admin'] ?? false)) { showLogin($_LOGIN_ERR, $_BLOCKED); exit; }

/* ── Helpers ──────────────────────────────────────────────────── */
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
$db     = getDB();
$page   = $_GET['p'] ?? 'dashboard';

/* ── POST Actions ─────────────────────────────────────────────── */
if ($_POST['action'] ?? '' === 'update_status') {
    $id = clean($_POST['id'] ?? '');
    $st = clean($_POST['status'] ?? 'pending');
    $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$st, $id]);
    header('Location: admin-dashboard.php?p=orders'); exit;
}
if ($_POST['action'] ?? '' === 'update_note') {
    $id   = clean($_POST['id'] ?? '');
    $note = clean($_POST['note'] ?? '');
    $db->prepare("UPDATE orders SET admin_note=? WHERE id=?")->execute([$note, $id]);
    header('Location: admin-dashboard.php?p=orders'); exit;
}
if ($_POST['action'] ?? '' === 'update_tracking') {
    $id  = clean($_POST['id'] ?? '');
    $trk = clean($_POST['tracking'] ?? '');
    $row = $db->prepare("SELECT timeline FROM orders WHERE id=?");
    $row->execute([$id]);
    $existing = $row->fetchColumn();
    $tl = json_decode($existing ?: '[]', true) ?: [];
    $tl[] = ['status'=>'tracking','time'=>date('d M Y, h:i A'),'note'=>'Tracking: '.$trk];
    $db->prepare("UPDATE orders SET tracking=?, timeline=? WHERE id=?")->execute([$trk, json_encode($tl), $id]);
    header('Location: admin-dashboard.php?p=orders'); exit;
}
if ($_POST['action'] ?? '' === 'update_abandoned_status') {
    $id = clean($_POST['id'] ?? '');
    $st = clean($_POST['status'] ?? 'new');
    $db->prepare("UPDATE abandoned_forms SET status=? WHERE id=?")->execute([$st, $id]);
    header('Location: admin-dashboard.php?p=abandoned'); exit;
}
if ($_POST['action'] ?? '' === 'bulk_status') {
    $ids = array_map('trim', (array)($_POST['ids'] ?? []));
    $st  = clean($_POST['bulk_st'] ?? 'pending');
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$st], $ids);
        $db->prepare("UPDATE orders SET status=? WHERE id IN ($placeholders)")->execute($params);
    }
    header('Location: admin-dashboard.php?p=orders'); exit;
}
if ($_POST['action'] ?? '' === 'update_stock') {
    $stmt = $db->prepare("UPDATE stock SET qty=?, price=? WHERE slug=?");
    $slugs = $db->query("SELECT slug FROM stock")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($slugs as $k) {
        $qty   = isset($_POST["stock_$k"]) ? (int)$_POST["stock_$k"] : null;
        $price = isset($_POST["price_$k"])  ? (int)$_POST["price_$k"]  : null;
        if ($qty !== null && $price !== null) $stmt->execute([$qty, $price, $k]);
    }
    header('Location: admin-dashboard.php?p=stock'); exit;
}

/* ── CSV Export ───────────────────────────────────────────────── */
if (($_GET['export'] ?? '') === 'csv') {
    $allOrders = dbOrders($db);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="nasir-orders-' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF";
    echo "Order ID,Date,Name,Phone,City,Address,Product,Price,Status,Source Page,Note\n";
    foreach ($allOrders as $o) {
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
    $ad_account = 'act_1059183479834210';

    // Ads Insights API — spend, reach, impressions, clicks, CTR, CPC, CPM
    $fields = 'impressions,clicks,spend,reach,cpm,cpc,ctr,actions';
    $url = "https://graph.facebook.com/v21.0/{$ad_account}/insights?fields=" . urlencode($fields)
         . "&date_preset=last_30d&access_token=" . urlencode($token);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>8]);
    $res  = curl_exec($ch); curl_close($ch);
    $ins  = json_decode($res, true);
    $row  = $ins['data'][0] ?? [];

    // Extract purchase/other action counts
    $purchases = 0; $add_to_cart = 0; $initiate = 0; $contacts = 0;
    foreach (($row['actions'] ?? []) as $a) {
        if ($a['action_type'] === 'purchase')              $purchases  += (int)$a['value'];
        if ($a['action_type'] === 'add_to_cart')           $add_to_cart+= (int)$a['value'];
        if ($a['action_type'] === 'initiate_checkout')     $initiate   += (int)$a['value'];
        if (str_contains($a['action_type'],'contact'))     $contacts   += (int)$a['value'];
    }

    return [
        'spend'       => (float)($row['spend']       ?? 0),
        'impressions' => (int)  ($row['impressions'] ?? 0),
        'clicks'      => (int)  ($row['clicks']      ?? 0),
        'reach'       => (int)  ($row['reach']       ?? 0),
        'cpm'         => (float)($row['cpm']         ?? 0),
        'cpc'         => (float)($row['cpc']         ?? 0),
        'ctr'         => (float)($row['ctr']         ?? 0),
        'Purchase'          => $purchases,
        'AddToCart'         => $add_to_cart,
        'InitiateCheckout'  => $initiate,
        'Contact'           => $contacts,
        'ViewContent'       => (int)($row['impressions'] ?? 0), // fallback
    ];
}

$orders    = dbOrders($db);
$stock     = dbStock($db);
$stats     = orderStats($orders);
$prodSales = productSales($orders);
$customers = getCustomers($orders);
$abandoned = $db->query("SELECT * FROM abandoned_forms ORDER BY created_at DESC")->fetchAll();
// add formatted date field for display
foreach ($abandoned as &$a) {
    if (!isset($a['date'])) $a['date'] = date('d M Y, h:i A', strtotime($a['created_at'] ?? 'now'));
}
unset($a);
$newAbandoned = count(array_filter($abandoned, fn($a) => ($a['status']??'new') === 'new'));

// Revenue chart data for multiple periods
function buildChartData($orders, $days) {
    $labels = []; $data = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $labels[] = date('j M', strtotime("-$i days"));
        $data[]   = 0;
    }
    foreach ($orders as $o) {
        if (!($o['date'] ?? '')) continue;
        $key = date('j M', strtotime($o['date']));
        $idx = array_search($key, $labels);
        if ($idx !== false) $data[$idx] += (float)($o['price'] ?? 0);
    }
    return ['labels' => $labels, 'data' => $data];
}
$todayStr    = date('d M Y');
$todayOrders = array_values(array_filter($orders, fn($o) => str_starts_with($o['date'] ?? '', $todayStr)));
$chartToday  = ['labels' => ['Today'], 'data' => [array_sum(array_column($todayOrders, 'price'))]];
$chart7      = buildChartData($orders, 7);
$chart14     = buildChartData($orders, 14);
$chart30     = buildChartData($orders, 30);
$chartDaysJson = json_encode($chart14['labels']);
$chartRevJson  = json_encode($chart14['data']);

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
function showLogin($err = '', $blocked = 0) { ?>
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
.err{background:#fdecea;color:#c0392b;border:1px solid #f5c6cb;border-radius:8px;padding:10px 14px;font-size:.78rem;font-weight:500;margin-bottom:14px;text-align:left;}
.blocked-bar{background:#fff3cd;color:#856404;border-radius:8px;padding:10px 14px;font-size:.78rem;font-weight:500;margin-bottom:14px;}
button:disabled{opacity:.6;cursor:not-allowed;transform:none !important;box-shadow:none !important;}
</style></head><body>
<div class="card">
  <div class="logo-wrap"><img src="images/logo.png" alt="Nasir Oil Expert"></div>
  <h1>Nasir Oil Expert</h1>
  <p>Admin Dashboard — Secure Access</p>
  <div class="divider"></div>
  <?php if ($err): ?><div class="err">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if (isset($_GET['timeout'])): ?><div class="err">⏱️ Session expired. Please login again.</div><?php endif; ?>
  <form method="post" action="admin-dashboard.php">
    <input type="password" name="pass" placeholder="Enter admin password" autofocus <?= $blocked>0?'disabled':'' ?>>
    <button type="submit" <?= $blocked>0?'disabled':'' ?>><?= $blocked>0 ? "Blocked ($blocked sec)" : 'Login →' ?></button>
  </form>
  <div class="hint">nasiroilexpert.com</div>
</div></body></html>
<?php }

/* ═══════════════════ NAV CONFIG ════════════════════════════ */
$nav = [
    'dashboard' => ['icon'=>'📊','label'=>'Dashboard'],
    'orders'    => ['icon'=>'📦','label'=>'Orders'],
    'customers' => ['icon'=>'👥','label'=>'Customers'],
    'abandoned' => ['icon'=>'🔔','label'=>'Abandoned'],
    'cities'    => ['icon'=>'📍','label'=>'Cities'],
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
.mob-topbar{display:none;}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:199;}

@media(max-width:768px){
  body{flex-direction:column;}
  .sidebar{
    position:fixed;top:0;left:0;width:240px;height:100vh;z-index:200;
    transform:translateX(-100%);transition:transform .25s ease;
  }
  .sidebar.open{transform:translateX(0);}
  .sidebar-overlay.open{display:block;}
  .mob-topbar{
    display:flex;align-items:center;justify-content:space-between;
    background:#1B4332;color:#fff;padding:0 16px;height:52px;
    position:sticky;top:0;z-index:100;flex-shrink:0;
  }
  .mob-topbar-left{display:flex;align-items:center;gap:10px;}
  .mob-topbar-left img{width:30px;height:30px;object-fit:contain;border-radius:8px;}
  .mob-topbar-left span{font-size:.82rem;font-weight:700;color:#fff;}
  .mob-menu-btn{background:none;border:none;color:#fff;font-size:1.4rem;cursor:pointer;padding:4px 6px;line-height:1;}
  .main{padding:14px 12px;width:100%;}
  .two-col{grid-template-columns:1fr;}
  .page-title{font-size:1.4rem;}
  .stat-grid{grid-template-columns:1fr 1fr;gap:10px;}
  .stat-num{font-size:1.7rem;}
  .card{padding:16px;}
  td,th{padding:8px 10px;font-size:.72rem;}
  .btn{padding:7px 12px;font-size:.68rem;}
  .page-header{margin-bottom:14px;}
}

select.status-sel{padding:5px 9px;border:1px solid #ddd;border-radius:6px;font-size:.73rem;font-family:'Poppins',sans-serif;outline:none;}
select.status-sel:focus{border-color:#1B4332;}
</style>
</head>
<body>

<!-- Mobile Topbar -->
<div class="mob-topbar">
  <div class="mob-topbar-left">
    <button class="mob-menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('open');document.querySelector('.sidebar-overlay').classList.toggle('open')">☰</button>
    <img src="images/logo.png" alt="Logo">
    <span>Admin Panel</span>
  </div>
  <a href="?logout=1" style="color:rgba(255,255,255,.6);font-size:.72rem;text-decoration:none;">Logout</a>
</div>
<div class="sidebar-overlay" onclick="document.querySelector('.sidebar').classList.remove('open');this.classList.remove('open')"></div>

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
    <?php elseif ($key==='abandoned' && $newAbandoned > 0): ?>
      <span class="badge-pill"><?= $newAbandoned ?></span>
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

<div class="card" style="margin-bottom:20px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
    <div>
      <div class="section-title" style="margin:0;">Revenue Chart</div>
      <div style="font-size:.7rem;color:#aaa;margin-top:2px;">Total: <strong id="adRevTotal" style="color:#1B4332;">Rs <?= number_format(array_sum($chart14['data'])) ?></strong> &nbsp;·&nbsp; <span id="adRevPeriod">Last 14 Days</span></div>
    </div>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      <button onclick="adSwitchPeriod('today')" id="adbtn-today" class="rev-filter-btn">Today</button>
      <button onclick="adSwitchPeriod('7')"     id="adbtn-7"     class="rev-filter-btn">7 Days</button>
      <button onclick="adSwitchPeriod('14')"    id="adbtn-14"    class="rev-filter-btn rev-filter-active">14 Days</button>
      <button onclick="adSwitchPeriod('30')"    id="adbtn-30"    class="rev-filter-btn">30 Days</button>
    </div>
  </div>
  <canvas id="revenueChart" height="80"></canvas>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
.rev-filter-btn{padding:5px 13px;border-radius:20px;border:1.5px solid #d0ddd5;background:#fff;color:#888;font-family:'Poppins',sans-serif;font-size:.67rem;font-weight:600;cursor:pointer;transition:.15s;}
.rev-filter-btn:hover{border-color:#1B4332;color:#1B4332;}
.rev-filter-active{background:#1B4332;color:#fff !important;border-color:#1B4332;}
</style>
<script>
var _adPeriods = {
  'today': { labels: <?= json_encode($chartToday['labels']) ?>, data: <?= json_encode($chartToday['data']) ?>, label: 'Today' },
  '7':     { labels: <?= json_encode($chart7['labels'])     ?>, data: <?= json_encode($chart7['data'])     ?>, label: 'Last 7 Days' },
  '14':    { labels: <?= json_encode($chart14['labels'])    ?>, data: <?= json_encode($chart14['data'])    ?>, label: 'Last 14 Days' },
  '30':    { labels: <?= json_encode($chart30['labels'])    ?>, data: <?= json_encode($chart30['data'])    ?>, label: 'Last 30 Days' }
};
var _adChart = new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: _adPeriods['14'].labels,
    datasets: [{
      label: 'Revenue (Rs)',
      data: _adPeriods['14'].data,
      backgroundColor: 'rgba(27,67,50,.15)',
      borderColor: '#1B4332',
      borderWidth: 2,
      borderRadius: 6,
      hoverBackgroundColor: 'rgba(27,67,50,.3)'
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { family: 'Poppins', size: 10 }, callback: v => 'Rs ' + v.toLocaleString() } },
      x: { grid: { display: false }, ticks: { font: { family: 'Poppins', size: 10 } } }
    }
  }
});
function adSwitchPeriod(p) {
  var d = _adPeriods[p];
  _adChart.data.labels = d.labels;
  _adChart.data.datasets[0].data = d.data;
  _adChart.update();
  var total = d.data.reduce(function(a,b){return a+b;},0);
  document.getElementById('adRevTotal').textContent = 'Rs ' + total.toLocaleString('en-PK');
  document.getElementById('adRevPeriod').textContent = d.label;
  document.querySelectorAll('.rev-filter-btn').forEach(function(b){ b.classList.remove('rev-filter-active'); });
  document.getElementById('adbtn-' + p).classList.add('rev-filter-active');
}
</script>

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

<!-- Bulk Update Bar -->
<div id="bulkBar" style="display:none;background:#1B4332;color:#fff;padding:12px 20px;border-radius:12px;margin-bottom:12px;display:none;align-items:center;gap:12px;flex-wrap:wrap;">
  <span id="bulkCount" style="font-size:.8rem;font-weight:600;">0 selected</span>
  <form method="post" action="admin-dashboard.php?p=orders" id="bulkForm" style="display:flex;gap:8px;align-items:center;margin:0;">
    <input type="hidden" name="action" value="bulk_status">
    <div id="bulkIdsContainer"></div>
    <select name="bulk_st" style="padding:6px 10px;border-radius:6px;font-family:'Poppins',sans-serif;font-size:.75rem;border:none;outline:none;">
      <option value="done">✅ Mark Done</option>
      <option value="pending">⏳ Mark Pending</option>
      <option value="cancelled">❌ Mark Cancelled</option>
    </select>
    <button type="submit" class="btn btn-sm" style="background:#74c69d;color:#1B4332;font-weight:700;">Apply</button>
  </form>
  <button onclick="clearSelection()" style="background:transparent;border:1px solid rgba(255,255,255,.4);color:#fff;padding:5px 12px;border-radius:6px;font-size:.72rem;cursor:pointer;font-family:'Poppins',sans-serif;">Clear</button>
</div>

<div class="card">
<div class="table-wrap">
<table>
  <thead><tr>
    <th><input type="checkbox" id="selectAll" onchange="toggleAll(this)" style="cursor:pointer;"></th>
    <th>Order ID</th><th>Date</th><th>Name</th><th>Phone</th><th>City</th><th>Address</th><th>Product</th><th>Price</th><th>Page</th><th>Tracking</th><th>Status</th><th>Note</th><th>WhatsApp</th>
  </tr></thead>
  <tbody>
  <?php foreach ($filtered as $i => $o):
    $wa = waNumber($o['phone']??'');
    $name = clean($o['name']??''); $prod = clean($o['product']??'');
    $price = (float)($o['price']??0); $total = $price + 250;
    $waTpl1 = $wa ? 'https://wa.me/'.$wa.'?text='.urlencode("Assalam o Alaikum $name! ✅ Aapka order *$prod* confirm ho gaya hai.\nTotal: Rs ".number_format($total)." (COD)\nHum jald dispatch karenge. Shukriya! 🌿 Nasir Oil Expert") : '';
    $waTpl2 = $wa ? 'https://wa.me/'.$wa.'?text='.urlencode("Assalam o Alaikum $name! 🚚 Aapka order *$prod* dispatch ho gaya hai.\n2-3 din mein deliver ho jayega. Insha'Allah!\n— Nasir Oil Expert") : '';
    $waTpl3 = $wa ? 'https://wa.me/'.$wa.'?text='.urlencode("Assalam o Alaikum $name! ✅ Aapka order *$prod* deliver ho gaya.\nUmmeed hai pasand aaya hoga. Feedback zaroor dein! 🌿\n— Nasir Oil Expert") : '';
  ?>
  <tr>
    <td><input type="checkbox" class="order-chk" value="<?= clean($o['id']??'') ?>" onchange="updateBulk()" style="cursor:pointer;"></td>
    <td>
      <strong style="font-size:.73rem;"><?= clean($o['id']??'') ?></strong>
      <?php if (!empty($o['duplicate'])): ?><br><span class="badge" style="background:#fff3cd;color:#856404;font-size:.58rem;">⚠️ DUPLICATE</span><?php endif; ?>
    </td>
    <td style="font-size:.68rem;color:#aaa;white-space:nowrap;"><?= clean($o['date']??'') ?></td>
    <td><strong><?= $name ?></strong></td>
    <td style="color:#1B4332;font-size:.78rem;white-space:nowrap;">📞 <?= clean($o['phone']??'') ?></td>
    <td style="font-size:.78rem;">📍 <?= clean($o['city']??'—') ?></td>
    <td style="font-size:.72rem;color:#888;max-width:130px;"><?= clean($o['address']??'—') ?></td>
    <td style="font-size:.8rem;font-weight:600;"><?= $prod ?></td>
    <td><strong>Rs <?= number_format($price) ?></strong></td>
    <td style="font-size:.68rem;color:#888;white-space:nowrap;"><?= pageLabel($o['source_page']??'') ?></td>
    <td>
      <form method="post" style="display:flex;gap:4px;min-width:130px;">
        <input type="hidden" name="action" value="update_tracking">
        <input type="hidden" name="id" value="<?= clean($o['id']??'') ?>">
        <input type="text" name="tracking" value="<?= clean($o['tracking']??'') ?>" placeholder="TRK#..." style="flex:1;min-width:0;padding:5px 8px;border:1px solid #e4e4e4;border-radius:6px;font-size:.72rem;font-family:'Poppins',sans-serif;outline:none;">
        <button type="submit" class="btn btn-primary btn-sm" title="Save tracking">✓</button>
      </form>
      <?php if (!empty($o['tracking']) && $wa): ?>
        <?php $trkMsg = urlencode("Assalam o Alaikum $name! 🚚 Aapka order track karein:\nTracking: ".$o['tracking']."\n— Nasir Oil Expert"); ?>
        <a href="https://wa.me/<?= $wa ?>?text=<?= $trkMsg ?>" target="_blank" class="btn-wa" style="font-size:.65rem;margin-top:4px;display:inline-flex;">📲 Send</a>
      <?php endif; ?>
    </td>
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
      <form method="post" style="display:flex;gap:4px;min-width:130px;">
        <input type="hidden" name="action" value="update_note">
        <input type="hidden" name="id" value="<?= clean($o['id']??'') ?>">
        <input type="text" name="note" value="<?= clean($o['admin_note']??'') ?>" placeholder="Note..." style="flex:1;min-width:0;padding:5px 8px;border:1px solid #e4e4e4;border-radius:6px;font-size:.72rem;font-family:'Poppins',sans-serif;outline:none;">
        <button type="submit" class="btn btn-primary btn-sm">✓</button>
      </form>
    </td>
    <td>
      <?php if ($wa): ?>
      <div style="display:flex;flex-direction:column;gap:4px;min-width:110px;">
        <a href="<?= $waTpl1 ?>" target="_blank" class="btn-wa" style="font-size:.65rem;">✅ Confirm</a>
        <a href="<?= $waTpl2 ?>" target="_blank" class="btn-wa" style="font-size:.65rem;background:#128C7E;">🚚 Dispatch</a>
        <a href="<?= $waTpl3 ?>" target="_blank" class="btn-wa" style="font-size:.65rem;background:#075E54;">📦 Delivered</a>
      </div>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (empty($filtered)): ?><tr><td colspan="14" style="text-align:center;color:#ccc;padding:40px;">No orders found.</td></tr><?php endif; ?>
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

<?php /* ══════ ABANDONED FORMS ══════ */ elseif ($page === 'abandoned'): ?>
<div class="page-header">
  <div>
    <div class="page-title">Abandoned Forms</div>
    <div class="page-sub"><?= count($abandoned) ?> captured · <?= $newAbandoned ?> new</div>
  </div>
</div>

<?php if (empty($abandoned)): ?>
<div class="card"><p style="text-align:center;color:#ccc;padding:40px;">No abandoned forms yet. Forms with partial data after 30s inactivity will appear here.</p></div>
<?php else: ?>
<div class="card">
<div class="table-wrap">
<table>
  <thead><tr>
    <th>ID</th><th>Date</th><th>Name</th><th>Phone</th><th>Product</th><th>Page</th><th>Status</th><th>Action</th>
  </tr></thead>
  <tbody>
  <?php foreach ($abandoned as $a):
    $wa = waNumber($a['phone']??'');
    $aName = clean($a['name']??'');
    $aProd = clean($a['product']??'');
    $waMsgAban = $wa ? 'https://wa.me/'.$wa.'?text='.urlencode("Assalam o Alaikum $aName! 👋 Aap Nasir Oil Expert sy ".($aProd?:"hair oil")." order karna chahte they?\nAaj special offer hai — abhi order karein:\nnasiroilexpert.com\n— Nasir Oil Expert") : '';
    $stColor = ['new'=>'#e74c3c','contacted'=>'#e67e22','converted'=>'#27ae60','ignore'=>'#aaa'];
  ?>
  <tr style="<?= ($a['status']??'new')==='new' ? 'background:#fffdf0;' : '' ?>">
    <td><strong style="font-size:.73rem;"><?= clean($a['id']??'') ?></strong></td>
    <td style="font-size:.68rem;color:#aaa;white-space:nowrap;"><?= clean($a['date']??'') ?></td>
    <td><strong><?= $aName ?: '<span style="color:#ccc;">—</span>' ?></strong></td>
    <td style="color:#1B4332;font-size:.78rem;white-space:nowrap;"><?php if ($a['phone']??''): ?>📞 <?= clean($a['phone']) ?><?php else: ?><span style="color:#ccc;">—</span><?php endif; ?></td>
    <td style="font-size:.8rem;"><?= $aProd ?: '<span style="color:#ccc;">Unknown</span>' ?></td>
    <td style="font-size:.68rem;color:#888;"><?= pageLabel($a['page']??'') ?></td>
    <td>
      <span class="badge" style="background:<?= $stColor[$a['status']??'new']??'#aaa' ?>20;color:<?= $stColor[$a['status']??'new']??'#aaa' ?>;border:1px solid <?= $stColor[$a['status']??'new']??'#aaa' ?>40;">
        <?= ucfirst($a['status']??'new') ?>
      </span>
    </td>
    <td>
      <div style="display:flex;flex-direction:column;gap:4px;">
        <form method="post" style="display:flex;gap:4px;">
          <input type="hidden" name="action" value="update_abandoned_status">
          <input type="hidden" name="id" value="<?= clean($a['id']??'') ?>">
          <select name="status" class="status-sel" onchange="this.form.submit()" style="font-size:.68rem;">
            <option value="new"       <?= ($a['status']??'new')==='new'?'selected':'' ?>>🔴 New</option>
            <option value="contacted" <?= ($a['status']??'')==='contacted'?'selected':'' ?>>🟠 Contacted</option>
            <option value="converted" <?= ($a['status']??'')==='converted'?'selected':'' ?>>🟢 Converted</option>
            <option value="ignore"    <?= ($a['status']??'')==='ignore'?'selected':'' ?>>⚫ Ignore</option>
          </select>
        </form>
        <?php if ($waMsgAban): ?>
        <a href="<?= $waMsgAban ?>" target="_blank" class="btn-wa" style="font-size:.65rem;">📲 Follow Up</a>
        <?php endif; ?>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
</div>
<?php endif; ?>

<?php /* ══════ CITIES ══════ */ elseif ($page === 'cities'):
  $cityMap = [];
  foreach ($orders as $o) {
    $c = trim($o['city'] ?? '');
    if (!$c) { $c = 'Unknown'; }
    if (!isset($cityMap[$c])) $cityMap[$c] = ['orders'=>0,'revenue'=>0,'pending'=>0,'done'=>0,'cancelled'=>0,'products'=>[]];
    $cityMap[$c]['orders']++;
    $cityMap[$c]['revenue'] += (float)($o['price'] ?? 0);
    $st = $o['status'] ?? 'pending';
    $cityMap[$c][$st] = ($cityMap[$c][$st] ?? 0) + 1;
    $prod = $o['product'] ?? '';
    if ($prod) $cityMap[$c]['products'][$prod] = ($cityMap[$c]['products'][$prod] ?? 0) + 1;
  }
  uasort($cityMap, fn($a,$b) => $b['orders'] - $a['orders']);
  $totalCityOrders = array_sum(array_column($cityMap, 'orders'));
?>
<div class="page-header">
  <div>
    <div class="page-title">City Report</div>
    <div class="page-sub"><?= count($cityMap) ?> cities · <?= $totalCityOrders ?> total orders</div>
  </div>
</div>

<?php if (empty($cityMap)): ?>
<div class="card"><p style="text-align:center;color:#ccc;padding:40px;">No city data yet.</p></div>
<?php else: ?>

<div class="stat-grid" style="margin-bottom:18px;">
<?php $topCities = array_slice($cityMap, 0, 4, true); foreach ($topCities as $cityName => $cd): ?>
<div class="stat-card" style="--c:#1B4332">
  <div class="stat-num" style="font-size:1.6rem;"><?= $cd['orders'] ?></div>
  <div class="stat-label">📍 <?= clean($cityName) ?></div>
  <div class="stat-sub">Rs <?= number_format($cd['revenue']) ?></div>
</div>
<?php endforeach; ?>
</div>

<div class="card">
<div class="table-wrap">
<table>
  <thead><tr>
    <th>City</th><th>Orders</th><th>Revenue</th><th>Pending</th><th>Done</th><th>Cancelled</th><th>Top Product</th><th>Share %</th>
  </tr></thead>
  <tbody>
  <?php foreach ($cityMap as $cityName => $cd):
    arsort($cd['products']);
    $topProd = array_key_first($cd['products']) ?? '—';
    $share = $totalCityOrders > 0 ? round($cd['orders']/$totalCityOrders*100) : 0;
  ?>
  <tr>
    <td><strong>📍 <?= clean($cityName) ?></strong></td>
    <td><strong><?= $cd['orders'] ?></strong></td>
    <td><strong style="color:#B8860B;">Rs <?= number_format($cd['revenue']) ?></strong></td>
    <td><span class="badge badge-pending"><?= $cd['pending'] ?></span></td>
    <td><span class="badge badge-done"><?= $cd['done'] ?></span></td>
    <td><span class="badge badge-cancelled"><?= $cd['cancelled'] ?></span></td>
    <td style="font-size:.75rem;"><?= clean($topProd) ?></td>
    <td>
      <div style="display:flex;align-items:center;gap:8px;">
        <div class="prog-bar" style="flex:1;margin:0;"><div class="prog-fill" style="width:<?= $share ?>%;--c:#1B4332;"></div></div>
        <span style="font-size:.72rem;color:#888;min-width:28px;"><?= $share ?>%</span>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
</div>
<?php endif; ?>

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
?>

<?php if ($metaStats): ?>

<!-- Ad Performance KPIs -->
<div class="stat-grid" style="margin-bottom:18px;">
  <?php
  $kpis = [
    ['💰','Ad Spend','Rs '.number_format($metaStats['spend']),'Last 30 days','#B8860B'],
    ['👥','Reach',number_format($metaStats['reach']),'Unique people','#1B4332'],
    ['👁','Impressions',number_format($metaStats['impressions']),'Total views','#8e44ad'],
    ['🖱','Clicks',number_format($metaStats['clicks']),'Link clicks','#27ae60'],
    ['📊','CTR',number_format($metaStats['ctr'],2).'%','Click-through rate','#2980b9'],
    ['💵','CPC','Rs '.number_format($metaStats['cpc'],1),'Cost per click','#e67e22'],
  ];
  foreach ($kpis as [$ic,$lb,$val,$sub,$col]): ?>
  <div class="stat-card" style="--c:<?= $col ?>">
    <div style="font-size:1.3rem;margin-bottom:4px;"><?= $ic ?></div>
    <div class="stat-num"><?= $val ?></div>
    <div class="stat-label"><?= $lb ?></div>
    <div class="stat-sub"><?= $sub ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Conversion Results -->
<div class="two-col">
  <div class="card">
    <div class="section-title">Conversion Results (Last 30 Days)</div>
    <?php
    $convs = [
      ['💳','Purchases',         $metaStats['Purchase'],         '#B8860B'],
      ['🛒','Add to Cart',       $metaStats['AddToCart'],        '#27ae60'],
      ['📋','Initiate Checkout', $metaStats['InitiateCheckout'], '#8e44ad'],
      ['📲','Contacts',          $metaStats['Contact'],          '#25D366'],
    ];
    foreach ($convs as [$ic,$lb,$cnt,$col]): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;">
      <span style="font-size:.82rem;font-weight:600;"><?= $ic ?> <?= $lb ?></span>
      <span style="font-size:1.3rem;font-weight:700;color:<?= $col ?>;"><?= number_format($cnt) ?></span>
    </div>
    <?php endforeach; ?>
    <?php if ($metaStats['spend'] > 0 && $metaStats['Purchase'] > 0): ?>
    <div style="margin-top:12px;padding:10px;background:#f4f8f5;border-radius:8px;font-size:.78rem;">
      💡 Cost per Purchase: <strong>Rs <?= number_format($metaStats['spend'] / $metaStats['Purchase'], 0) ?></strong>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-title">Funnel Overview</div>
    <?php
    $funnel = ['impressions'=>'Impressions','clicks'=>'Clicks','AddToCart'=>'Add to Cart','Purchase'=>'Purchases'];
    $prev = null;
    foreach ($funnel as $key => $label):
      $count = is_string($key) && isset($metaStats[$key]) ? $metaStats[$key] : ($metaStats[$key] ?? 0);
      $rate = ($prev > 0) ? round($count / $prev * 100, 1) : 100;
      $prev = $count > 0 ? $count : $prev;
    ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;">
      <div>
        <div style="font-size:.8rem;font-weight:600;"><?= $label ?></div>
        <div style="font-size:.65rem;color:#aaa;"><?= $rate ?>% of prev step</div>
      </div>
      <div style="font-size:1.4rem;font-weight:700;color:#1B4332;"><?= number_format($count) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php else: ?>
<div class="card"><div style="text-align:center;padding:40px;">
  <div style="font-size:2.5rem;margin-bottom:12px;">📡</div>
  <p style="color:#aaa;font-size:.85rem;">Unable to fetch Meta Ads stats.<br>Check access token in config.php or verify Ad Account ID.</p>
  <a href="https://adsmanager.facebook.com" target="_blank" class="btn btn-primary" style="margin-top:16px;">Open Ads Manager ↗</a>
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
<script>
function updateBulk() {
  var chks = document.querySelectorAll('.order-chk:checked');
  var bar  = document.getElementById('bulkBar');
  var cnt  = document.getElementById('bulkCount');
  var cont = document.getElementById('bulkIdsContainer');
  if (!bar) return;
  if (chks.length > 0) {
    bar.style.display = 'flex';
    cnt.textContent = chks.length + ' selected';
    cont.innerHTML = '';
    chks.forEach(function(c) {
      var inp = document.createElement('input');
      inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = c.value;
      cont.appendChild(inp);
    });
  } else {
    bar.style.display = 'none';
  }
}
function toggleAll(el) {
  document.querySelectorAll('.order-chk').forEach(function(c){ c.checked = el.checked; });
  updateBulk();
}
function clearSelection() {
  document.querySelectorAll('.order-chk, #selectAll').forEach(function(c){ c.checked = false; });
  updateBulk();
}
</script>
</body></html>

