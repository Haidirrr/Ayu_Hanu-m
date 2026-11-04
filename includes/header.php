<?php
require_once __DIR__ . "/functions.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . " — Ayu Hanu'm" : "Ayu Hanu'm — Authentic Branded Shoes & Fashion" ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
</head>
<body>

<!-- ========================= TOP ANNOUNCEMENT ========================= -->
<div class="announce-bar" id="announceBar">
    <span>Free shipping on all orders over $100</span>
    <span class="sep">|</span>
    <span>Easy 30-day returns</span>
    <button class="announce-close" onclick="document.getElementById('announceBar').style.display='none'" aria-label="Close">✕</button>
</div>

<!-- ========================= SITE HEADER ========================= -->
<header class="site-header">
    <div class="hdr-inner">
        <!-- Logo -->
        <a href="index.php" class="hdr-logo">
            AYU<span class="x"> HANU'M</span>
        </a>

        <!-- Center Nav -->
        <nav class="hdr-nav" id="mainNav">
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) === 'products.php' && empty($_GET['category']) ? 'active' : '' ?>">Shop</a>
            <a href="admin.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['admin.php', 'admin_orders.php']) ? 'active' : '' ?>" style="background: #f0f0f4; font-weight: 700; border-radius: 6px; padding: 6px 12px; margin-left: 6px; display: inline-flex; align-items: center; gap: 4px;">⚙️ Admin</a>
        </nav>

        <!-- Right Action Icons -->
        <div class="hdr-icons">
            <!-- Search Button -->
            <button class="hdr-icon-btn" onclick="toggleSearch()" title="Pencarian" aria-label="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>

            <!-- Cart Button -->
            <a href="cart.php" class="hdr-icon-btn" title="Keranjang Belanja" aria-label="Cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <span class="cart-badge"><?= cart_count() ?></span>
            </a>

            <!-- Account / Admin Button -->
            <a href="admin.php" class="hdr-icon-btn" title="Akun / Admin" aria-label="Account">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </a>

            <!-- Mobile Hamburger -->
            <button class="hdr-burger" onclick="toggleMobileNav()" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>

    <!-- Quick Search Overlay Bar -->
    <div id="quickSearch" class="quick-search-bar" style="display:none;">
        <div class="wrap">
            <form action="products.php" method="GET" class="search-form-full">
                <input type="text" name="search" placeholder="Cari sepatu, brand, atau kategori..." autofocus>
                <button type="submit" class="btn btn-primary" style="padding: 8px 18px;">Cari</button>
                <button type="button" onclick="toggleSearch()" class="btn-close-search">✕</button>
            </form>
        </div>
    </div>
</header>

<main class="site-main">
