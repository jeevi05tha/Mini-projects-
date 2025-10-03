<?php
session_start();
if (empty($_SESSION['official_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db_connect.php';

function getTableCount(mysqli $conn, string $table): ?int {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM `" . $conn->real_escape_string($table) . "`");
    if ($result === false) {
        return null;
    }
    $row = $result->fetch_assoc();
    return isset($row['cnt']) ? (int)$row['cnt'] : 0;
}

$cityCount = getTableCount($conn, 'city');
$availCount = getTableCount($conn, 'availabitities');
$txCount = getTableCount($conn, 'transactions');

$recentTx = null;
if ($txCount !== null && $txCount > 0) {
    $recentTx = $conn->query("SELECT cust_name, city_name, rate, a_id FROM transactions ORDER BY a_id DESC LIMIT 10");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Official Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
  <nav class="navbar navbar-light bg-light shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">Real Estate Explorer</a>
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted small">Signed in as <?php echo htmlspecialchars($_SESSION['official_username'] ?? 'official'); ?></span>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <h1 class="h3 mb-4">Official Dashboard</h1>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted">Cities</div>
                <div class="display-6"><?php echo $cityCount === null ? '—' : number_format($cityCount); ?></div>
              </div>
              <span class="badge bg-primary">Total</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted">Availabilities</div>
                <div class="display-6"><?php echo $availCount === null ? '—' : number_format($availCount); ?></div>
              </div>
              <span class="badge bg-success">Total</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted">Transactions</div>
                <div class="display-6"><?php echo $txCount === null ? '—' : number_format($txCount); ?></div>
              </div>
              <span class="badge bg-secondary">Total</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h5 mb-3">Recent Transactions</h2>
        <?php if ($recentTx && $recentTx instanceof mysqli_result && $recentTx->num_rows > 0): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>Customer</th>
                  <th>City</th>
                  <th>Rate (₹)</th>
                  <th>Property ID</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $recentTx->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['cust_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['city_name']); ?></td>
                    <td><?php echo number_format((int)$row['rate']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['a_id']); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">No transactions found or table missing.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
