<?php
require_once 'config.php';
require_once 'includes/functions.php';

$search        = trim($_GET['search'] ?? '');
$categoryParam = trim($_GET['category'] ?? '');
$sort          = trim($_GET['sort'] ?? 'newest');

$categoryId = 0;
if (is_numeric($categoryParam) && (int)$categoryParam > 0) {
    $categoryId = (int)$categoryParam;
} elseif ($categoryParam !== '') {
    // Cek apakah ada kategori dengan nama / slug ini
    $stmtCat = $conn->prepare("SELECT id FROM categories WHERE slug = ? OR name LIKE ? LIMIT 1");
    if ($stmtCat) {
        $catLike = "%" . $categoryParam . "%";
        $stmtCat->bind_param("ss", $categoryParam, $catLike);
        $stmtCat->execute();
        $catRow = $stmtCat->get_result()->fetch_assoc();
        if ($catRow) {
            $categoryId = (int)$catRow['id'];
        }
    }
    if ($categoryId === 0 && $search === '') {
        $search = $categoryParam;
    }
}

$sql    = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
$types  = "";

if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR c.name LIKE ? OR p.description LIKE ?)";
    $searchLike = "%" . $search . "%";
    $params[]   = $searchLike;
    $params[]   = $searchLike;
    $params[]   = $searchLike;
    $types     .= "sss";
}
if ($categoryId > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryId;
    $types   .= "i";
}
if ($sort === 'price_asc') {
    $sql .= " ORDER BY p.price ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY p.price DESC";
} else {
    $sql .= " ORDER BY p.id DESC";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Katalog Produk';
include 'includes/header.php';
?>

<div class="page-wrap">
<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="index.php">Beranda</a>
    <span class="sep">›</span>
    <span class="current">Katalog Produk</span>
</nav>

<div class="page-heading">
    <span class="eyebrow">OUR COLLECTION</span>
    <h1>Katalog Produk</h1>
    <p>Temukan produk favoritmu di Ayu Hanu'm Store.</p>
</div>

<!-- Filter bar -->
<form id="filterForm" method="GET" action="products.php" class="filter-bar">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari produk...">

    <select name="category" id="categorySelect">
        <option value="0">Semua Kategori</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="sort" id="sortSelect">
        <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Terbaru</option>
        <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Harga Terendah</option>
        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Harga Tertinggi</option>
    </select>

    <button type="submit" class="btn btn-primary" style="padding:8px 22px;">Cari</button>

    <span class="filter-count"><?= count($products) ?> produk ditemukan</span>
</form>

<!-- Product grid -->
<?php if (empty($products)): ?>
    <p style="padding:40px 0;color:#888;text-align:center;">Tidak ada produk yang ditemukan.</p>
<?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $prod): ?>
            <?php
                $imgSrc = '';
                if (!empty($prod['image'])) {
                    $imgSrc = (strpos($prod['image'], 'uploads/') === 0)
                        ? e($prod['image'])
                        : 'uploads/' . e($prod['image']);
                }
            ?>
            <div class="product-card">
                <a href="product.php?id=<?= (int)$prod['id'] ?>" class="product-img">
                    <?php if ($imgSrc): ?>
                        <img src="<?= $imgSrc ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="product-img-placeholder">👜</div>
                    <?php endif; ?>
                </a>
                <div class="product-info">
                    <div class="product-brand"><?= e($prod['category_name'] ?? "Ayu Hanu'm") ?></div>
                    <div class="product-name"><?= e($prod['name']) ?></div>
                    <div class="product-price"><?= rupiah($prod['price']) ?></div>
                </div>
                <div class="product-footer">
                    <span></span>
                    <a href="product.php?id=<?= (int)$prod['id'] ?>" class="btn-view">Lihat</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
document.getElementById('categorySelect').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
document.getElementById('sortSelect').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>
</div><!-- /.page-wrap -->

<?php include 'includes/footer.php'; ?>
