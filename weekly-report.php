<?php
require_once __DIR__ . '/config.php';

// Auth: admin password via GET or session
session_start();
if (!($_SESSION['noe_admin'] ?? false) && ($_GET['key'] ?? '') !== ADMIN_PASS) {
    http_response_code(403); die('Unauthorized');
}

function readJson($f) {
    if (!file_exists($f)) return [];
    return json_decode(file_get_contents($f), true) ?: [];
}

$orders = readJson(__DIR__ . '/orders-data.json');
$stock  = readJson(__DIR__ . '/stock.json');

// Date range: last 7 days
$from    = strtotime('-7 days');
$fromStr = date('d M Y', $from);
$toStr   = date('d M Y');

$weekly = array_filter($orders, function($o) use ($from) {
    $t = strtotime($o['date'] ?? '');
    return $t && $t >= $from;
});
$weekly = array_values($weekly);

// Stats
$revenue   = array_sum(array_column($weekly, 'price'));
$total     = count($weekly);
$pending   = count(array_filter($weekly, fn($o) => ($o['status']??'pending') === 'pending'));
$done      = count(array_filter($weekly, fn($o) => ($o['status']??'') === 'done'));
$cancelled = count(array_filter($weekly, fn($o) => ($o['status']??'') === 'cancelled'));
$duplicate = count(array_filter($weekly, fn($o) => !empty($o['duplicate'])));

// Product breakdown
$prodMap = [];
foreach ($weekly as $o) {
    $p = $o['product'] ?? 'Unknown';
    $prodMap[$p] = ($prodMap[$p] ?? 0) + 1;
}
arsort($prodMap);

// City breakdown
$cityMap = [];
foreach ($weekly as $o) {
    $c = $o['city'] ?? 'Unknown';
    if ($c) $cityMap[$c] = ($cityMap[$c] ?? 0) + 1;
}
arsort($cityMap);

// Top customers (by order count this week)
$custMap = [];
foreach ($weekly as $o) {
    $ph = $o['phone'] ?? '';
    if (!$ph) continue;
    if (!isset($custMap[$ph])) $custMap[$ph] = ['name'=>$o['name']??'','count'=>0,'spent'=>0];
    $custMap[$ph]['count']++;
    $custMap[$ph]['spent'] += (float)($o['price']??0);
}
uasort($custMap, fn($a,$b) => $b['count'] - $a['count']);

/* ── Send email if requested ── */
$emailSent = false;
if (($_GET['send'] ?? '') === '1') {
    $emailBody  = "WEEKLY SALES REPORT — Nasir Oil Expert\n";
    $emailBody .= "Period: $fromStr to $toStr\n\n";
    $emailBody .= "━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $emailBody .= "ORDERS SUMMARY\n";
    $emailBody .= "━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $emailBody .= "Total Orders   : $total\n";
    $emailBody .= "Pending        : $pending\n";
    $emailBody .= "Delivered      : $done\n";
    $emailBody .= "Cancelled      : $cancelled\n";
    $emailBody .= "Duplicates     : $duplicate\n";
    $emailBody .= "Total Revenue  : Rs " . number_format($revenue) . "\n\n";
    $emailBody .= "TOP PRODUCTS\n";
    foreach (array_slice($prodMap, 0, 5, true) as $p => $c) {
        $emailBody .= "  $p : $c orders\n";
    }
    $emailBody .= "\nTOP CITIES\n";
    foreach (array_slice($cityMap, 0, 5, true) as $city => $c) {
        $emailBody .= "  $city : $c orders\n";
    }
    $emailBody .= "\n━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $emailBody .= "STOCK STATUS\n━━━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($stock as $s) {
        $low = (int)$s['stock'] <= 10 ? ' ⚠️ LOW' : '';
        $emailBody .= $s['name'] . ' : ' . $s['stock'] . ' units' . $low . "\n";
    }
    $emailBody .= "\nnasiroilexpert.com/admin-dashboard.php\n";

    $emailSent = mail(
        'info@nasiroilexpert.com',
        '[Weekly Report] Nasir Oil Expert — ' . $fromStr . ' to ' . $toStr,
        $emailBody,
        'From: Nasir Oil Expert <info@nasiroilexpert.com>'
    );
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Weekly Report — Nasir Oil Expert</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;background:#eef2ef;padding:24px;}
.wrap{max-width:780px;margin:0 auto;}
.header{background:#1B4332;color:#fff;border-radius:14px;padding:24px 28px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;}
.header h1{font-size:1.2rem;font-weight:700;}
.header p{font-size:.75rem;color:rgba(255,255,255,.65);margin-top:3px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-family:'Poppins',sans-serif;font-size:.75rem;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:.15s;}
.btn-white{background:#fff;color:#1B4332;} .btn-white:hover{background:#f0f0f0;}
.btn-green{background:#2D6A4F;color:#fff;} .btn-green:hover{background:#1B4332;}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:18px;}
.stat{background:#fff;border-radius:12px;padding:18px;border-top:3px solid var(--c,#1B4332);box-shadow:0 1px 4px rgba(0,0,0,.05);}
.stat .num{font-size:1.8rem;font-weight:700;color:var(--c,#1B4332);line-height:1;}
.stat .lbl{font-size:.62rem;color:#999;text-transform:uppercase;letter-spacing:.07em;margin-top:5px;font-weight:600;}
.card{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.05);padding:20px;margin-bottom:14px;}
.section-title{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#999;margin-bottom:14px;}
table{width:100%;border-collapse:collapse;}
th{background:#f4f8f5;color:#1B4332;padding:9px 12px;font-size:.62rem;text-transform:uppercase;letter-spacing:.07em;text-align:left;font-weight:700;border-bottom:2px solid #e2ede5;}
td{padding:10px 12px;font-size:.8rem;border-bottom:1px solid #f5f5f5;}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:.6rem;font-weight:700;text-transform:uppercase;}
.badge-low{background:#ffe0e0;color:#c0392b;} .badge-ok{background:#d4edda;color:#155724;}
.alert-ok{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:11px 14px;border-radius:8px;font-size:.8rem;margin-bottom:14px;}
</style>
</head><body>
<div class="wrap">
<div class="header">
  <div>
    <h1>📊 Weekly Sales Report</h1>
    <p><?= $fromStr ?> — <?= $toStr ?></p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="?key=<?= urlencode(ADMIN_PASS) ?>&send=1" class="btn btn-white">📧 Email Report</a>
    <a href="admin-dashboard.php" class="btn btn-green">← Admin Panel</a>
  </div>
</div>

<?php if ($emailSent): ?>
<div class="alert-ok">✅ Report emailed to info@nasiroilexpert.com</div>
<?php endif; ?>

<div class="stat-grid">
  <div class="stat" style="--c:#1B4332"><div class="num"><?= $total ?></div><div class="lbl">Total Orders</div></div>
  <div class="stat" style="--c:#e67e22"><div class="num"><?= $pending ?></div><div class="lbl">Pending</div></div>
  <div class="stat" style="--c:#27ae60"><div class="num"><?= $done ?></div><div class="lbl">Delivered</div></div>
  <div class="stat" style="--c:#e74c3c"><div class="num"><?= $cancelled ?></div><div class="lbl">Cancelled</div></div>
  <div class="stat" style="--c:#B8860B"><div class="num" style="font-size:1.2rem;">Rs <?= number_format($revenue) ?></div><div class="lbl">Revenue</div></div>
  <div class="stat" style="--c:#f39c12"><div class="num"><?= $duplicate ?></div><div class="lbl">Duplicates</div></div>
</div>

<div class="card">
  <div class="section-title">Product Performance</div>
  <table>
    <thead><tr><th>Product</th><th>Orders</th><th>% Share</th></tr></thead>
    <tbody>
    <?php foreach ($prodMap as $p => $c): ?>
    <tr>
      <td><?= htmlspecialchars($p) ?></td>
      <td><strong><?= $c ?></strong></td>
      <td><?= $total > 0 ? round($c/$total*100) : 0 ?>%</td>
    </tr>
    <?php endforeach; if (empty($prodMap)): ?>
    <tr><td colspan="3" style="text-align:center;color:#ccc;padding:20px;">No orders this week.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <div class="section-title">Top Cities</div>
  <table>
    <thead><tr><th>City</th><th>Orders</th></tr></thead>
    <tbody>
    <?php foreach (array_slice($cityMap, 0, 10, true) as $city => $c): ?>
    <tr><td><?= htmlspecialchars($city) ?></td><td><strong><?= $c ?></strong></td></tr>
    <?php endforeach; if (empty($cityMap)): ?>
    <tr><td colspan="2" style="text-align:center;color:#ccc;padding:20px;">No data.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <div class="section-title">Stock Status</div>
  <table>
    <thead><tr><th>Product</th><th>SKU</th><th>Stock</th><th>Price</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($stock as $s):
      $low = (int)$s['stock'] <= 10;
    ?>
    <tr>
      <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
      <td style="color:#aaa;font-size:.72rem;"><?= $s['sku'] ?></td>
      <td><strong style="color:<?= $low?'#e74c3c':'#1B4332' ?>;"><?= $s['stock'] ?></strong></td>
      <td>Rs <?= number_format($s['price']) ?></td>
      <td><span class="badge <?= $low?'badge-low':'badge-ok' ?>"><?= $low?'LOW':'OK' ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (!empty($weekly)): ?>
<div class="card">
  <div class="section-title">This Week's Orders</div>
  <table>
    <thead><tr><th>Date</th><th>Customer</th><th>Product</th><th>City</th><th>Price</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($weekly as $o): ?>
    <tr>
      <td style="font-size:.72rem;color:#aaa;"><?= htmlspecialchars($o['date']??'') ?></td>
      <td>
        <?= htmlspecialchars($o['name']??'') ?>
        <?php if (!empty($o['duplicate'])): ?><span class="badge" style="background:#fff3cd;color:#856404;margin-left:4px;">DUP</span><?php endif; ?>
        <br><span style="font-size:.7rem;color:#888;">📞 <?= htmlspecialchars($o['phone']??'') ?></span>
      </td>
      <td><?= htmlspecialchars($o['product']??'') ?></td>
      <td style="font-size:.75rem;">📍 <?= htmlspecialchars($o['city']??'') ?></td>
      <td><strong>Rs <?= number_format((float)($o['price']??0)) ?></strong></td>
      <td><span class="badge badge-<?= $o['status']??'pending' ?>"><?= $o['status']??'pending' ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

</div></body></html>
