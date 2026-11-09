<?php
/**
 * index.php - public landing page
 */
$pageTitle = 'Home';
include 'includes/header.php';
?>

<section class="hero text-center mb-5">
    <h1 class="display-5 fw-bold">Welcome to UserHub</h1>
    <p class="lead text-muted mb-4">
        A secure user registration and login system built with PHP, MySQL and Bootstrap 5.
    </p>
    <?php if (is_logged_in()): ?>
        <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
    <?php else: ?>
        <a href="register.php" class="btn btn-primary btn-lg me-2">Get Started</a>
        <a href="login.php" class="btn btn-outline-primary btn-lg">Login</a>
    <?php endif; ?>
</section>

<div class="row g-4 text-center">
    <div class="col-md-4">
        <div class="card h-100 p-4">
            <div><span class="feature-icon"><i class="bi bi-person-plus-fill"></i></span></div>
            <h5 class="mt-3">Easy Registration</h5>
            <p class="text-muted mb-0">Create an account with live password-strength feedback and full validation.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 p-4">
            <div><span class="feature-icon"><i class="bi bi-lock-fill"></i></span></div>
            <h5 class="mt-3">Secure by Design</h5>
            <p class="text-muted mb-0">Hashed passwords, prepared statements, CSRF protection and safe sessions.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 p-4">
            <div><span class="feature-icon"><i class="bi bi-clock-history"></i></span></div>
            <h5 class="mt-3">Login History</h5>
            <p class="text-muted mb-0">Every sign-in is recorded so you can review recent account activity.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
