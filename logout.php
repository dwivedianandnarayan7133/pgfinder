<?php
/**
 * logout.php — Session Logout
 * Student Accommodation Platform
 */
require_once 'config/db.php';
startSession();

$_SESSION = [];
session_destroy();

redirect('login.php');
