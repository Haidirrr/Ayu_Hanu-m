<?php
require_once "config.php";
require_once "includes/functions.php";

$dbProducts = get_products($conn, false, 5);

include "includes/header.php";
?>

<!-- ========================= HERO SECTION ========================= -->
<section class="hero-wrap">
    <div class="wrap">
        <div class="hero">
            <!-- Left Copy -->
            <div class="hero-copy">
                <h1 class="hero-h1">
                    Step Into<br>Greatness
                </h1>
                <p class="hero-sub">
                    Discover authentic branded shoes for every step of your journey.
                </p>
                <div class="hero-btns">
                    <a href="products.php" class="btn-hero-primary">
                        Shop Now <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Right Visual (Foto Produk) -->
            <div class="hero-img-wrap">
                <img src="uploads/keychain bg.jpeg" alt="Produk Ayu Hanu'm" class="hero-shoe-img" loading="eager">
            </div>
        </div>
    </div>
</section>

<!-- ========================= AYU HANU'M COLLECTION STRIP ========================= -->
<section class="brand-row">
    <div class="wrap">
        <div class="brand-row-inner">
            <span class="brand-label">AYU HANU'M</span>

            <div class="brand-logos-list">
                <a href="products.php?search=Keychain" class="brand-item">
                    <span class="brand-text-logo nike">Keychain</span>
                </a>
                <a href="products.php?search=Dompet" class="brand-item">
                    <span class="brand-text-logo adidas">Dompet</span>
                </a>
                <a href="products.php?search=Tas" class="brand-item">
                    <span class="brand-text-logo puma">Tas</span>
                </a>
              
            </div>

            <a href="products.php" class="view-all brand-view-all">View all</a>
        </div>
    </div>
</section>

<!-- ========================= SHOP BY CATEGORY ========================= -->
<section class="section">
    <div class="wrap">
        <div class="section-hdr">
            <h2 class="section-title">SHOP BY CATEGORY</h2>
        </div>

        <div class="cat-grid">
            <!-- 1. Keychain -->
            <a href="products.php?search=Keychain" class="cat-card">
                <div class="cat-img">
                    <img src="uploads/Keychain2.jpeg" alt="Keychain">
                </div>
                <span class="cat-name">crochet kue gulung</span>
            </a>

            <!-- 2. Dompet -->
            <a href="products.php?search=Dompet" class="cat-card">
                <div class="cat-img">
                    <img src="uploads/dompet.jpg" alt="Dompet">
                </div>
                <span class="cat-name">Dompet</span>
            </a>

            <!-- 3. Tas -->
            <a href="products.php?search=Tas" class="cat-card">
                <div class="cat-img">
                    <img src="uploads/tas_kulit.jpg" alt="Tas">
                </div>
                <span class="cat-name">Tas</span>
            </a>
        </div>
    </div>
</section>

<!-- ========================= BEST SELLERS ========================= -->
<section class="section" style="padding-top: 10px;">
    <div class="wrap">
        <div class="section-hdr">
            <h2 class="section-title">BEST SELLERS</h2>
            <a href="products.php" class="view-all">View all</a>
        </div>

        <div class="product-grid">
            <!-- Product 1 -->
            <div class="product-card">
                <a href="product.php?id=1" class="prod-img">
                    <img src="uploads/dompet.jpg" alt="Nike Air Force 1 '07" loading="lazy">
                </a>
                <div class="prod-body">
                    <h3 class="prod-name">
                        <a href="product.php?id=1">Nike Air Force 1 '07</a>
                    </h3>
                    <div class="prod-cat">Men's Shoes</div>
                    <div class="prod-footer">
                        <div class="prod-price">rp.20.000</div>
                        <div class="prod-rating">
                            <span class="star">★</span> 4.8 <span class="rating-count">(2.4k)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="product-card">
                <a href="product.php?id=2" class="prod-img">
                    <img src="uploads/Keychain.jpeg" alt="Adidas Superstar" loading="lazy">
                </a>
                <div class="prod-body">
                    <h3 class="prod-name">
                        <a href="product.php?id=2">Adidas Superstar</a>
                    </h3>
                    <div class="prod-cat">Unisex Shoes</div>
                    <div class="prod-footer">
                        <div class="prod-price">rp.15.000</div>
                        <div class="prod-rating">
                            <span class="star">★</span> 4.7 <span class="rating-count">(1.8k)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 3 -->
            <div class="product-card">
                <a href="product.php?id=3" class="prod-img">
                    <img src="uploads/tas_kulit.jpg" alt="Air Jordan 4 Retro" loading="lazy">
                </a>
                <div class="prod-body">
                    <h3 class="prod-name">
                        <a href="product.php?id=3">Air Jordan 4 Retro</a>
                    </h3>
                    <div class="prod-cat">Men's Shoes</div>
                    <div class="prod-footer">
                        <div class="prod-price">rp.20.000</div>
                        <div class="prod-rating">
                            <span class="star">★</span> 4.9 <span class="rating-count">(3.2k)</span>
                        </div>
                    </div>
                </div>
            </div>

              <div class="product-card">
                <a href="product.php?id=3" class="prod-img">
                    <img src="uploads/tas_kulit.jpg" alt="Air Jordan 4 Retro" loading="lazy">
                </a>
                <div class="prod-body">
                    <h3 class="prod-name">
                        <a href="product.php?id=3">Air Jordan 4 Retro</a>
                    </h3>
                    <div class="prod-cat">Men's Shoes</div>
                    <div class="prod-footer">
                        <div class="prod-price">rp.20.000</div>
                        <div class="prod-rating">
                            <span class="star">★</span> 4.9 <span class="rating-count">(3.2k)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 5 -->
            <div class="product-card">
                <a href="products.php" class="prod-img">
                    <img src="uploads/Keychain.jpeg" alt="Vans Old Skool" loading="lazy">
                </a>
                <div class="prod-body">
                    <h3 class="prod-name">
                        <a href="products.php">Vans Old Skool</a>
                    </h3>
                    <div class="prod-cat">Unisex Shoes</div>
                    <div class="prod-footer">
                        <div class="prod-price">rp.12.000</div>
                        <div class="prod-rating">
                            <span class="star">★</span> 4.7 <span class="rating-count">(1.2k)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================= SUMMER SALE BANNER ========================= -->
<section class="section" style="padding-top: 10px; padding-bottom: 20px;">
    <div class="wrap">
        <div class="sale-banner">
            <!-- Left Copy -->
            <div class="sale-copy">
                <div class="sale-eyebrow">LIMITED TIME ONLY</div>
                <h2 class="sale-h2">Summer Sale</h2>
                <p class="sale-sub">Up to 40% off on selected items</p>
                <a href="products.php?sort=price_asc" class="btn-sale">
                    Shop the Sale <span>&rarr;</span>
                </a>
            </div>

            <!-- Right Visual -->
            <div class="sale-visual">
                <img src="uploads/dompet.jpg" alt="Summer Sale Shoes" class="sale-shoe-img">
                <div class="sale-pct">
                    40%
                    <span>OFF</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?>
