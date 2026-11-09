<?php
/**
 * login.php - SELECT + session start
 * Accepts username OR email, verifies the bcrypt hash, starts a
 * session and records the login in `login_logs`.
 */
$pageTitle = 'Login';
include 'includes/header.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {

        if ($user['status'] !== 'active') {
            $error = "This account has been disabled. Contact an administrator.";
        } else {
            // New session id on login prevents session fixation
            session_regenerate_id(true);
            $_SESSION['user_id']  = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            // Record the login (uses the related table)
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
            $log = $conn->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $log->bind_param("iss", $user['id'], $ip, $ua);
            $log->execute();
            $log->close();

            header("Location: dashboard.php");
            exit;
        }
    } else {
        // Same message for wrong user and wrong password on purpose
        $error = "Invalid username/email or password.";
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-7 col-lg-5">
    <div class="card">
      <div class="card-body p-4 p-md-5">
        <h3 class="text-center mb-4"><i class="bi bi-box-arrow-in-right me-2"></i>Login</h3>

        <?php show_flash(); ?>
        <?php if ($error): ?>
          <div class="alert alert-danger py-2"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label" for="login">Username or Email</label>
            <input type="text" id="login" name="login" class="form-control" required autofocus
                   value="<?= e($_POST['login'] ?? '') ?>">
          </div>
          <div class="mb-4">
            <label class="form-label" for="lpass">Password</label>
            <div class="input-group">
              <input type="password" id="lpass" name="password" class="form-control" required>
              <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#lpass"><i class="bi bi-eye"></i></button>
            </div>
          </div>
          <button class="btn btn-primary w-100 py-2">Login</button>
        </form>

        <p class="text-center mt-3 mb-0">No account yet? <a href="register.php">Register</a></p>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
