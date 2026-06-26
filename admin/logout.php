<?php
require_once __DIR__ . '/../config/db.php';
startSession();
if (isset($_SESSION['admin_id'])) { unset($_SESSION['admin_id'],$_SESSION['admin_name']); }
if (isset($_SESSION['user_id']) && ($_SESSION['user_role']??'') === 'admin') {
    $_SESSION = []; session_destroy();
}
redirect(BASE_URL . 'admin/login.php');
