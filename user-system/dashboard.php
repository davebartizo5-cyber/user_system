<?php
/**
 * dashboard.php - READ (SELECT)
 *  - Admin : stats, searchable + paginated list of ALL users
 *  - User  : own profile and own login history
 */
$pageTitle = 'Dashboard';
include 'includes/header.php';
require_login();

$uid     = (int)$_SESSION['user_id'];
$isAdmin = is_admin();

// ---------------------------------------------------------------------------
// Admin: statistics + search + pagination
// ---------------------------------------------------------------------------
if ($isAdmin) {
    $totalUsers  = (int)$conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
    $totalAdmins = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetch_row()[0];
    $loginsToday = (int)$conn->query("SELECT COUNT(*) FROM login_logs WHERE DATE(login_time) = CURDATE()")->fetch_row()[0];

    $search  = trim($_GET['q'] ?? '');
    $perPage = 5;
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $like    = '%' . $search . '%';

    // Count matching rows
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE full_name LIKE ? OR email LIKE ? OR username LIKE ?");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $matches = (int)$stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $pages  = max(1, (int)ceil($matches / $perPage));
    $page   = min($page, $pages);
    $offset = ($page - 1) * $perPage;

    // Fetch the current page
    $stmt = $conn->prepare(
        "SELECT id, full_name, email, username, role, status, created_at
         FROM users
         WHERE full_name LIKE ? OR email LIKE ? OR username LIKE ?
         ORDER BY id DESC LIMIT ? OFFSET ?"
    );
    $stmt->bind_param("sssii", $like, $like, $like, $perPage, $offset);
    $stmt->execute();
    $users = $stmt->get_result();
    $stmt->close();
} else {
    // Regular user: only their own record
    $stmt = $conn->prepare("SELECT id, full_name, email, username, role, status, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $users = $stmt->get_result();
    $stmt->close();
}

// Own recent login history (Enhancement: uses the related table)
$stmt = $conn->prepare("SELECT login_time, ip_address, user_agent FROM login_logs WHERE user_id = ? ORDER BY login_time DESC, id DESC LIMIT 5");
$stmt->bind_param("i", $uid);
$stmt->execute();
$history = $stmt->get_result();
$stmt->close();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Welcome, <?= e($_SESSION['username']) ?>!</h2>
    <span class="badge bg-<?= $isAdmin ? 'danger' : 'secondary' ?> fs-6"><?= e(ucfirst($_SESSION['role'])) ?></span>
</div>

<?php show_flash(); ?>

<?php if ($isAdmin): ?>
<div class="row g-3 mb-4">
    <div class="col-sm-4"><div class="card stat-card p-3 text-center"><div class="text-muted">Total Users</div><div class="stat-number"><?= $totalUsers ?></div></div></div>
    <div class="col-sm-4"><div class="card stat-card p-3 text-center"><div class="text-muted">Administrators</div><div class="stat-number"><?= $totalAdmins ?></div></div></div>
    <div class="col-sm-4"><div class="card stat-card p-3 text-center"><div class="text-muted">Logins Today</div><div class="stat-number"><?= $loginsToday ?></div></div></div>
</div>
<?php endif; ?>

<div class="card mb-4">
  <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
    <strong><?= $isAdmin ? 'All Users' : 'My Profile' ?></strong>
    <?php if ($isAdmin): ?>
    <form method="GET" class="d-flex" role="search">
        <input type="search" name="q" class="form-control form-control-sm me-2" placeholder="Search name, email, username"
               value="<?= e($search) ?>">
        <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
    </form>
    <?php endif; ?>
  </div>

  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
      <?php if ($users->num_rows === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
      <?php endif; ?>
      <?php while ($row = $users->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$row['id'] ?></td>
          <td><?= e($row['full_name']) ?></td>
          <td><?= e($row['email']) ?></td>
          <td><?= e($row['username']) ?></td>
          <td><span class="badge bg-<?= $row['role'] === 'admin' ? 'danger' : 'secondary' ?>"><?= e($row['role']) ?></span></td>
          <td><span class="badge bg-<?= $row['status'] === 'active' ? 'success' : 'dark' ?>"><?= e($row['status']) ?></span></td>
          <td><?= e(date('M d, Y', strtotime($row['created_at']))) ?></td>
          <td class="text-end text-nowrap">
            <a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
            <?php if (!($isAdmin && (int)$row['id'] === $uid)): ?>
            <form action="delete.php" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete this account? This cannot be undone.');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
              <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <?php if ($isAdmin && $pages > 1): ?>
  <div class="card-footer bg-transparent">
    <nav aria-label="User pages">
      <ul class="pagination pagination-sm justify-content-center mb-0">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header bg-transparent py-3"><strong><i class="bi bi-clock-history me-1"></i>My Recent Logins</strong></div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th>Date &amp; Time</th><th>IP Address</th><th>Device / Browser</th></tr></thead>
      <tbody>
      <?php if ($history->num_rows === 0): ?>
        <tr><td colspan="3" class="text-muted text-center py-3">No login history yet.</td></tr>
      <?php endif; ?>
      <?php while ($h = $history->fetch_assoc()): ?>
        <tr>
          <td><?= e(date('M d, Y h:i A', strtotime($h['login_time']))) ?></td>
          <td><?= e($h['ip_address']) ?></td>
          <td class="small text-muted"><?= e(mb_strimwidth($h['user_agent'] ?? '', 0, 70, '...')) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
