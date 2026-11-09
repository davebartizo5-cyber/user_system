<?php
/**
 * delete.php - DELETE
 * POST-only and CSRF-protected. Admins can delete any user except
 * themselves; users can delete their own account.
 * Related login_logs rows are removed automatically (ON DELETE CASCADE).
 */
require_once 'includes/db_connect.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}
verify_csrf();

$id     = (int)($_POST['id'] ?? 0);
$selfId = (int)$_SESSION['user_id'];

// Authorization: admin = anyone, user = only self
if (!is_admin() && $id !== $selfId) {
    http_response_code(403);
    die("Access denied.");
}

// An admin cannot delete their own account (avoids leaving the system with no admin)
if (is_admin() && $id === $selfId) {
    flash("Administrators cannot delete their own account.", "warning");
    header("Location: dashboard.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// A user who deleted their own account is logged out
if ($id === $selfId) {
    $_SESSION = [];
    session_destroy();
    session_start();
    flash("Your account has been deleted.", "info");
    header("Location: register.php");
    exit;
}

flash("User deleted.");
header("Location: dashboard.php");
exit;
