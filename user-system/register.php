<?php
/**
 * register.php - CREATE (INSERT)
 * Validates the form, checks for duplicates, hashes the password
 * and inserts a new row into `users`.
 */
$pageTitle = 'Register';
include 'includes/header.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // 1. Collect and clean input
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    // 2. Validate
    if ($full_name === '' || $email === '' || $username === '' || $password === '') {
        $errors[] = "All fields are required.";
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if ($username !== '' && !preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = "Username must be 3-30 characters: letters, numbers and underscores only.";
    }
    if ($password !== '' && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // 3. Duplicate check (prepared statement prevents SQL injection)
    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "That email or username is already registered.";
        }
        $stmt->close();
    }

    // 4. INSERT with a hashed password
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, username, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $username, $hash);
        try {
            $stmt->execute();
            flash("Registration successful! You can now log in.");
            header("Location: login.php");
            exit;
        } catch (mysqli_sql_exception $ex) {
            $errors[] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card">
      <div class="card-body p-4 p-md-5">
        <h3 class="text-center mb-4"><i class="bi bi-person-plus-fill me-2"></i>Create Account</h3>

        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger py-2"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" novalidate>
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label" for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-control" maxlength="100" required
                   value="<?= e($_POST['full_name'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" maxlength="100" required
                   value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" maxlength="30" required
                   value="<?= e($_POST['username'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
              <input type="password" id="password" name="password" class="form-control" minlength="8" required>
              <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="strength-bar mt-2"><div id="strengthFill"></div></div>
            <small id="strengthLabel" class="text-muted"></small>
          </div>
          <div class="mb-4">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <div class="input-group">
              <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
              <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#confirm_password"><i class="bi bi-eye"></i></button>
            </div>
          </div>
          <button class="btn btn-primary w-100 py-2">Register</button>
        </form>

        <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
