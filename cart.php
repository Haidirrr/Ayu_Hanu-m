<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = (int)($_POST['id'] ?? 0);
        $qty       = max(1, (int)($_POST['qty'] ?? 1));
        $color     = trim($_POST['color'] ?? '-');
        $size      = trim($_POST['size'] ?? '-');
        if ($productId > 0) {
            $cartKey = $productId . '_' . md5($color . '_' . $size);
            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$cartKey] = ['id' => $productId, 'qty' => $qty, 'color' => $color, 'size' => $size];
            }
        }
        header('Location: cart.php'); exit;
    }
    if ($action === 'inc') {
        $key = $_POST['key'] ?? '';
        if (isset($_SESSION['cart'][$key])) $_SESSION['cart'][$key]['qty']++;
        header('Location: cart.php'); exit;
    }
    if ($action === 'dec') {
        $key = $_POST['key'] ?? '';
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty']--;
            if ($_SESSION['cart'][$key]['qty'] <= 0) unset($_SESSION['cart'][$key]);
        }
        header('Location: cart.php'); exit;
    }
    if ($action === 'remove') {
        $key = $_POST['key'] ?? '';
        if (isset($_SESSION['cart'][$key])) unset($_SESSION['cart'][$key]);
        header('Location: cart.php'); exit;
    }
}

$items    = cart_items($conn);
$subtotal = cart_subtotal($items);
$pageTitle = 'Keranjang Belanja';
include 'includes/header.php';
?>

<div class="page-wrap">
<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="index.php">Keranjang Belanja</a>
    <span class="sep">›</span>
    <span class="current">Rincian Transaksi</span>
    <span class="sep">›</span>
    <span class="current">Transaksi Selesai</span>
</nav>

<div class="cart-page">

<?php if (!$items): ?>

    <!-- Keranjang kosong -->
    <div class="cart-empty">
        <p>Keranjang Anda saat ini kosong.</p>
        <a href="products.php" class="btn btn-primary">KEMBALI KE TOKO</a>
    </div>

<?php else: ?>

    <div class="cart-layout">

        <!-- Tabel item -->
        <div>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <!-- Produk -->
                        <td>
                            <div class="cart-prod">
                                <?php
                                    $imgSrc = '';
                                    if (!empty($item['image'])) {
                                        $imgSrc = (strpos($item['image'], 'uploads/') === 0)
                                            ? e($item['image'])
                                            : 'uploads/' . e($item['image']);
                                    }
                                ?>
                                <?php if ($imgSrc): ?>
                                    <img class="cart-img" src="<?= $imgSrc ?>" alt="<?= e($item['name']) ?>">
                                <?php else: ?>
                                    <div class="cart-img" style="display:flex;align-items:center;justify-content:center;font-size:2rem;background:#f5f5f5;">👜</div>
                                <?php endif; ?>
                                <div>
                                    <div class="cart-name"><?= e($item['name']) ?></div>
                                    <div class="cart-meta">Warna: <?= e($item['sel_color']) ?> | Ukuran: <?= e($item['sel_size']) ?></div>
                                </div>
                            </div>
                        </td>
                        <!-- Harga satuan -->
                        <td class="cart-price"><?= rupiah($item['price']) ?></td>
                        <!-- Qty -->
                        <td>
                            <div class="qty-ctrl">
                                <form method="POST" action="cart.php" style="display:contents">
                                    <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                                    <button type="submit" name="action" value="dec" class="qty-btn">−</button>
                                </form>
                                <span class="qty-val"><?= (int)$item['qty'] ?></span>
                                <form method="POST" action="cart.php" style="display:contents">
                                    <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                                    <button type="submit" name="action" value="inc" class="qty-btn">+</button>
                                </form>
                            </div>
                        </td>
                        <!-- Subtotal baris -->
                        <td class="cart-price"><?= rupiah($item['line_total']) ?></td>
                        <!-- Hapus -->
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                                <button type="submit" name="action" value="remove" class="remove-btn" title="Hapus">✕</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top:10px;">
                <a href="products.php" class="btn btn-outline-red">← Lanjut Belanja</a>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="cart-summary">
            <h3>Ringkasan Pesanan</h3>
            <div class="summary-row">
                <span>Subtotal</span>
                <span><?= rupiah($subtotal) ?></span>
            </div>
            <div class="summary-row">
                <span>Pengiriman</span>
                <span style="color:#888">Dihitung saat checkout</span>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span class="price"><?= rupiah($subtotal) ?></span>
            </div>
            <form action="checkout.php" method="GET">
                <button type="submit" class="btn btn-primary">Lanjut ke Checkout →</button>
            </form>
        </div>

    </div>

<?php endif; ?>

</div><!-- /.cart-page -->
</div><!-- /.page-wrap -->

<?php include 'includes/footer.php'; ?>
