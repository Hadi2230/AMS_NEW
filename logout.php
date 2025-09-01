<?php
session_start();
include 'config.php';
if (isset($_SESSION['user_id'])) {
    try { logAction($pdo, 'LOGOUT', 'کاربر از سیستم خارج شد'); } catch (Throwable $e) {}
}
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location: login.php');
exit();
?>
