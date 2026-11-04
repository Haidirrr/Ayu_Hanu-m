<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit;
}

$pageTitle = $product['name'];
include 'includes/header.php';

$colors = array_filter(array_map('trim', explode(',', $product['colors'] ?? '')));
$sizes  = array_filter(array_map('trim', explode(',', $product['sizes'] ?? '')));

$imgSrc = '';
if (!empty($product['image'])) {
    $imgSrc = (strpos($product['image'], 'uploads/') === 0)
        ? e($product['image'])
        : 'uploads/' . e($product['image']);
}
?>

<div class="page-wrap">
<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="index.php">Beranda</a>
    <span class="sep">›</span>
    <a href="products.php">Katalog</a>
    <span class="sep">›</span>
    <span class="current"><?= e($product['name']) ?></span>
</nav>

<!-- Detail layout -->
<div class="detail-layout">

    <!-- Gambar -->
    <div class="detail-img-wrap">
        <?php if ($imgSrc): ?>
            <img src="<?= $imgSrc ?>" alt="<?= e($product['name']) ?>" style="max-height:520px;width:100%;object-fit:contain;">
        <?php else: ?>
            <div style="height:400px;display:flex;align-items:center;justify-content:center;font-size:5rem;background:#f9f9f9;color:#ddd;">👜</div>
        <?php endif; ?>
    </div>

    <!-- Info & form -->
    <div class="detail-body">
        <div class="detail-cat"><?= e($product['category_name'] ?? "Ayu Hanu'm") ?></div>
        <h1 class="detail-title"><?= e($product['name']) ?></h1>
        <div class="detail-price"><?= rupiah($product['price']) ?></div>

        <?php if (!empty($product['description'])): ?>
            <p style="color:#555;line-height:1.8;margin-bottom:18px;"><?= e($product['description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($product['specs'])): ?>
            <div style="background:#f8f8f8;border-left:3px solid #8b1a2f;padding:12px 16px;border-radius:4px;margin-bottom:18px;font-size:13px;">
                <strong>Spesifikasi:</strong><br>
                <?= str_replace('|', '<br>', e($product['specs'])) ?>
            </div>
        <?php endif; ?>

        <p style="font-size:13px;color:#477b60;font-weight:600;margin-bottom:20px;">
            ✓ Stok tersedia: <?= (int)$product['stock'] ?> pcs
        </p>

        <form action="cart.php" method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

            <?php if (!empty($colors)): ?>
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Warna</label>
                <select name="color" required style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:14px;font-size:14px;background:#fff;">
                    <?php foreach ($colors as $color): ?>
                        <option value="<?= e($color) ?>"><?= e($color) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="color" value="-">
            <?php endif; ?>

            <?php if (!empty($sizes)): ?>
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Ukuran</label>
                <select name="size" required style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:14px;font-size:14px;background:#fff;">
                    <?php foreach ($sizes as $size): ?>
                        <option value="<?= e($size) ?>"><?= e($size) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="size" value="-">
            <?php endif; ?>

            <label style="display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Jumlah</label>
            <input type="number" name="qty" value="1" min="1" max="<?= (int)$product['stock'] ?>" required
                   style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:20px;font-size:14px;">

            <div class="detail-actions">
                <button class="btn btn-primary" type="submit" style="flex:1;">
                    Tambah ke Keranjang
                </button>
                <a href="products.php" class="btn btn-outline-red">← Kembali</a>
            </div>
        </form>
    </div>

</div><!-- /.detail-layout -->
</div><!-- /.page-wrap -->

<?php include 'includes/footer.php'; ?>
