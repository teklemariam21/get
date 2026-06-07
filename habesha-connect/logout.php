<?php
require_once 'includes/functions.php';
session_destroy();
redirect(SITE_URL . '/login.php');
