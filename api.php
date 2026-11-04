<?php
require_once 'config.php';
require_once 'includes/functions.php';

$items = cart_items($conn);
$subtotal = cart_subtotal($items);

$pageTitle = 'Keranjang Belanja';
include 'includes/header.php';
?>

<section class="section">
    <h1>Keranjang Belanja</h1>

    <?php if (!$items): ?>
        <p style="margin: 30px 0;">Keranjang belanja Anda masih kosong.</p>
        <a href="products.php" class="btn-pink">Mulai Belanja 🛍️</a>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 20px; align-items: start;">
            <div>
                <?php foreach ($items as $item): ?>
                    <div class="product-card product-info" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; margin-bottom: 16px; padding: 16px;">
                        <div>
                            <h3 style="margin-bottom: 4px;"><?= e($item['name']) ?></h3>
                            <small style="color: var(--muted);">Warna: <?= e($item['sel_color']) ?> • Ukuran: <?= e($item['sel_size']) ?></small>
                            <div class="price" style="margin-top: 6px; font-size: 1.05rem;"><?= rupiah($item['price']) ?></div>
                        </div>
                        <div style="text-align: right;">
                            <form method="POST" action="cart.php" style="display: flex; gap: 6px; align-items: center; margin-bottom: 6px;">
                                <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                                <button type="submit" name="action" value="dec" class="btn-outline" style="padding: 2px 8px;">-</button>
                                <span style="font-weight: bold; width: 24px; text-align: center;"><?= (int)$item['qty'] ?></span>
                                <button type="submit" name="action" value="inc" class="btn-outline" style="padding: 2px 8px;">+</button>
                                <button type="submit" name="action" value="remove" class="btn-pink" style="padding: 4px 10px; font-size: 0.8rem; background: #dc3545; border: none;">Hapus</button>
                            </form>
                            <small style="color: var(--muted);">Subtotal: <strong><?= rupiah($item['line_total']) ?></strong></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="product-card product-info" style="padding: 24px;">
                <h3>Ringkasan Pesanan</h3>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 15px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 1.1rem; margin-bottom: 20px;">
                    <span>Subtotal:</span>
                    <strong style="color: var(--pink-dark);"><?= rupiah($subtotal) ?></strong>
                </div>
                
                <!-- Tombol dengan onclick JavaScript langsung -->
                <button type="button" onclick="window.location.href='checkout.php';" class="btn-pink" style="width: 100%; text-align: center; border: none; cursor: pointer; font-size: 1rem; padding: 12px 18px; border-radius: 24px; display: block;">
                    Lanjut ke Checkout ➔
                </button>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>