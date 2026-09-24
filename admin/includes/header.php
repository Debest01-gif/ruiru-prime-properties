<?php
/**
 * Admin Sidebar & Header Include
 */
session_start();
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireAdmin();

$currentAdmin = $_SESSION['admin_name'] ?? 'Admin';
$currentPage  = basename($_SERVER['PHP_SELF'], '.php');

// Count new inquiries for badge
$newInquiries = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status='new'")->fetchColumn();
$pendingTestimonials = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_approved=0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? e($adminTitle) . ' | ' : '' ?>Admin — <?= e(setting('company_name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="admin-body">

<!-- ===== SIDEBAR ===== -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="fas fa-building"></i></div>
        <div class="sidebar-brand-text">
            <span class="brand-name"><?= e(setting('company_name')) ?></span>
            <span class="brand-sub">Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">Main</div>
        <a href="<?= SITE_URL ?>/admin/index.php" class="sidebar-link <?= $currentPage === 'index' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="sidebar-section">Properties</div>
        <a href="<?= SITE_URL ?>/admin/properties.php" class="sidebar-link <?= $currentPage === 'properties' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> All Properties
        </a>
        <a href="<?= SITE_URL ?>/admin/property-add.php" class="sidebar-link <?= $currentPage === 'property-add' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Add Property
        </a>

        <div class="sidebar-section">People</div>
        <a href="<?= SITE_URL ?>/admin/agents.php" class="sidebar-link <?= $currentPage === 'agents' ? 'active' : '' ?>">
            <i class="fas fa-user-tie"></i> Agents
        </a>
        <a href="<?= SITE_URL ?>/admin/inquiries.php" class="sidebar-link <?= $currentPage === 'inquiries' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Inquiries
            <?php if ($newInquiries > 0): ?>
            <span class="sidebar-badge"><?= $newInquiries ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/testimonials.php" class="sidebar-link <?= $currentPage === 'testimonials' ? 'active' : '' ?>">
            <i class="fas fa-star"></i> Testimonials
            <?php if ($pendingTestimonials > 0): ?>
            <span class="sidebar-badge"><?= $pendingTestimonials ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-section">Content</div>
        <a href="<?= SITE_URL ?>/admin/blog.php" class="sidebar-link <?= $currentPage === 'blog' ? 'active' : '' ?>">
            <i class="fas fa-newspaper"></i> Blog Posts
        </a>
        <a href="<?= SITE_URL ?>/admin/settings.php" class="sidebar-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Site Settings
        </a>

        <div class="sidebar-section">Actions</div>
        <a href="<?= SITE_URL ?>/index.php" target="_blank" class="sidebar-link">
            <i class="fas fa-external-link-alt"></i> View Website
        </a>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="sidebar-link" style="color:#ef4444;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr($currentAdmin, 0, 1)) ?></div>
            <div>
                <div class="sidebar-user-name"><?= e($currentAdmin) ?></div>
                <div class="sidebar-user-role"><?= ucfirst($_SESSION['admin_role'] ?? 'Admin') ?></div>
            </div>
        </div>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="admin-btn admin-btn-danger admin-btn-sm" style="width:100%;justify-content:center;">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
    </div>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<main class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
        <div class="topbar-left">
            <button onclick="document.getElementById('adminSidebar').classList.toggle('open')"
                    style="display:none;background:none;border:none;color:#94a3b8;font-size:1.1rem;cursor:pointer;" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-greeting">
                Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>,
                <strong><?= e(explode(' ', $currentAdmin)[0]) ?></strong>!
                <?php if (isset($adminTitle)): ?> · <?= e($adminTitle) ?><?php endif; ?>
            </div>
        </div>
        <div class="topbar-right">
            <a href="<?= SITE_URL ?>/admin/inquiries.php" class="topbar-btn topbar-notification" title="Inquiries">
                <i class="fas fa-bell"></i>
                <?php if ($newInquiries > 0): ?><span class="notification-dot"></span><?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/index.php" target="_blank" class="topbar-btn" title="View Website">
                <i class="fas fa-external-link-alt"></i>
            </a>
            <a href="<?= SITE_URL ?>/admin/settings.php" class="topbar-btn" title="Settings">
                <i class="fas fa-cog"></i>
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="admin-content">
