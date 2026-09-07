<?php
/**
 * Shared Header / Navigation
 * Ruiru Prime Properties
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(setting('company_tagline')) ?> - Premium properties in Ruiru, Kiambu County.">
    <meta name="keywords" content="real estate Ruiru, property Ruiru, houses for sale Ruiru, land Ruiru, Kiambu property">
    <meta name="author" content="<?= e(setting('company_name')) ?>">
    <meta property="og:title" content="<?= e(setting('company_name')) ?>">
    <meta property="og:description" content="<?= e(setting('company_tagline')) ?>">
    <meta property="og:type" content="website">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= e(setting('company_name')) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- AOS Animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- ===== TOP BAR ===== -->
<div class="topbar">
    <div class="container">
        <div class="topbar-left">
            <a href="tel:<?= e(setting('company_phone')) ?>">
                <i class="fas fa-phone-alt"></i> <?= e(setting('company_phone')) ?>
            </a>
            <a href="mailto:<?= e(setting('company_email')) ?>">
                <i class="fas fa-envelope"></i> <?= e(setting('company_email')) ?>
            </a>
            <span><i class="fas fa-map-marker-alt"></i> <?= e(setting('company_address')) ?></span>
        </div>
        <div class="topbar-right">
            <?php if (!empty(setting('facebook_url'))): ?>
            <a href="<?= e(setting('facebook_url')) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <?php endif; ?>
            <?php if (!empty(setting('instagram_url'))): ?>
            <a href="<?= e(setting('instagram_url')) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
            <?php endif; ?>
            <?php if (!empty(setting('twitter_url'))): ?>
            <a href="<?= e(setting('twitter_url')) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
            <?php endif; ?>
            <a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>" target="_blank" class="whatsapp-btn">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
        </div>
    </div>
</div>

<!-- ===== MAIN NAVBAR ===== -->
<nav class="navbar" id="mainNav">
    <div class="container">
        <a href="<?= SITE_URL ?>/index.php" class="navbar-brand">
            <div class="brand-logo">
                <i class="fas fa-building"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name"><?= e(setting('company_name')) ?></span>
                <span class="brand-sub">Premium Real Estate</span>
            </div>
        </a>

        <ul class="nav-links" id="navLinks">
            <li><a href="<?= SITE_URL ?>/index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= SITE_URL ?>/properties.php" class="<?= $currentPage === 'properties' ? 'active' : '' ?>">Properties</a></li>
            <li><a href="<?= SITE_URL ?>/agents.php" class="<?= $currentPage === 'agents' ? 'active' : '' ?>">Our Agents</a></li>
            <li><a href="<?= SITE_URL ?>/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About Us</a></li>
            <li><a href="<?= SITE_URL ?>/blog.php" class="<?= $currentPage === 'blog' ? 'active' : '' ?>">Blog</a></li>
            <li><a href="<?= SITE_URL ?>/mortgage-calculator.php" class="<?= $currentPage === 'mortgage-calculator' ? 'active' : '' ?>">Calculator</a></li>
            <li><a href="<?= SITE_URL ?>/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
        </ul>

        <div class="nav-actions">
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary nav-cta">
                <i class="fas fa-home"></i> List Property
            </a>
            <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Menu Overlay -->
<div class="nav-overlay" id="navOverlay"></div>
