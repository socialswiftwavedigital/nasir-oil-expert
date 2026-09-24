<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Client password — add to config.php: define('CLIENT_PASS', 'your-client-pass');
$clientPass = defined('CLIENT_PASS') ? CLIENT_PASS : 'nasir-client-2024';

session_name('noe_client_sess');
session_start();

// Session timeout 30 min
if (isset($_SESSION['noe_client'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
        session_destroy(); header('Location: client-panel.php?timeout=1'); exit;
    }
    $_SESSION['last_activity'] = time();
}

if (($_POST['pass'] ?? '') === $clientPass) {
    $_SESSION['noe_client'] = true;
    $_SESSION['last_activity'] = time();
    header('Location: client-panel.php'); exit;
}
if (($_GET['logout'] ?? '') === '1') { session_destroy(); header('Location: client-panel.php'); exit; }
if (!($_SESSION['noe_client'] ?? false)) { showClientLogin(); exit; }

/* ── Helpers ─────────────────────────────────────────────── */
function clean($v) { return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8'); }

/* ── POST Actions ────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_action'])) {
    $db_post = getDB();
    $id = clean($_POST['id'] ?? '');
    if ($_POST['client_action'] === 'update_status' && $id) {
        $st = clean($_POST['status'] ?? 'pending');
        if (in_array($st, ['pending','done','cancelled'])) {
            $db_post->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$st, $id]);
        }
    }
    if ($_POST['client_action'] === 'update_tracking' && $id) {
        $trk = clean($_POST['tracking'] ?? '');
        $row = $db_post->prepare("SELECT timeline FROM orders WHERE id=?");
        $row->execute([$id]);
        $tl = json_decode($row->fetchColumn() ?: '[]', true) ?: [];
        $tl[] = ['status'=>'tracking','time'=>date('d M Y, h:i A'),'note'=>'Tracking: '.$trk];
        $db_post->prepare("UPDATE orders SET tracking=?, timeline=? WHERE id=?")->execute([$trk, json_encode($tl), $id]);
    }
    header('Location: client-panel.php#orders-section'); exit;
}

/* ── Data ────────────────────────────────────────────────── */
$db     = getDB();
$orders = dbOrders($db);
$stock  = dbStock($db);

// Stats
$today = date('d M Y');
$week  = strtotime('-7 days');
$stats = ['total'=>0,'pending'=>0,'done'=>0,'revenue'=>0,'today'=>0,'week_rev'=>0];
$phones = [];
foreach ($orders as $o) {
    $stats['total']++;
    $st = $o['status'] ?? 'pending';
    $stats[$st] = ($stats[$st] ?? 0) + 1;
    $rev = (float)($o['price'] ?? 0);
    $stats['revenue'] += $rev;
    if (str_starts_with($o['date'] ?? '', $today)) $stats['today']++;
    $ot = strtotime($o['date'] ?? '');
    if ($ot && $ot >= $week) $stats['week_rev'] += $rev;
    if ($o['phone'] ?? '') $phones[$o['phone']] = true;
}
$stats['customers'] = count($phones);

// Revenue chart data for multiple periods
function buildChartData($orders, $days) {
    $labels = []; $data = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $labels[] = $days <= 1 ? date('h A', strtotime("-$i hours")) : date('j M', strtotime("-$i days"));
        $data[]   = 0;
    }
    foreach ($orders as $o) {
        if (!($o['date'] ?? '')) continue;
        $key = $days <= 1 ? date('h A', strtotime($o['date'])) : date('j M', strtotime($o['date']));
        $idx = array_search($key, $labels);
        if ($idx !== false) $data[$idx] += (float)($o['price'] ?? 0);
    }
    return ['labels' => $labels, 'data' => $data];
}
$chart7  = buildChartData($orders, 7);
$chart14 = buildChartData($orders, 14);
$chart30 = buildChartData($orders, 30);
// today: filter only today's orders
$todayOrders = array_filter($orders, fn($o) => str_starts_with($o['date'] ?? '', date('d M Y')));
$chartToday  = buildChartData(array_values($todayOrders), 1);
$chartDays = $chart14['labels']; $chartRev = $chart14['data']; // default

// Meta stats
function fetchMeta($token, $pid) {
    if (!$token) return null;
    $events = ['Purchase','ViewContent','AddToCart','Contact'];
    $r = []; $s = strtotime('-30 days'); $e = time();
    foreach ($events as $ev) {
        $url = "https://graph.facebook.com/v19.0/{$pid}/stats?aggregation=event_source&event={$ev}&start_time={$s}&end_time={$e}&access_token=" . urlencode($token);
        $ch = curl_init($url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch); curl_close($ch);
        $d = json_decode($res, true); $cnt = 0;
        if (isset($d['data'])) foreach ($d['data'] as $row) $cnt += (int)($row['count'] ?? 0);
        $r[$ev] = $cnt;
    }
    return $r;
}
$metaStats = fetchMeta(META_ACCESS_TOKEN, META_PIXEL_ID);

function showClientLogin() { ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nasir Oil — Client View</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#2c3e50,#3498db);min-height:100vh;display:flex;align-items:center;justify-content:center;}
.card{background:#fff;border-radius:24px;padding:48px 44px;width:min(400px,94vw);box-shadow:0 32px 80px rgba(0,0,0,.3);text-align:center;}
.logo-wrap{margin-bottom:20px;}
.logo-wrap img{width:70px;height:70px;object-fit:contain;border-radius:12px;}
h1{font-size:1.3rem;font-weight:700;color:#2c3e50;margin-bottom:4px;}
p{color:#999;font-size:.78rem;margin-bottom:28px;}
.divider{height:1px;background:#f0f0f0;margin:0 0 22px;}
input{width:100%;padding:13px 16px;border:1.5px solid #e8e8e8;border-radius:12px;font-family:'Poppins',sans-serif;font-size:.85rem;margin-bottom:14px;outline:none;transition:.2s;}
input:focus{border-color:#3498db;}
button{width:100%;padding:14px;background:#2c3e50;color:#fff;border:none;border-radius:12px;font-family:'Poppins',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;transition:.2s;}
button:hover{background:#3498db;}
.hint{font-size:.68rem;color:#ccc;margin-top:14px;}
</style></head><body>
<div class="card">
  <div class="logo-wrap"><img src="images/logo.png" alt="Nasir Oil Expert"></div>
  <h1>Nasir Oil Expert</h1>
  <p>Client Overview — Read Only</p>
  <div class="divider"></div>
  <?php if (isset($_GET['timeout'])): ?><div style="background:#fdecea;color:#c0392b;border-radius:8px;padding:10px;font-size:.78rem;margin-bottom:14px;">Session expired. Login again.</div><?php endif; ?>
  <form method="post">
    <input type="password" name="pass" placeholder="Enter client password" autofocus>
    <button type="submit">View Dashboard →</button>
  </form>
  <div class="hint">nasiroilexpert.com · Read Only</div>
</div></body></html>
<?php exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Business Dashboard — Nasir Oil Expert</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;background:#f0f4f8;color:#1a1a1a;min-height:100vh;}

/* ── Top nav ── */
.topnav{background:#1a2535;color:#fff;padding:0 28px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100;box-shadow:0 2px 12px rgba(0,0,0,.2);}
.topnav-brand{display:flex;align-items:center;gap:12px;}
.topnav-brand img{width:36px;height:36px;object-fit:contain;border-radius:10px;border:2px solid rgba(255,255,255,.15);}
.topnav-brand-text{display:flex;flex-direction:column;}
.topnav-brand-text strong{font-size:.9rem;font-weight:700;line-height:1.2;}
.topnav-brand-text span{font-size:.6rem;color:rgba(255,255,255,.45);font-weight:500;letter-spacing:.05em;text-transform:uppercase;}
.topnav-right{display:flex;align-items:center;gap:14px;font-size:.75rem;}
.topnav-right a{color:rgba(255,255,255,.5);text-decoration:none;font-weight:500;transition:.15s;} .topnav-right a:hover{color:#fff;}
.readonly-badge{background:rgba(52,152,219,.2);color:#74b9ff;border:1px solid rgba(52,152,219,.35);padding:4px 12px;border-radius:20px;font-size:.63rem;font-weight:700;letter-spacing:.05em;}
.nav-time{color:rgba(255,255,255,.45);font-size:.73rem;}

/* ── Hero banner ── */
.hero{background:linear-gradient(135deg,#1a2535 0%,#2c3e50 60%,#1a3a52 100%);color:#fff;padding:28px 30px 24px;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;right:-40px;top:-40px;width:220px;height:220px;background:rgba(52,152,219,.1);border-radius:50%;}
.hero::after{content:'';position:absolute;right:60px;bottom:-60px;width:140px;height:140px;background:rgba(52,152,219,.07);border-radius:50%;}
.hero-inner{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
.hero-left h1{font-size:1.55rem;font-weight:800;line-height:1.2;margin-bottom:6px;}
.hero-left h1 span{color:#74b9ff;}
.hero-left p{font-size:.78rem;color:rgba(255,255,255,.55);max-width:420px;line-height:1.6;}
.hero-chips{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;}
.hero-chip{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.7);padding:5px 14px;border-radius:20px;font-size:.65rem;font-weight:600;display:flex;align-items:center;gap:5px;}
.hero-right{text-align:right;flex-shrink:0;}
.hero-date{font-size:.72rem;color:rgba(255,255,255,.4);margin-bottom:4px;}
.hero-pending{display:inline-flex;align-items:center;gap:6px;background:rgba(231,76,60,.2);border:1px solid rgba(231,76,60,.35);color:#ff9a8b;padding:7px 16px;border-radius:10px;font-size:.78rem;font-weight:700;}

/* ── Main content ── */
.main{padding:24px 28px;max-width:1400px;}

/* ── Section header ── */
.section-hd{margin-bottom:16px;margin-top:28px;}
.section-hd:first-of-type{margin-top:0;}
.section-hd h2{font-size:1rem;font-weight:700;color:#1a2535;margin-bottom:3px;}
.section-hd p{font-size:.72rem;color:#999;}
.section-divider{height:1px;background:linear-gradient(to right,#dde3ec,transparent);margin-bottom:16px;}

/* ── Stat cards ── */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;margin-bottom:8px;}
.stat-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 14px rgba(0,0,0,.04);padding:20px 22px;border-top:3px solid var(--c,#2c3e50);position:relative;overflow:hidden;}
.stat-card::after{content:'';position:absolute;right:-8px;top:-8px;width:52px;height:52px;background:var(--c,#2c3e50);opacity:.05;border-radius:50%;}
.stat-num{font-size:2rem;font-weight:800;color:var(--c,#2c3e50);line-height:1;}
.stat-label{font-size:.63rem;color:#aaa;text-transform:uppercase;letter-spacing:.08em;margin-top:5px;font-weight:600;}
.stat-sub{font-size:.68rem;color:#bbb;margin-top:3px;}

/* ── Cards ── */
.card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 14px rgba(0,0,0,.04);padding:22px 24px;margin-bottom:16px;}
.card-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;}
.card-hd-left h3{font-size:.9rem;font-weight:700;color:#1a2535;}
.card-hd-left p{font-size:.7rem;color:#aaa;margin-top:2px;}
.card-hd-right{font-size:.73rem;color:#aaa;}

/* ── Two col ── */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:0;}
@media(max-width:768px){
  .two-col{grid-template-columns:1fr;}
  .main{padding:12px 14px;}
  .hero{padding:18px 14px;}
  .topnav{padding:0 14px;}
  .topnav-right .readonly-badge{display:none;}
  .topnav-brand-text strong{font-size:.8rem;}
  .hero h1{font-size:1.5rem;}
  .stat-grid{grid-template-columns:1fr 1fr;gap:10px;}
  .stat-num{font-size:1.7rem;}
  .card{padding:14px;}
  .card-hd{flex-direction:column;gap:8px;align-items:flex-start;}
  .rev-filter-btn{padding:5px 10px;font-size:.65rem;}
  .section-hd h2{font-size:1rem;}
  td,th{padding:7px 9px;font-size:.7rem;}
  .trk-input{width:100px;}
  .trk-form{flex-wrap:wrap;}
}
@media(max-width:480px){
  .stat-grid{grid-template-columns:1fr;}
  .hero h1{font-size:1.2rem;}
  .topnav-right span:not(.readonly-badge){font-size:.62rem;}
}

/* ── Table ── */
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{background:#f6f8fb;color:#2c3e50;padding:10px 14px;font-size:.62rem;text-transform:uppercase;letter-spacing:.07em;text-align:left;font-weight:700;border-bottom:2px solid #e8ecf1;white-space:nowrap;}
td{padding:11px 14px;font-size:.79rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafbfd;}

/* ── Badges ── */
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.6rem;font-weight:700;text-transform:uppercase;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-done{background:#d4edda;color:#155724;}
.badge-cancelled{background:#f8d7da;color:#721c24;}
.badge-ok-stock{background:#e8f5e9;color:#2e7d32;}
.badge-low{background:#ffe0e0;color:#c0392b;}

/* ── Meta cards ── */
.meta-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;}
.meta-card{background:#f6f8fb;border:1px solid #eaecf2;border-radius:14px;padding:18px 16px;text-align:center;}
.meta-icon{font-size:1.6rem;margin-bottom:8px;}
.meta-label{font-size:.62rem;font-weight:700;color:#aaa;text-transform:uppercase;margin-bottom:6px;letter-spacing:.06em;}
.meta-num{font-size:2rem;font-weight:800;}

/* ── Progress bar ── */
.prog-bar{height:6px;background:#edf0f5;border-radius:4px;overflow:hidden;margin:6px 0;}
.prog-fill{height:100%;border-radius:4px;background:var(--c,#2c3e50);}

/* ── Info note ── */
.info-note{background:#eef4ff;border:1px solid #c7daf5;border-radius:10px;padding:12px 16px;font-size:.75rem;color:#2c5282;margin-bottom:20px;display:flex;align-items:center;gap:8px;line-height:1.5;}
</style>
</head>
<body>

<!-- Top Navigation -->
<div class="topnav">
  <div class="topnav-brand">
    <img src="images/logo.png" alt="Nasir Oil Expert">
    <div class="topnav-brand-text">
      <strong>Nasir Oil Expert</strong>
      <span>Business Dashboard</span>
    </div>
  </div>
  <div class="topnav-right">
    <span class="readonly-badge">👁 READ ONLY</span>
    <span class="nav-time"><?= date('d M Y, h:i A') ?></span>
    <a href="?logout=1">Logout →</a>
  </div>
</div>

<!-- Hero Banner -->
<div class="hero">
  <div class="hero-inner">
    <div class="hero-left">
      <h1>Business Performance <span>Dashboard</span></h1>
      <p>Ye dashboard aapko Nasir Oil Expert ka complete business overview deta hai — orders, revenue, Meta ads traffic, aur stock levels sab ek jagah.</p>
      <div class="hero-chips">
        <span class="hero-chip">📦 Orders Overview</span>
        <span class="hero-chip">💰 Revenue Tracking</span>
        <span class="hero-chip">📈 Meta Ads Traffic</span>
        <span class="hero-chip">🏪 Stock Levels</span>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-date"><?= date('l, d F Y') ?></div>
      <?php if ($stats['pending'] > 0): ?>
      <div class="hero-pending">🔴 <?= $stats['pending'] ?> Order<?= $stats['pending']>1?'s':'' ?> Pending</div>
      <?php else: ?>
      <div class="hero-pending" style="background:rgba(39,174,96,.15);border-color:rgba(39,174,96,.3);color:#6fcf97;">✅ Sab Orders Clear</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="main">

<div class="info-note">✅ <span>Orders ka <strong>status</strong> update kar sakte hain aur <strong>tracking number</strong> add kar sakte hain. Baaki settings ke liye admin se rabta karein.</span></div>

<!-- Section 1: Orders Summary -->
<div class="section-hd">
  <h2>📦 Orders Summary</h2>
  <p>Total orders, status breakdown, aur aaj ke orders</p>
</div>
<div class="section-divider"></div>
<div class="stat-grid">
  <div class="stat-card" style="--c:#1a2535">
    <div class="stat-num"><?= $stats['total'] ?></div>
    <div class="stat-label">Total Orders</div>
    <div class="stat-sub">+<?= $stats['today'] ?> aaj</div>
  </div>
  <div class="stat-card" style="--c:#e67e22">
    <div class="stat-num"><?= $stats['pending'] ?></div>
    <div class="stat-label">Pending</div>
    <div class="stat-sub">Process baki hai</div>
  </div>
  <div class="stat-card" style="--c:#27ae60">
    <div class="stat-num"><?= $stats['done'] ?></div>
    <div class="stat-label">Delivered</div>
    <div class="stat-sub">Successfully complete</div>
  </div>
  <div class="stat-card" style="--c:#B8860B">
    <div class="stat-num" style="font-size:1.3rem;">Rs <?= number_format($stats['revenue']) ?></div>
    <div class="stat-label">Total Revenue</div>
    <div class="stat-sub">Rs <?= number_format($stats['week_rev']) ?> is hafte</div>
  </div>
  <div class="stat-card" style="--c:#8e44ad">
    <div class="stat-num"><?= $stats['customers'] ?></div>
    <div class="stat-label">Customers</div>
    <div class="stat-sub">Unique buyers</div>
  </div>
</div>

<!-- Section 2: Revenue Chart -->
<div class="section-hd" style="margin-top:28px;">
  <h2>💰 Revenue Chart</h2>
  <p>Pichle 14 din ki daily revenue — trend dekhein</p>
</div>
<div class="section-divider"></div>
<div class="card">
  <div class="card-hd">
    <div class="card-hd-left">
      <h3>Revenue Chart</h3>
      <p>Period select karein neeche se</p>
    </div>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      <button onclick="switchPeriod('today')" id="btn-today" class="rev-btn">Today</button>
      <button onclick="switchPeriod('7')"     id="btn-7"     class="rev-btn">7 Days</button>
      <button onclick="switchPeriod('14')"    id="btn-14"    class="rev-btn rev-btn-active">14 Days</button>
      <button onclick="switchPeriod('30')"    id="btn-30"    class="rev-btn">30 Days</button>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px;">
    <div style="font-size:.72rem;color:#aaa;">Total: <strong id="chartTotal" style="color:#1a2535;">Rs <?= number_format(array_sum($chart14['data'])) ?></strong></div>
    <div style="font-size:.72rem;color:#aaa;">Period: <strong id="chartPeriodLabel" style="color:#1a2535;">Last 14 Days</strong></div>
  </div>
  <canvas id="revChart" height="70"></canvas>
</div>

<!-- Section 3: Meta Ads -->
<div class="section-hd" style="margin-top:28px;">
  <h2>📈 Meta Ads Performance</h2>
  <p>Facebook/Instagram pixel data — pichle 30 din ki audience activity</p>
</div>
<div class="section-divider"></div>
<?php if ($metaStats):
  $evI = ['Purchase'=>['💳','#B8860B','Purchases'],'ViewContent'=>['👁','#1a2535','Product Views'],'AddToCart'=>['🛒','#27ae60','Add to Cart'],'Contact'=>['📲','#25D366','Contacts']];
  $vc = $metaStats['ViewContent'] ?? 0;
  $ac = $metaStats['AddToCart']   ?? 0;
  $pu = $metaStats['Purchase']    ?? 0;
  $cvr = $vc > 0 ? round($pu/$vc*100,1) : 0;
?>
<div class="card">
  <div class="card-hd">
    <div class="card-hd-left">
      <h3>Pixel Events Breakdown</h3>
      <p>Kitne log aaye, dekha, cart mein daala aur kharida</p>
    </div>
    <div class="card-hd-right" style="text-align:right;">
      Conversion: <strong style="color:#B8860B;"><?= $cvr ?>%</strong> &nbsp;|&nbsp;
      Cart Rate: <strong style="color:#27ae60;"><?= $vc > 0 ? round($ac/$vc*100,1) : 0 ?>%</strong>
    </div>
  </div>
  <div class="meta-grid">
    <?php foreach ($evI as $ev => [$icon,$color,$label]): $cnt = $metaStats[$ev] ?? 0; ?>
    <div class="meta-card">
      <div class="meta-icon"><?= $icon ?></div>
      <div class="meta-label"><?= $label ?></div>
      <div class="meta-num" style="color:<?= $color ?>;"><?= number_format($cnt) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if ($vc > 0): ?>
  <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f2f5;">
    <div style="font-size:.72rem;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px;">Conversion Funnel</div>
    <?php $funnel = ['ViewContent'=>['Product Views',$vc],'AddToCart'=>['Add to Cart',$ac],'Purchase'=>['Purchases',$pu]]; $prev=null;
    foreach ($funnel as [$flbl,$fval]):
      $fpct = $vc > 0 ? round($fval/$vc*100) : 0; ?>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
      <div style="font-size:.78rem;font-weight:600;min-width:110px;color:#555;"><?= $flbl ?></div>
      <div class="prog-bar" style="flex:1;margin:0;"><div class="prog-fill" style="width:<?= $fpct ?>%;--c:#1a2535;"></div></div>
      <div style="font-size:.78rem;color:#888;min-width:60px;text-align:right;"><?= number_format($fval) ?> (<?= $fpct ?>%)</div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:32px;">
  <div style="font-size:2rem;margin-bottom:10px;">📡</div>
  <div style="font-size:.85rem;color:#aaa;">Meta Pixel data load nahi hua. Admin se check karwayein.</div>
</div>
<?php endif; ?>

<!-- Section 4: Recent Orders + Stock -->
<div class="section-hd" style="margin-top:28px;">
  <h2>📦 Orders &amp; 🏪 Inventory</h2>
  <p>Latest orders aur har product ka stock level</p>
</div>
<div class="section-divider"></div>
<div class="two-col">
  <div class="card" style="margin-bottom:0;">
    <div class="card-hd">
      <div class="card-hd-left">
        <h3>Recent Orders</h3>
        <p>Last <?= min(6, count($orders)) ?> orders</p>
      </div>
    </div>
    <?php foreach (array_slice($orders, 0, 6) as $o): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f4f6f9;">
      <div>
        <div style="font-size:.82rem;font-weight:600;color:#1a2535;"><?= clean($o['name']??'') ?></div>
        <div style="font-size:.7rem;color:#999;margin-top:2px;"><?= clean($o['product']??'') ?> &nbsp;·&nbsp; 📍<?= clean($o['city']??'') ?></div>
      </div>
      <div style="text-align:right;flex-shrink:0;margin-left:10px;">
        <div style="font-size:.85rem;font-weight:700;color:#B8860B;">Rs <?= number_format((float)($o['price']??0)) ?></div>
        <span class="badge badge-<?= $o['status']??'pending' ?>"><?= $o['status']??'pending' ?></span>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?><p style="color:#ccc;text-align:center;padding:24px;font-size:.8rem;">Koi orders nahi hain abhi.</p><?php endif; ?>
  </div>

  <div class="card" style="margin-bottom:0;">
    <div class="card-hd">
      <div class="card-hd-left">
        <h3>Stock Levels</h3>
        <p>4 products ka current inventory</p>
      </div>
    </div>
    <?php foreach ($stock as $k => $p):
      $s = (int)$p['stock']; $low = $s <= 10;
      $pct = min(100, $s);
      $color = $low ? '#e74c3c' : ($s <= 25 ? '#e67e22' : '#27ae60');
    ?>
    <div style="margin-bottom:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
        <div>
          <div style="font-size:.82rem;font-weight:600;color:#1a2535;"><?= clean($p['name']) ?></div>
          <div style="font-size:.65rem;color:#aaa;">Rs <?= number_format($p['price']) ?> &nbsp;·&nbsp; <?= $p['sku'] ?></div>
        </div>
        <span class="badge <?= $low?'badge-low':'badge-ok-stock' ?>"><?= $s ?> units<?= $low?' ⚠️':'' ?></span>
      </div>
      <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;--c:<?= $color ?>;"></div></div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($stock)): ?><p style="color:#ccc;text-align:center;padding:24px;font-size:.8rem;">Stock data nahi mila.</p><?php endif; ?>
  </div>
</div>

<!-- Section 5: Full Orders Table -->
<div class="section-hd" style="margin-top:28px;" id="orders-section">
  <h2>📋 All Orders — Manage</h2>
  <p>Status update karein aur tracking number add karein</p>
</div>
<div class="section-divider"></div>
<div class="card">
  <div class="card-hd">
    <div class="card-hd-left">
      <h3>Complete Orders History</h3>
      <p>Total <?= count($orders) ?> orders recorded</p>
    </div>
  </div>
  <style>
  .cl-select{padding:5px 8px;border:1.5px solid #dde3ed;border-radius:8px;font-family:'Poppins',sans-serif;font-size:.72rem;background:#fff;color:#2c3e50;cursor:pointer;}
  .cl-select:focus{outline:none;border-color:#3498db;}
  .trk-form{display:flex;gap:6px;align-items:center;}
  .trk-input{padding:5px 9px;border:1.5px solid #dde3ed;border-radius:8px;font-family:'Poppins',sans-serif;font-size:.72rem;width:130px;color:#2c3e50;}
  .trk-input:focus{outline:none;border-color:#3498db;}
  .trk-btn{padding:5px 10px;background:#1a2535;color:#fff;border:none;border-radius:8px;font-family:'Poppins',sans-serif;font-size:.7rem;cursor:pointer;white-space:nowrap;}
  .trk-btn:hover{background:#3498db;}
  </style>
  <div class="table-wrap">
  <table>
    <thead><tr><th>#</th><th>Date</th><th>Customer</th><th>Phone</th><th>City</th><th>Product</th><th>Price</th><th>Status</th><th>Tracking</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $i => $o): ?>
    <tr>
      <td style="color:#ccc;font-size:.72rem;"><?= $i+1 ?></td>
      <td style="font-size:.7rem;color:#aaa;white-space:nowrap;"><?= clean($o['date']??'') ?></td>
      <td><strong><?= clean($o['name']??'') ?></strong></td>
      <td style="font-size:.75rem;color:#555;white-space:nowrap;">📞 <?= clean($o['phone']??'') ?></td>
      <td style="font-size:.75rem;white-space:nowrap;">📍 <?= clean($o['city']??'—') ?></td>
      <td style="font-weight:600;"><?= clean($o['product']??'') ?></td>
      <td><strong style="color:#B8860B;">Rs <?= number_format((float)($o['price']??0)) ?></strong></td>
      <td>
        <form method="post" action="client-panel.php#orders-section">
          <input type="hidden" name="client_action" value="update_status">
          <input type="hidden" name="id" value="<?= clean($o['id']??'') ?>">
          <select name="status" class="cl-select" onchange="this.form.submit()">
            <option value="pending"  <?= ($o['status']??'pending')==='pending'  ? 'selected':'' ?>>Pending</option>
            <option value="done"     <?= ($o['status']??'')==='done'            ? 'selected':'' ?>>Done</option>
            <option value="cancelled"<?= ($o['status']??'')==='cancelled'       ? 'selected':'' ?>>Cancelled</option>
          </select>
        </form>
      </td>
      <td>
        <form method="post" action="client-panel.php#orders-section" class="trk-form">
          <input type="hidden" name="client_action" value="update_tracking">
          <input type="hidden" name="id" value="<?= clean($o['id']??'') ?>">
          <input type="text" name="tracking" class="trk-input" value="<?= clean($o['tracking']??'') ?>" placeholder="TRK-12345">
          <button type="submit" class="trk-btn">Save</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?><tr><td colspan="9" style="text-align:center;color:#ccc;padding:30px;font-size:.82rem;">Abhi tak koi orders nahi aaye.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

</div><!-- /main -->

<style>
.rev-btn{padding:6px 14px;border-radius:20px;border:1.5px solid #dde3ec;background:#fff;color:#888;font-family:'Poppins',sans-serif;font-size:.68rem;font-weight:600;cursor:pointer;transition:.15s;}
.rev-btn:hover{border-color:#1a2535;color:#1a2535;}
.rev-btn-active{background:#1a2535;color:#fff;border-color:#1a2535;}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
var _periods = {
  'today': { labels: <?= json_encode($chartToday['labels']) ?>, data: <?= json_encode($chartToday['data']) ?>, label: 'Today' },
  '7':     { labels: <?= json_encode($chart7['labels'])     ?>, data: <?= json_encode($chart7['data'])     ?>, label: 'Last 7 Days' },
  '14':    { labels: <?= json_encode($chart14['labels'])    ?>, data: <?= json_encode($chart14['data'])    ?>, label: 'Last 14 Days' },
  '30':    { labels: <?= json_encode($chart30['labels'])    ?>, data: <?= json_encode($chart30['data'])    ?>, label: 'Last 30 Days' }
};

var _chart = new Chart(document.getElementById('revChart'), {
  type: 'bar',
  data: {
    labels: _periods['14'].labels,
    datasets: [{
      data: _periods['14'].data,
      backgroundColor: 'rgba(26,37,53,.1)',
      borderColor: '#1a2535',
      borderWidth: 2,
      borderRadius: 6,
      hoverBackgroundColor: 'rgba(26,37,53,.25)'
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: '#f4f6f9' }, ticks: { font: { family: 'Poppins', size: 10 }, callback: v => 'Rs ' + v.toLocaleString() } },
      x: { grid: { display: false }, ticks: { font: { family: 'Poppins', size: 10 } } }
    }
  }
});

function switchPeriod(p) {
  var d = _periods[p];
  _chart.data.labels = d.labels;
  _chart.data.datasets[0].data = d.data;
  _chart.update();
  var total = d.data.reduce(function(a,b){return a+b;}, 0);
  document.getElementById('chartTotal').textContent = 'Rs ' + total.toLocaleString('en-PK');
  document.getElementById('chartPeriodLabel').textContent = d.label;
  document.querySelectorAll('.rev-btn').forEach(function(b){ b.classList.remove('rev-btn-active'); });
  document.getElementById('btn-' + p).classList.add('rev-btn-active');
}
</script>
</body></html>
