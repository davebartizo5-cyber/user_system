
<?php
/**
 * db_connect.php
 * InfinityFree database connection
 */

// Database credentials
$host = "sql303.infinityfree.com";
$user = "if0_43392861";
$pass = "Dhexdes123";
$db   = "if0_43392861_user_system";

// Enable MySQLi exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    die("Database connection failed. Please check your database settings.");
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => true
    ]);

    session_start();
}

// Load functions
require_once __DIR__ . '/functions.php';
