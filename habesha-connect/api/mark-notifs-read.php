<?php
require_once '../includes/functions.php';
if (!isLoggedIn()) { redirect(SITE_URL . '/login.php'); }
markNotificationsRead($_SESSION['user_id']);
$ref = $_SERVER['HTTP_REFERER'] ?? SITE_URL . '/browse.php';
redirect($ref);
