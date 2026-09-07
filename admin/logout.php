<?php
/**
 * Admin Logout
 */
session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);
session_destroy();

redirect(SITE_URL . '/admin/login.php?logged_out=1');
