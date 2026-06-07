<?php
require_once dirname(__DIR__) . '/includes/functions.php';
unset($_SESSION['admin_id']);
redirect(SITE_URL . '/admin/login.php');
