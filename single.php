<?php
session_start();

// ===== Inline Database Connection (Single-file) =====
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "dbms";

$databaseConnection = @new mysqli($servername, $username, $password, $dbname);
$databaseError = $databaseConnection->connect_error ? $databaseConnection->connect_error : null;

function isOfficialLoggedIn(): bool {
    return !empty($_SESSION['official_logged_in']);
}

function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

// ===== Action Handlers =====
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    redirect('single.php');
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = trim($_POST['username'] ?? '');
    $inputPassword = trim($_POST['password'] ?? '');

    $validUsername = 'official';
    $validPassword = 'official123';

    if (hash_equals($validUsername, $inputUsername) && hash_equals($validPassword, $inputPassword)) {
        $_SESSION['official_logged_in'] = true;
        $_SESSION['official_username'] = $inputUsername;
        redirect('single.php?page=dashboard');
    } else {
        $_SESSION['login_error'] = 'Invalid username or password.';
        redirect('single.php?page=login');
    }
}

if ($action === 'transaction' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($databaseError) {
        $_SESSION['tx_error'] = 'Database unavailable.';
        redirect('single.php?page=home');
    }

    $customerName = trim($_POST['cust_name'] ?? '');
    $cityName     = trim($_POST['city_name'] ?? '');
    $rateAmount   = (int)($_POST['rate'] ?? 0);
    $availabilityId = (int)($_POST['a_id'] ?? 0);

    if ($customerName === '' || $cityName === '' || $rateAmount <= 0 || $availabilityId <= 0) {
        $_SESSION['tx_error'] = 'Please provide all required fields correctly.';
        redirect('single.php?page=home');
    }

    $stmtCheck = $databaseConnection->prepare("SELECT a_id FROM availabitities WHERE a_id = ? AND available = 1");
    if ($stmtCheck) {
        $stmtCheck->bind_param('i', $availabilityId);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        $isAvailable = $result && $result->num_rows > 0;
        $stmtCheck->close();

        if (!$isAvailable) {
            $_SESSION['tx_error'] = 'This property is already sold!';
            redirect('single.php?page=home');
        }
    } else {
        $_SESSION['tx_error'] = 'Failed to validate availability.';
        redirect('single.php?page=home');
    }

    $stmtInsert = $databaseConnection->prepare("INSERT INTO transactions (cust_name, city_name, rate, a_id) VALUES (?, ?, ?, ?)");
    if (!$stmtInsert) {
        $_SESSION['tx_error'] = 'Failed to create transaction.';
        redirect('single.php?page=home');
    }
    $stmtInsert->bind_param('ssii', $customerName, $cityName, $rateAmount, $availabilityId);
    $okInsert = $stmtInsert->execute();
    $stmtInsert->close();

    if (!$okInsert) {
        $_SESSION['tx_error'] = 'Failed to insert transaction.';
        redirect('single.php?page=home');
    }

    $stmtUpdate = $databaseConnection->prepare("UPDATE availabitities SET available = 0 WHERE a_id = ?");
    if ($stmtUpdate) {
        $stmtUpdate->bind_param('i', $availabilityId);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    $_SESSION['tx_success'] = 'Transaction successful!';
    redirect('single.php?page=home');
}

if ($action === 'add_city' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($databaseError) redirect('single.php?page=dashboard');
    $cityName = $databaseConnection->real_escape_string(trim($_POST['city_name'] ?? ''));
    $numAvail = (int)($_POST['no_of_availabilities'] ?? 0);
    $databaseConnection->query("INSERT INTO city (city_name, no_of_availabilities) VALUES ('{$cityName}', {$numAvail})");
    $_SESSION['admin_notice'] = 'City added successfully.';
    redirect('single.php?page=dashboard');
}

if ($action === 'add_availability' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($databaseError) redirect('single.php?page=dashboard');
    $cityId   = (int)($_POST['city_id'] ?? 0);
    $type     = $databaseConnection->real_escape_string(trim($_POST['type'] ?? ''));
    $mode     = $databaseConnection->real_escape_string(trim($_POST['mode'] ?? ''));
    $rate     = (float)($_POST['rate'] ?? 0);
    $available = isset($_POST['available']) ? 1 : 0;

    if ($cityId > 0 && $type !== '' && $mode !== '' && $rate >= 0) {
        $databaseConnection->query("INSERT INTO availabitities (id, type, mode, rate, available) VALUES ({$cityId}, '{$type}', '{$mode}', {$rate}, {$available})");
        $databaseConnection->query("UPDATE city SET no_of_availabilities = no_of_availabilities + 1 WHERE id = {$cityId}");
        $_SESSION['admin_notice'] = 'Availability added successfully.';
    } else {
        $_SESSION['admin_notice'] = 'Invalid availability details.';
    }
    redirect('single.php?page=dashboard');
}

// ===== Routing =====
$page = $_GET['page'] ?? 'home';

if ($page === 'mca') {
    $targetUrl = 'https://www.mca.gov.in/content/mca/global/en/additional-services/econsultation.html';
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $targetUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $html   = curl_exec($curl);
    $err    = curl_error($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($html === false || $status >= 400) {
        http_response_code(502);
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>MCA eConsultation</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet' /></head><body><div class='container py-5'><h1 class='h3'>MCA eConsultation</h1><div class='alert alert-danger'>Unable to fetch the MCA page right now. Please try again later.</div>";
        if (!empty($err)) {
            echo "<pre class='small text-muted'>Error: " . htmlspecialchars($err) . "</pre>";
        }
        echo "<p><a href='single.php' class='btn btn-secondary'>Back to Home</a></p></div></body></html>";
        exit;
    }

    // Drop inline CSP, add <base> for relative URL resolution
    $html = preg_replace('/<meta[^>]+http-equiv=["\']Content-Security-Policy["\'][^>]*>/i', '', $html);
    if (stripos($html, '<base ') === false) {
        $html = preg_replace('/<head[^>]*>/i', '$0' . "\n" . '<base href="https://www.mca.gov.in/">', $html, 1, $count);
        if (empty($count)) {
            $html = '<base href="https://www.mca.gov.in/">' . $html;
        }
    }

    echo $html;
    exit;
}

// ===== Shared Layout (for home/login/dashboard) =====
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Real Estate Explorer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background: linear-gradient(to right, #dfe9f3, #ffffff); font-family: 'Segoe UI', sans-serif; }
    .hero { background: url('background.webp') no-repeat center center/cover; height: 60vh; display: flex; align-items: center; justify-content: center; color: white; text-shadow: 2px 2px 6px #000; }
    .hero h1 { font-size: 3rem; font-weight: bold; }
    .section { padding: 50px 0; }
    .card-city img { height: 180px; object-fit: cover; border-radius: 10px; transition: transform 0.3s ease; }
    .card-city:hover img { transform: scale(1.05); }
    .form-card { background-color: #ffffff; border-radius: 12px; padding: 30px 35px; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12); transition: box-shadow 0.3s ease; max-width: 100%; margin: auto; }
    .form-card:hover { box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18); }
    .form-card label { font-weight: 600; color: #444; margin-bottom: 8px; display: block; }
    .form-card input[type="text"], .form-card input[type="number"], .form-card select { width: 100%; padding: 10px 15px; border: 1.8px solid #ced4da; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease; box-sizing: border-box; }
    .form-card input[type="text"]:focus, .form-card input[type="number"]:focus, .form-card select:focus { outline: none; border-color: rgb(126,174,246); box-shadow: 0 0 8px rgba(13, 110, 253, 0.25); }
    .form-card button[type="submit"] { background-color: rgb(235,166,139); border: none; color: white; font-weight: 700; font-size: 1.1rem; padding: 12px 0; border-radius: 10px; cursor: pointer; transition: background-color 0.3s ease; width: 100%; }
    .form-card button[type="submit"]:hover { background-color: rgb(149,222,173); }
    @media (max-width: 576px) { .form-card { padding: 20px 20px; } }
    #transaction-section { background: linear-gradient(rgba(255,255,255,0.63), rgba(255,255,255,0.32)), url('background.jpg') no-repeat center center/cover; background-attachment: fixed; padding: 60px 0; }
    .navbar-brand { font-weight: 700; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand" href="single.php?page=home#hero-section">Real Estate Explorer</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link<?php echo $page==='home'?' active':''; ?>" href="single.php?page=home#hero-section">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="single.php?page=home#cities-section">Cities</a></li>
          <li class="nav-item"><a class="nav-link" href="single.php?page=home#transaction-section">Transactions</a></li>
          <li class="nav-item"><a class="nav-link" href="single.php?page=mca" target="_blank">MCA eConsultation</a></li>
          <?php if (isOfficialLoggedIn()): ?>
            <li class="nav-item"><a class="nav-link text-success fw-bold" href="single.php?page=dashboard">Official Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="single.php?action=logout">Logout</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="single.php?page=login">Official Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <?php if ($page === 'home'): ?>
      <div id="hero-section" class="hero"><h1>Find Your Dream Property</h1></div>

      <?php if (isset($_SESSION['tx_success'])): ?><div class="alert alert-success mt-3"><?php echo htmlspecialchars($_SESSION['tx_success']); unset($_SESSION['tx_success']); ?></div><?php endif; ?>
      <?php if (isset($_SESSION['tx_error'])): ?><div class="alert alert-danger mt-3"><?php echo htmlspecialchars($_SESSION['tx_error']); unset($_SESSION['tx_error']); ?></div><?php endif; ?>

      <section id="cities-section" class="section">
        <h2 class="section-title text-center">Popular Cities</h2>
        <div class="row">
          <?php if ($databaseError): ?>
            <div class="col-12"><div class="alert alert-warning">Database connection error: <?php echo htmlspecialchars($databaseError); ?></div></div>
          <?php else: ?>
            <?php $cityResult = $databaseConnection->query("SELECT * FROM city"); ?>
            <?php if ($cityResult): while ($row = $cityResult->fetch_assoc()): $cityName = $row['city_name']; $img = strtolower($cityName) . ".jpg"; ?>
              <div class='col-md-3 col-6 mb-4 text-center'>
                <div class='card card-city border-0' data-bs-toggle='modal' data-bs-target='#modal-<?php echo htmlspecialchars($cityName); ?>' style='cursor:pointer;'>
                  <img src='<?php echo htmlspecialchars($img); ?>' class='w-100' alt='<?php echo htmlspecialchars($cityName); ?>' />
                  <div class='card-body p-2'>
                    <h6 class='mb-0'><?php echo htmlspecialchars($cityName); ?></h6>
                    <small class='text-muted'><?php echo (int)$row['no_of_availabilities']; ?> listings</small>
                  </div>
                </div>
              </div>

              <?php $cityID = (int)$row['id']; $availResult = $databaseConnection->query("SELECT * FROM availabitities WHERE id = {$cityID}"); ?>
              <div class='modal fade' id='modal-<?php echo htmlspecialchars($cityName); ?>' tabindex='-1'>
                <div class='modal-dialog modal-lg'>
                  <div class='modal-content'>
                    <div class='modal-header'>
                      <h5 class='modal-title'>Available Properties in <?php echo htmlspecialchars($cityName); ?></h5>
                      <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                      <?php if ($availResult && $availResult->num_rows > 0): ?>
                        <div class='row'>
                          <?php while ($avail = $availResult->fetch_assoc()): ?>
                            <div class='col-md-4 mb-4'>
                              <div class='card h-100 shadow-sm border-0 property-card' data-city='<?php echo htmlspecialchars($cityName, ENT_QUOTES); ?>' data-rate='<?php echo htmlspecialchars((string)$avail['rate']); ?>' data-aid='<?php echo htmlspecialchars((string)$avail['a_id']); ?>' style='cursor:pointer;'>
                                <div class='card-body'>
                                  <h5 class='card-title text-primary'><?php echo htmlspecialchars($avail['type']); ?></h5>
                                  <p class='card-text'><strong>Mode:</strong> <?php echo htmlspecialchars($avail['mode']); ?><br /><strong>Rate:</strong> ₹<?php echo number_format((int)$avail['rate']); ?></p>
                                </div>
                              </div>
                            </div>
                          <?php endwhile; ?>
                        </div>
                      <?php else: ?>
                        <p class='text-muted'>No properties available in this city yet.</p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; endif; ?>
          <?php endif; ?>
        </div>
      </section>

      <section id="transaction-section" class="section bg-light">
        <h2 class="section-title text-center">Buy Yours</h2>
        <div class="row justify-content-center">
          <div class="col-md-6">
            <div class="form-card">
              <form action="single.php?page=home" method="post" id="transactionForm">
                <input type="hidden" name="action" value="transaction" />
                <div class="mb-3">
                  <label class="form-label">Customer Name</label>
                  <input type="text" name="cust_name" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">City</label>
                  <select name="city_name" class="form-select" required>
                    <?php if (!$databaseError): $cities = $databaseConnection->query("SELECT DISTINCT city_name FROM city"); if ($cities) { while ($city = $cities->fetch_assoc()) { echo "<option value='" . htmlspecialchars($city['city_name']) . "'>" . htmlspecialchars($city['city_name']) . "</option>"; } } endif; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Rate</label>
                  <input type="number" name="rate" class="form-control" required />
                </div>
                <input type="hidden" name="a_id" id="a_id" />
                <button type="submit" class="btn btn-primary w-100">Submit Transaction</button>
              </form>
            </div>
          </div>
        </div>
      </section>

    <?php elseif ($page === 'login'): ?>
      <div class="row justify-content-center py-5">
        <div class="col-md-5">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <h1 class="h4 mb-3">Official Login</h1>
              <?php if (isset($_SESSION['login_error'])): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?></div><?php endif; ?>
              <form method="post" action="single.php?page=login">
                <input type="hidden" name="action" value="login" />
                <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required autofocus /></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required /></div>
                <button type="submit" class="btn btn-primary w-100">Sign in</button>
              </form>
              <div class="text-center mt-3"><a href="single.php?page=home" class="text-decoration-none">Back to Home</a></div>
            </div>
          </div>
        </div>
      </div>

    <?php elseif ($page === 'dashboard'): ?>
      <?php if (!isOfficialLoggedIn()) { redirect('single.php?page=login'); } ?>
      <div class="py-4">
        <?php if (isset($_SESSION['admin_notice'])): ?><div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['admin_notice']); unset($_SESSION['admin_notice']); ?></div><?php endif; ?>
        <h1 class="h3 mb-4">Official Dashboard</h1>
        <div class="row g-3 mb-4">
          <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted">Cities</div><div class="display-6"><?php echo $databaseError ? '—' : number_format((int)($databaseConnection->query("SELECT COUNT(*) AS cnt FROM city")->fetch_assoc()['cnt'] ?? 0)); ?></div></div><span class="badge bg-primary">Total</span></div></div></div></div>
          <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted">Availabilities</div><div class="display-6"><?php echo $databaseError ? '—' : number_format((int)($databaseConnection->query("SELECT COUNT(*) AS cnt FROM availabitities")->fetch_assoc()['cnt'] ?? 0)); ?></div></div><span class="badge bg-success">Total</span></div></div></div></div>
          <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted">Transactions</div><div class="display-6"><?php echo $databaseError ? '—' : number_format((int)($databaseConnection->query("SELECT COUNT(*) AS cnt FROM transactions")->fetch_assoc()['cnt'] ?? 0)); ?></div></div><span class="badge bg-secondary">Total</span></div></div></div></div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <h2 class="h5 mb-3">Recent Transactions</h2>
            <?php if (!$databaseError): $recentTx = $databaseConnection->query("SELECT cust_name, city_name, rate, a_id FROM transactions ORDER BY a_id DESC LIMIT 10"); endif; ?>
            <?php if (!empty($recentTx) && $recentTx instanceof mysqli_result && $recentTx->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead><tr><th>Customer</th><th>City</th><th>Rate (₹)</th><th>Property ID</th></tr></thead>
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

        <div class="row g-4">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
              <h2 class="h5 mb-3">Add New City</h2>
              <form method="post" action="single.php?page=dashboard">
                <input type="hidden" name="action" value="add_city" />
                <div class="mb-3"><label class="form-label">City Name</label><input type="text" class="form-control" name="city_name" required /></div>
                <div class="mb-3"><label class="form-label">Number of Availabilities</label><input type="number" class="form-control" name="no_of_availabilities" min="0" value="0" required /></div>
                <button type="submit" class="btn btn-primary">Add City</button>
              </form>
            </div></div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
              <h2 class="h5 mb-3">Add New Availability (Property)</h2>
              <form method="post" action="single.php?page=dashboard">
                <input type="hidden" name="action" value="add_availability" />
                <div class="mb-3">
                  <label class="form-label">Select City</label>
                  <select name="city_id" class="form-select" required>
                    <option value="">Select City</option>
                    <?php if (!$databaseError): $cityOptions = $databaseConnection->query("SELECT id, city_name FROM city ORDER BY city_name"); if ($cityOptions) { while ($city = $cityOptions->fetch_assoc()) { echo '<option value="' . (int)$city['id'] . '">' . htmlspecialchars($city['city_name']) . '</option>'; } } endif; ?>
                  </select>
                </div>
                <div class="mb-3"><label class="form-label">Property Type</label><input type="text" name="type" class="form-control" placeholder="Apartment, Villa, etc." required /></div>
                <div class="mb-3"><label class="form-label">Mode</label><select name="mode" class="form-select" required><option value="">Select Mode</option><option value="Sale">Sale</option><option value="Rent">Rent</option></select></div>
                <div class="mb-3"><label class="form-label">Rate (₹)</label><input type="number" name="rate" class="form-control" min="0" required /></div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" id="available" name="available" checked><label class="form-check-label" for="available">Available</label></div>
                <button type="submit" class="btn btn-success">Add Availability</button>
              </form>
            </div></div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="py-5"><div class="alert alert-secondary">Unknown page. <a href="single.php?page=home">Go Home</a></div></div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <?php if ($page === 'home'): ?>
  <script>
  document.querySelectorAll('.property-card').forEach(function(card) {
    card.addEventListener('click', function() {
      var city = card.getAttribute('data-city');
      var rate = card.getAttribute('data-rate');
      var aid  = card.getAttribute('data-aid');
      var citySelect = document.querySelector('select[name="city_name"]');
      if (citySelect) { citySelect.value = city; }
      var rateInput = document.querySelector('input[name="rate"]');
      if (rateInput) { rateInput.value = rate; }
      var aIdInput = document.querySelector('input[name="a_id"]');
      if (aIdInput) { aIdInput.value = aid; }
      var formSection = document.querySelector('#transaction-section');
      if (formSection) { formSection.scrollIntoView({ behavior: 'smooth' }); }
      var modal = card.closest('.modal');
      if (modal) {
        var modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) { modalInstance.hide(); }
      }
    });
  });
  </script>
  <?php endif; ?>
</body>
</html>
