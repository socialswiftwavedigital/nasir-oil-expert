<?php
// Password protection — change this to a strong password
define('ADMIN_PASS', 'nasir@admin2024');

$key = $_GET['key'] ?? '';
if ($key !== ADMIN_PASS) {
    http_response_code(401);
    die('<!DOCTYPE html><html><head><title>Login</title><style>
body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f5f5f5;}
form{background:#fff;padding:36px 40px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.1);text-align:center;}
h2{margin:0 0 20px;color:#1B4332;}
input[type=password]{width:100%;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:.9rem;margin-bottom:14px;box-sizing:border-box;}
button{width:100%;padding:11px;background:#1A1A1A;color:#fff;border:none;border-radius:8px;font-size:.88rem;font-weight:700;cursor:pointer;}
</style></head><body><form method="get">
<h2>Orders Admin</h2>
<input type="password" name="key" placeholder="Enter password">
<button type="submit">Login</button>
</form></body></html>');
}

$file   = __DIR__ . '/orders-data.json';
$orders = [];
if (file_exists($file)) {
    $raw    = file_get_contents($file);
    $orders = json_decode($raw, true) ?: [];
}

$total   = count($orders);
$pending = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$done    = $total - $pending;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $uid  = $_POST['update_id'];
    $nst  = $_POST['new_status'] ?? 'pending';
    foreach ($orders as &$ord) {
        if ($ord['id'] === $uid) { $ord['status'] = $nst; break; }
    }
    unset($ord);
    file_put_contents($file, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="orders-' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF"; // BOM for Excel
    echo "Order ID,Date,Name,Phone,City,Address,Product,Price,Status,Source\n";
    foreach ($orders as $o) {
        echo '"' . implode('","', [
            $o['id'], $o['date'], $o['name'], $o['phone'], $o['city'],
            $o['address'], $o['product'], $o['price'] ?? '', $o['status'], $o['source'] ?? ''
        ]) . '"' . "\n";
    }
    exit;
}

// Filter
$filterStatus = $_GET['status'] ?? 'all';
$filtered = $filterStatus === 'all' ? $orders : array_filter($orders, fn($o) => $o['status'] === $filterStatus);
$filtered = array_values($filtered);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nasir Oil — Orders Admin</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',sans-serif;background:#f0f4f8;color:#1a1a1a;min-height:100vh;}
.header{background:#1B4332;color:#fff;padding:18px 32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;}
.header h1{font-size:1.1rem;font-weight:700;letter-spacing:.04em;}
.header a{color:#c8e8d4;font-size:.78rem;text-decoration:none;}
.stats{display:flex;gap:16px;padding:24px 32px 8px;flex-wrap:wrap;}
.stat-card{background:#fff;border-radius:12px;padding:18px 24px;flex:1;min-width:120px;box-shadow:0 1px 6px rgba(0,0,0,.07);}
.stat-card .num{font-size:2rem;font-weight:700;color:#1B4332;}
.stat-card .lbl{font-size:.72rem;color:#666;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;}
.controls{display:flex;gap:10px;padding:16px 32px;flex-wrap:wrap;align-items:center;}
.filter-btn{padding:7px 18px;border-radius:20px;border:1.5px solid #ddd;background:#fff;font-size:.78rem;cursor:pointer;font-weight:600;transition:.15s;}
.filter-btn.active{background:#1B4332;color:#fff;border-color:#1B4332;}
.export-btn{margin-left:auto;padding:8px 20px;background:#1A1A1A;color:#fff;border:none;border-radius:8px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.table-wrap{padding:0 32px 40px;overflow-x:auto;}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.07);}
th{background:#1B4332;color:#fff;padding:12px 14px;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;text-align:left;font-weight:600;}
td{padding:12px 14px;font-size:.82rem;border-bottom:1px solid #f0f0f0;vertical-align:top;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#f9fbf9;}
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:.68rem;font-weight:700;text-transform:uppercase;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-done{background:#d4edda;color:#155724;}
.badge-cancelled{background:#f8d7da;color:#721c24;}
.source-cell{max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#666;font-size:.74rem;}
form.status-form{display:inline;}
select.status-sel{padding:4px 8px;border:1px solid #ddd;border-radius:6px;font-size:.76rem;margin-right:6px;}
button.status-save{padding:4px 10px;background:#1B4332;color:#fff;border:none;border-radius:6px;font-size:.74rem;cursor:pointer;}
.empty{text-align:center;padding:60px 20px;color:#aaa;font-size:.9rem;}
@media(max-width:600px){.stats,.controls,.table-wrap{padding-left:16px;padding-right:16px;}}
</style>
</head>
<body>

<div class="header">
  <h1>&#128722; Nasir Oil — Order Tracker</h1>
  <span><?= date('d M Y') ?></span>
</div>

<div class="stats">
  <div class="stat-card"><div class="num"><?= $total ?></div><div class="lbl">Total Orders</div></div>
  <div class="stat-card"><div class="num"><?= $pending ?></div><div class="lbl">Pending</div></div>
  <div class="stat-card"><div class="num"><?= $done ?></div><div class="lbl">Completed</div></div>
</div>

<div class="controls">
  <a href="?key=<?= ADMIN_PASS ?>&status=all"    class="filter-btn <?= $filterStatus==='all'?'active':''       ?>">All (<?= $total ?>)</a>
  <a href="?key=<?= ADMIN_PASS ?>&status=pending"  class="filter-btn <?= $filterStatus==='pending'?'active':''   ?>">Pending (<?= $pending ?>)</a>
  <a href="?key=<?= ADMIN_PASS ?>&status=done"     class="filter-btn <?= $filterStatus==='done'?'active':''      ?>">Done (<?= $done ?>)</a>
  <a href="?key=<?= ADMIN_PASS ?>&export=1"        class="export-btn">&#8595; Export CSV</a>
</div>

<div class="table-wrap">
<?php if (empty($filtered)): ?>
  <div class="empty">No orders found.</div>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th>#</th><th>Order ID</th><th>Date</th><th>Customer</th>
      <th>Product</th><th>Price</th><th>Status</th><th>Source</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($filtered as $i => $o): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><strong><?= htmlspecialchars($o['id']) ?></strong></td>
    <td><?= htmlspecialchars($o['date']) ?></td>
    <td>
      <strong><?= htmlspecialchars($o['name']) ?></strong><br>
      <span style="color:#1B4332;">&#128222; <?= htmlspecialchars($o['phone']) ?></span><br>
      <span style="color:#666;font-size:.74rem;"><?= htmlspecialchars($o['city']) ?> — <?= htmlspecialchars($o['address']) ?></span>
    </td>
    <td><?= htmlspecialchars($o['product']) ?></td>
    <td><strong>Rs <?= htmlspecialchars($o['price'] ?? '') ?></strong></td>
    <td>
      <form class="status-form" method="post" action="?key=<?= ADMIN_PASS ?>">
        <input type="hidden" name="update_id" value="<?= htmlspecialchars($o['id']) ?>">
        <select class="status-sel" name="new_status">
          <option <?= $o['status']==='pending'?'selected':'' ?> value="pending">Pending</option>
          <option <?= $o['status']==='done'?'selected':'' ?> value="done">Done</option>
          <option <?= $o['status']==='cancelled'?'selected':'' ?> value="cancelled">Cancelled</option>
        </select>
        <button class="status-save" type="submit">Save</button>
      </form>
    </td>
    <td class="source-cell" title="<?= htmlspecialchars($o['source'] ?? '') ?>"><?= htmlspecialchars($o['source'] ?? 'direct') ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</div>

</body>
</html>
