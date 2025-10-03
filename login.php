<?php
session_start();

$errMsg = null;

if (!empty($_SESSION['official_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $validUsername = 'official';
    $validPassword = 'official123';

    if (hash_equals($validUsername, $username) && hash_equals($validPassword, $password)) {
        $_SESSION['official_logged_in'] = true;
        $_SESSION['official_username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $errMsg = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Official Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3">Official Login</h1>
            <?php if ($errMsg): ?>
              <div class="alert alert-danger py-2"><?php echo htmlspecialchars($errMsg); ?></div>
            <?php endif; ?>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus />
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required />
              </div>
              <button type="submit" class="btn btn-primary w-100">Sign in</button>
            </form>
            <div class="text-center mt-3">
              <a href="index.php" class="text-decoration-none">Back to Home</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
