<?php
/**
 * edit.php - UPDATE
 * Admins can edit anyone (including role/status);
 * regular users can only edit their own account.
 */
$pageTitle = 'Edit Account';
include 'includes/header.php';
require_login();

$id     = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$selfId = (int)$_SESSION['user_id'];

// Authorization check
if (!is_admin() && $id !== $selfId) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Access denied.</div>';
    include 'includes/footer.php';
    exit;
}

// Load the record
$stmt = $conn->prepare("SELECT id, full_name, email, username, role, status FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo '<div class="alert alert-warning">User not found.</div><a href="dashboard.php" class="btn btn-secondary">Back</a>';
    include 'includes/footer.php';
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $new_pass  = $_POST['password'] ?? '';

    // Only admins may change role/status, and never their own (prevents lock-out)
    $role   = $user['role'];
    $status = $user['status'];
    if (is_admin() && $id !== $selfId) {
        $role   = in_array($_POST['role'] ?? '', ['admin', 'user'], true) ? $_POST['role'] : $role;
        $status = in_array($_POST['status'] ?? '', ['active', 'disabled'], true) ? $_POST['status'] : $status;
    }

    // Validate
    if ($full_name === '' || $email === '' || $username === '') {
        $errors[] = "Name, email and username are required.";
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if ($username !== '' && !preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = "Username must be 3-30 characters: letters, numbers and underscores only.";
    }
    if ($new_pass !== '' && strlen($new_pass) < 8) {
        $errors[] = "New password must be at least 8 characters.";
    }

    // Duplicate check (ignore this user's own row)
    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id <> ? LIMIT 1");
        $stmt->bind_param("ssi", $email, $username, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "That email or username is already used by another account.";
        }
        $stmt->close();
    }

    // UPDATE
    if (!$errors) {
        if ($new_pass !== '') {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, username=?, role=?, status=?, password=? WHERE id=?");
            $stmt->bind_param("ssssssi", $full_name, $email, $username, $role, $status, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, username=?, role=?, status=? WHERE id=?");
            $stmt->bind_param("sssssi", $full_name, $email, $username, $role, $status, $id);
        }
        $stmt->execute();
        $stmt->close();

        if ($id === $selfId) {
            $_SESSION['username'] = $username;   // keep the navbar in sync
        }
        flash("Account updated successfully.");
        header("Location: dashboard.php");
        exit;
    }

    // Re-show what the user typed if validation failed
    $user['full_name'] = $full_name;
    $user['email']     = $email;
    $user['username']  = $username;
}
?>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card">
      <div class="card-body p-4 p-md-5">
        <h3 class="mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Account</h3>

        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger py-2"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

          <div class="mb-3">
            <label class="form-label" for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-control" maxlength="100" required value="<?= e($user['full_name']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" maxlength="100" required value="<?= e($user['email']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" maxlength="30" required value="<?= e($user['username']) ?>">
          </div>

          <?php if (is_admin() && $id !== $selfId): ?>
          <div class="row">
            <div class="col-sm-6 mb-3">
              <label class="form-label" for="role">Role</label>
              <select id="role" name="role" class="form-select">
                <option value="user"  <?= $user['role'] === 'user'  ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
              </select>
            </div>
            <div class="col-sm-6 mb-3">
              <label class="form-label" for="status">Status</label>
              <select id="status" name="status" class="form-select">
                <option value="active"   <?= $user['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
                <option value="disabled" <?= $user['status'] === 'disabled' ? 'selected' : '' ?>>Disabled</option>
              </select>
            </div>
          </div>
          <?php endif; ?>

          <div class="mb-4">
            <label class="form-label" for="password">New Password <small class="text-muted">(leave blank to keep current)</small></label>
            <div class="input-group">
              <input type="password" id="password" name="password" class="form-control" minlength="8" autocomplete="new-password">
              <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="strength-bar mt-2"><div id="strengthFill"></div></div>
            <small id="strengthLabel" class="text-muted"></small>
          </div>

          <button class="btn btn-primary">Save Changes</button>
          <a href="dashboard.php" class="btn btn-secondary ms-1">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
