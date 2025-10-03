<?php
include 'db.php';

// ---- SUMMARY DATA ----
$todayQuery = "SELECT SUM(amount) as total FROM orders WHERE DATE(order_date) = CURDATE()";
$weekQuery = "SELECT SUM(amount) as total FROM orders WHERE YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)";
$monthQuery = "SELECT SUM(amount) as total FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())";
$totalQuery = "SELECT SUM(amount) as total FROM orders";

$today = $conn->query($todayQuery)->fetch_assoc()['total'] ?? 0;
$week = $conn->query($weekQuery)->fetch_assoc()['total'] ?? 0;
$month = $conn->query($monthQuery)->fetch_assoc()['total'] ?? 0;
$total = $conn->query($totalQuery)->fetch_assoc()['total'] ?? 0;

// ---- SELECT VIEW ----
$view = $_GET['view'] ?? 'daily';
if ($view === 'daily') {
    $query = "SELECT DATE(order_date) as label, SUM(amount) as total 
              FROM orders GROUP BY DATE(order_date) ORDER BY order_date DESC LIMIT 30";
} elseif ($view === 'weekly') {
    $query = "SELECT YEARWEEK(order_date,1) as label, SUM(amount) as total 
              FROM orders GROUP BY YEARWEEK(order_date,1) ORDER BY order_date DESC LIMIT 12";
} else {
    $query = "SELECT DATE_FORMAT(order_date, '%Y-%m') as label, SUM(amount) as total 
              FROM orders GROUP BY DATE_FORMAT(order_date, '%Y-%m') ORDER BY order_date DESC LIMIT 12";
}
$result = $conn->query($query);
$labels = []; $totals = [];
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['label'];
    $totals[] = $row['total'];
}

// ---- CATEGORY PIE DATA (example: PDFs, Notes, Tests) ----
$catQuery = "SELECT category, SUM(amount) as total FROM orders JOIN pdfs ON orders.pdf_id = pdfs.id GROUP BY category";
$catRes = $conn->query($catQuery);
$catLabels = []; $catTotals = [];
if ($catRes) {
    while ($row = $catRes->fetch_assoc()) {
        $catLabels[] = $row['category'];
        $catTotals[] = $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Revenue Dashboard - PrepSaathi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background:#f5f7fa; }
    .card { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
    .nav-tabs .nav-link.active { background:#007bff; color:white !important; }
  </style>
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4 fw-bold text-center">💰 Revenue Dashboard - PrepSaathi</h2>

  <!-- Summary Cards -->
  <div class="row mb-4 text-center">
    <div class="col-md-3"><div class="card p-3"><h6>Today</h6><h4>₹<?= number_format($today,2) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><h6>This Week</h6><h4>₹<?= number_format($week,2) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><h6>This Month</h6><h4>₹<?= number_format($month,2) ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><h6>Total</h6><h4>₹<?= number_format($total,2) ?></h4></div></div>
  </div>

  <!-- Tabs -->
  <ul class="nav nav-tabs mb-3 justify-content-center">
    <li class="nav-item"><a class="nav-link <?= $view==='daily'?'active':'' ?>" href="?view=daily">Daily</a></li>
    <li class="nav-item"><a class="nav-link <?= $view==='weekly'?'active':'' ?>" href="?view=weekly">Weekly</a></li>
    <li class="nav-item"><a class="nav-link <?= $view==='monthly'?'active':'' ?>" href="?view=monthly">Monthly</a></li>
  </ul>

  <div class="row">
    <!-- Line Chart -->
    <div class="col-md-8 mb-4">
      <div class="card p-3">
        <h5 class="mb-3">Revenue Trend (<?= ucfirst($view) ?>)</h5>
        <canvas id="lineChart"></canvas>
      </div>
    </div>
    <!-- Pie Chart -->
    <div class="col-md-4 mb-4">
      <div class="card p-3">
        <h5 class="mb-3">Revenue by Category</h5>
        <canvas id="pieChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Transactions Table -->
  <div class="card p-3 mt-4">
    <h5 class="mb-3">Transaction Records</h5>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead class="table-primary"><tr><th>Period</th><th>Revenue (₹)</th></tr></thead>
        <tbody>
          <?php foreach ($labels as $i=>$label): ?>
            <tr><td><?= $label ?></td><td><?= number_format($totals[$i],2) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const lineCtx = document.getElementById('lineChart').getContext('2d');
new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_reverse($labels)) ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode(array_reverse($totals)) ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0,123,255,0.2)',
            fill: true,
            tension: 0.3
        }]
    }
});

const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($catLabels) ?>,
        datasets: [{
            data: <?= json_encode($catTotals) ?>,
            backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1']
        }]
    }
});
</script>
</body>
</html>
