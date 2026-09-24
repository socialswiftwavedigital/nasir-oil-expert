<?php
require_once __DIR__ . '/config.php';

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
function readJson($f) {
    if (!file_exists($f)) return [];
    return json_decode(file_get_contents($f), true) ?: [];
}
function clean($v) { return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8'); }

/* ── Data ────────────────────────────────────────────────── */
$orders = readJson(__DIR__ . '/orders-data.json');
$stock  = readJson(__DIR__ . '/stock.json');

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

// Revenue chart last 14 days
$chartDays = []; $chartRev = [];
for ($i = 13; $i >= 0; $i--) { $chartDays[] = date('j M', strtotime("-$i days")); $chartRev[] = 0; }
foreach ($orders as $o) {
    if ($o['date'] ?? '') {
        $d = date('j M', strtotime($o['date']));
        $idx = array_search($d, $chartDays);
        if ($idx !== false) $chartRev[$idx] += (float)($o['price'] ?? 0);
    }
}

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
<title>Client View — Nasir Oil Expert</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;background:#eef2ef;color:#1a1a1a;min-height:100vh;}

/* Top nav */
.topnav{background:#2c3e50;color:#fff;padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:56px;position:sticky;top:0;z-index:100;}
.topnav-brand{display:flex;align-items:center;gap:10px;}
.topnav-brand img{width:32px;height:32px;object-fit:contain;border-radius:8px;}
.topnav-brand span{font-size:.88rem;font-weight:700;}
.topnav-badge{background:#e74c3c;color:#fff;font-size:.62rem;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:8px;}
.topnav-right{display:flex;align-items:center;gap:14px;font-size:.75rem;}
.topnav-right a{color:rgba(255,255,255,.6);text-decoration:none;} .topnav-right a:hover{color:#fff;}
.readonly-badge{background:rgba(52,152,219,.25);color:#74b9ff;border:1px solid rgba(52,152,219,.3);padding:3px 10px;border-radius:20px;font-size:.65rem;font-weight:700;}

.main{padding:24px;}

/* Stat cards */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:22px;}
.stat-card{background:#fff;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);padding:18px 20px;border-top:3px solid var(--c,#2c3e50);}
.stat-num{font-size:2rem;font-weight:700;color:var(--c,#2c3e50);line-height:1;}
.stat-label{font-size:.62rem;color:#999;text-transform:uppercase;letter-spacing:.08em;margin-top:5px;font-weight:600;}
.stat-sub{font-size:.68rem;color:#bbb;margin-top:3px;}

/* Cards */
.card{background:#fff;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);padding:20px 22px;margin-bottom:16px;}
.section-title{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#999;margin:0 0 14px;}

/* Two col */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;}

/* Table */
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{background:#f4f8f5;color:#2c3e50;padding:9px 12px;font-size:.62rem;text-transform:uppercase;letter-spacing:.07em;text-align:left;font-weight:700;border-bottom:2px solid #e2ede5;white-space:nowrap;}
td{padding:10px 12px;font-size:.79rem;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafffe;}

/* Badge */
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.6rem;font-weight:700;text-transform:uppercase;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-done{background:#d4edda;color:#155724;}
.badge-cancelled{background:#f8d7da;color:#721c24;}
.badge-ok-stock{background:#e8f5e9;color:#2e7d32;}
.badge-low{background:#ffe0e0;color:#c0392b;}

/* Meta cards */
.meta-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:16px;}
.meta-card{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.05);padding:16px;text-align:center;}
.meta-icon{font-size:1.4rem;margin-bottom:6px;}
.meta-label{font-size:.6rem;font-weight:700;color:#aaa;text-transform:uppercase;margin-bottom:4px;}
.meta-num{font-size:1.8rem;font-weight:700;}

/* Prog bar */
.prog-bar{height:5px;background:#e8f0ea;border-radius:4px;overflow:hidden;margin:6px 0;}
.prog-fill{height:100%;border-radius:4px;background:var(--c,#2c3e50);}

@media(max-width:768px){.two-col{grid-template-columns:1fr;}.main{padding:14px;}}
</style>
</head>
<body>

<div class="topnav">
  <div class="topnav-brand">
    <img src="images/logo.png" alt="Logo">
    <span>Nasir Oil Expert</span>
    <span class="topnav-badge"><?= $stats['pending'] ?> pending</span>
  </div>
  <div class="topnav-right">
    <span class="readonly-badge">READ ONLY</span>
    <span><?= date('d M, h:i A') ?></span>
    <a href="?logout=1">Logout</a>
  </div>
</div>

<div class="main">

<!-- Stat Cards -->
<div class="stat-grid">
  <div class="stat-card" style="--c:#2c3e50">
    <div class="stat-num"><?= $stats['total'] ?></div>
    <div class="stat-label">Total Orders</div>
    <div class="stat-sub">+<?= $stats['today'] ?> today</div>
  </div>
  <div class="stat-card" style="--c:#e67e22">
    <div class="stat-num"><?= $stats['pending'] ?></div>
    <div class="stat-label">Pending</div>
    <div class="stat-sub">Processing needed</div>
  </div>
  <div class="stat-card" style="--c:#27ae60">
    <div class="stat-num"><?= $stats['done'] ?></div>
    <div class="stat-label">Delivered</div>
    <div class="stat-sub">Completed</div>
  </div>
  <div class="stat-card" style="--c:#B8860B">
    <div class="stat-num" style="font-size:1.4rem;">Rs <?= number_format($stats['revenue']) ?></div>
    <div class="stat-label">Total Revenue</div>
    <div class="stat-sub">Rs <?= number_format($stats['week_rev']) ?> this week</div>
  </div>
  <div class="stat-card" style="--c:#8e44ad">
    <div class="stat-num"><?= $stats['customers'] ?></div>
    <div class="stat-label">Customers</div>
    <div class="stat-sub">Unique buyers</div>
  </div>
</div>

<!-- Revenue Chart -->
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <div class="section-title" style="margin:0;">Revenue — Last 14 Days</div>
    <span style="font-size:.7rem;color:#aaa;">Rs <?= number_format(array_sum($chartRev)) ?> total</span>
  </div>
  <canvas id="revChart" height="75"></canvas>
</div>

<!-- Meta Traffic -->
<?php if ($metaStats): ?>
<div class="card">
  <div class="section-title">Meta Pixel Traffic — Last 30 Days</div>
  <div class="meta-grid">
    <?php
    $evI = ['Purchase'=>['💳','#B8860B'],'ViewContent'=>['👁','#2c3e50'],'AddToCart'=>['🛒','#27ae60'],'Contact'=>['📲','#25D366']];
    foreach ($metaStats as $ev => $cnt): if (!isset($evI[$ev])) continue; ?>
    <div class="meta-card">
      <div class="meta-icon"><?= $evI[$ev][0] ?></div>
      <div class="meta-label"><?= $ev ?></div>
      <div class="meta-num" style="color:<?= $evI[$ev][1] ?>;"><?= number_format($cnt) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php
  $vc = $metaStats['ViewContent'] ?? 0;
  $ac = $metaStats['AddToCart']   ?? 0;
  $pu = $metaStats['Purchase']    ?? 0;
  $cvr = $vc > 0 ? round($pu/$vc*100,1) : 0;
  ?>
  <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:4px;">
    <div style="font-size:.78rem;color:#555;">Conversion Rate: <strong style="color:#B8860B;"><?= $cvr ?>%</strong> (views → purchases)</div>
    <div style="font-size:.78rem;color:#555;">Cart Rate: <strong style="color:#27ae60;"><?= $vc > 0 ? round($ac/$vc*100,1) : 0 ?>%</strong></div>
  </div>
</div>
<?php endif; ?>

<!-- Two col: recent orders + stock -->
<div class="two-col">
  <div class="card">
    <div class="section-title">Recent Orders</div>
    <?php foreach (array_slice($orders, 0, 6) as $o): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f5f5f5;">
      <div>
        <div style="font-size:.8rem;font-weight:600;"><?= clean($o['name']??'') ?></div>
        <div style="font-size:.7rem;color:#888;"><?= clean($o['product']??'') ?> · <?= clean($o['city']??'') ?></div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:.82rem;font-weight:700;color:#B8860B;">Rs <?= number_format((float)($o['price']??0)) ?></div>
        <span class="badge badge-<?= $o['status']??'pending' ?>"><?= $o['status']??'pending' ?></span>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?><p style="color:#ccc;text-align:center;padding:20px;font-size:.8rem;">No orders yet.</p><?php endif; ?>
  </div>

  <div class="card">
    <div class="section-title">Stock Levels</div>
    <?php foreach ($stock as $k => $p):
      $s = (int)$p['stock']; $low = $s <= 10;
      $pct = min(100, $s);
      $color = $low ? '#e74c3c' : ($s <= 25 ? '#e67e22' : '#27ae60');
    ?>
    <div style="margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px;">
        <span style="font-size:.8rem;font-weight:600;"><?= clean($p['name']) ?></span>
        <span class="badge <?= $low?'badge-low':'badge-ok-stock' ?>"><?= $s ?><?= $low?' ⚠️':'' ?></span>
      </div>
      <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;--c:<?= $color ?>;"></div></div>
      <div style="font-size:.65rem;color:#aaa;">Rs <?= number_format($p['price']) ?> · SKU: <?= $p['sku'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Full Orders Table -->
<div class="card">
  <div class="section-title">All Orders — View Only</div>
  <div class="table-wrap">
  <table>
    <thead><tr><th>#</th><th>Date</th><th>Customer</th><th>Phone</th><th>City</th><th>Product</th><th>Price</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $i => $o): ?>
    <tr>
      <td style="color:#ccc;"><?= $i+1 ?></td>
      <td style="font-size:.7rem;color:#aaa;white-space:nowrap;"><?= clean($o['date']??'') ?></td>
      <td><strong><?= clean($o['name']??'') ?></strong></td>
      <td style="font-size:.75rem;color:#555;">📞 <?= clean($o['phone']??'') ?></td>
      <td style="font-size:.75rem;">📍 <?= clean($o['city']??'—') ?></td>
      <td style="font-weight:600;"><?= clean($o['product']??'') ?></td>
      <td><strong>Rs <?= number_format((float)($o['price']??0)) ?></strong></td>
      <td><span class="badge badge-<?= $o['status']??'pending' ?>"><?= $o['status']??'pending' ?></span></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?><tr><td colspan="8" style="text-align:center;color:#ccc;padding:30px;">No orders yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

</div><!-- /main -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartDays) ?>,
    datasets: [{
      data: <?= json_encode($chartRev) ?>,
      backgroundColor: 'rgba(44,62,80,.12)',
      borderColor: '#2c3e50',
      borderWidth: 2,
      borderRadius: 5,
      hoverBackgroundColor: 'rgba(44,62,80,.25)'
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
</script>
</body></html>
