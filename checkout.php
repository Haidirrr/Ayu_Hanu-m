<?php
require_once 'config.php';
require_once 'includes/functions.php';

$items = cart_items($conn);

if (empty($items) && empty($_GET['done'])) {
    header('Location: cart.php');
    exit;
}

$shipping = [];
$resShip = $conn->query("SELECT * FROM shipping_methods ORDER BY cost ASC");
if ($resShip) $shipping = $resShip->fetch_all(MYSQLI_ASSOC);

$payments = [];
$resPay = $conn->query("SELECT * FROM payment_methods ORDER BY id ASC");
if ($resPay) $payments = $resPay->fetch_all(MYSQLI_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_GET['done'])) {
    $name     = trim($_POST['name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $location = trim($_POST['location_url'] ?? '');
    $shipId   = (int)($_POST['ship'] ?? 0);
    $payId    = (int)($_POST['pay'] ?? 0);
    $proofPath = '';
    $fullPath  = '';

    $stmtPayCheck = $conn->prepare("SELECT name FROM payment_methods WHERE id = ?");
    $stmtPayCheck->bind_param("i", $payId);
    $stmtPayCheck->execute();
    $payCheckRow = $stmtPayCheck->get_result()->fetch_assoc();
    $isCod = $payCheckRow && stripos($payCheckRow['name'], 'cod') !== false;

    if ($name === '' || $phone === '' || $address === '') {
        $error = 'Nama, nomor telepon, dan alamat wajib diisi.';
    } elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $error = 'Nomor telepon tidak valid.';
    } elseif ($location !== '' && !filter_var($location, FILTER_VALIDATE_URL)) {
        $error = 'Link lokasi harus berupa URL yang valid.';
    } elseif (!$isCod && (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK)) {
        $error = 'Silakan unggah bukti pembayaran.';
    } elseif (!$isCod && $_FILES['payment_proof']['size'] > (3 * 1024 * 1024)) {
        $error = 'Ukuran foto maksimal 3 MB.';
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if ($error === '' && !$isCod) {
        $tmpFile   = $_FILES['payment_proof']['tmp_name'];
        $imageInfo = @getimagesize($tmpFile);
        $mime      = $imageInfo['mime'] ?? '';
        if (!$imageInfo || !isset($allowed[$mime])) {
            $error = 'Bukti pembayaran harus berupa JPG, PNG, atau WEBP.';
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare("SELECT cost FROM shipping_methods WHERE id = ?");
        $stmt->bind_param("i", $shipId);
        $stmt->execute();
        $shipRow = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare("SELECT id FROM payment_methods WHERE id = ?");
        $stmt->bind_param("i", $payId);
        $stmt->execute();
        $payRow = $stmt->get_result()->fetch_assoc();

        if (!$shipRow || !$payRow) {
            $error = 'Pilih metode pengiriman dan pembayaran yang tersedia.';
        }
    }

    if ($error === '') {
        $subtotal  = (int)cart_subtotal($items);
        $shipCost  = (int)$shipRow['cost'];
        $total     = $subtotal + $shipCost;
        $code      = 'ELZ' . date('ymdHis') . random_int(100, 999);

        if (!$isCod) {
            $uploadDir = __DIR__ . '/uploads/payments/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileName  = 'bukti_' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
            $proofPath = 'uploads/payments/' . $fileName;
            $fullPath  = $uploadDir . $fileName;
            if (!move_uploaded_file($tmpFile, $fullPath)) {
                $error = 'Bukti pembayaran gagal diunggah ke server.';
            }
        }

        if ($error === '') {
            $conn->begin_transaction();
            try {
                $orderStatus = 'pending';
                $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_name, phone, address, location_url, shipping_id, payment_id, subtotal, shipping_cost, total, payment_proof, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssiiiiiss", $code, $name, $phone, $address, $location, $shipId, $payId, $subtotal, $shipCost, $total, $proofPath, $orderStatus);
                $stmt->execute();
                $orderId = $conn->insert_id;

                $detail = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, qty, color, size) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($items as $item) {
                    $productId   = (int)$item['id'];
                    $productName = $item['name'];
                    $price       = (int)$item['price'];
                    $qty         = (int)$item['qty'];
                    $color       = $item['sel_color'] ?? '';
                    $size        = $item['sel_size'] ?? '';
                    $detail->bind_param("iisiiss", $orderId, $productId, $productName, $price, $qty, $color, $size);
                    $detail->execute();
                }
                $conn->commit();
                $_SESSION['cart'] = [];
                header('Location: checkout.php?done=' . urlencode($code));
                exit;
            } catch (Throwable $ex) {
                $conn->rollback();
                if ($fullPath !== '' && file_exists($fullPath)) unlink($fullPath);
                $error = 'Pesanan gagal disimpan. Error: ' . $ex->getMessage();
            }
        }
    }
}

$pageTitle = 'Checkout';
include 'includes/header.php';
?>

<div class="page-wrap">
<?php if (!empty($_GET['done'])): ?>
    <?php
    $code = trim($_GET['done']);
    $stmt = $conn->prepare("SELECT o.*, p.name AS payment_name FROM orders o LEFT JOIN payment_methods p ON p.id = o.payment_id WHERE o.order_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    ?>

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="index.php">Keranjang Belanja</a>
        <span class="sep">›</span>
        <span>Rincian Transaksi</span>
        <span class="sep">›</span>
        <span class="current">Transaksi Selesai</span>
    </nav>

    <div style="max-width:580px;margin:30px auto;text-align:center;padding:44px 32px;border:1px solid #f3dce3;border-radius:16px;background:#fff;box-shadow:0 12px 34px rgba(220,120,150,0.1);">
        <?php if (!$order): ?>
            <h2>Pesanan tidak ditemukan.</h2>
            <a href="products.php" class="btn btn-outline-red" style="margin-top:20px;display:inline-block;">Kembali Belanja</a>
        <?php else: ?>
            <?php
            $isPaid = !empty($order['payment_proof']);
            $message = "Halo Ayu Hanu'm Store, saya telah melakukan pembayaran untuk pesanan:\nKode Pesanan: " . $order['order_code'] . "\nNama: " . $order['customer_name'] . "\nMetode: " . $order['payment_name'] . "\nTotal: " . rupiah($order['total']);
            $waAdmin = '6282190527576';
            $wa = "https://wa.me/" . $waAdmin . "?text=" . urlencode($message);
            ?>

            <!-- Icon Centang Hijau / Animasi Berhasil -->
            <div style="width:76px;height:76px;margin:0 auto 16px;background:#e8f8f0;border:2px solid #b7ecd0;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(46,204,113,0.2);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#27ae60" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <!-- Banner Notifikasi Pembayaran Berhasil -->
            <div style="background:#e8f8f0;border:1px solid #b7ecd0;color:#1e7e48;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;font-size:14px;display:inline-flex;align-items:center;gap:8px;">
                <span>✨</span> Pembayaran Berhasil Disimpan & Diterima!
            </div>

            <h1 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.85rem;font-weight:900;color:#2b141e;margin-bottom:8px;letter-spacing:-.02em;">Terima Kasih!</h1>
            <p style="color:#6d535d;font-size:14px;margin-bottom:24px;line-height:1.6;">
                Bukti pembayaran pesanan Anda telah berhasil diunggah. Pesanan Anda saat ini sedang kami proses untuk segera dikirimkan.
            </p>

            <div style="text-align:left;background:#fdf2f5;padding:20px;border-radius:12px;margin-bottom:24px;border:1px solid #f3dce3;">
                <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:13.5px;">
                    <span style="color:#666;">Kode Pesanan:</span>
                    <strong style="color:#2b141e;font-size:14px;letter-spacing:.03em;"><?= e($order['order_code']) ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:13.5px;">
                    <span style="color:#666;">Metode Pembayaran:</span>
                    <strong style="color:#2b141e;"><?= e($order['payment_name'] ?? 'QRIS') ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:13.5px;">
                    <span style="color:#666;">Total Tagihan:</span>
                    <strong style="color:#c4476c;font-size:16px;font-weight:900;"><?= rupiah($order['total']) ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:1px dashed #eed0d9;font-size:13.5px;align-items:center;">
                    <span style="color:#666;">Status Pesanan:</span>
                    <span style="background:#27ae60;color:#fff;font-size:11px;font-weight:800;padding:4px 10px;border-radius:20px;letter-spacing:.04em;text-transform:uppercase;">
                        <?= $isPaid ? 'Sudah Dibayar' : 'Menunggu Pembayaran' ?>
                    </span>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:12px;">
                <a class="btn btn-primary" href="<?= e($wa) ?>" target="_blank" rel="noopener" style="padding:14px 20px;font-size:14px;display:flex;align-items:center;justify-content:center;gap:8px;">
                    Konfirmasi ke WhatsApp Admin 📱
                </a>
                <a href="products.php" style="color:#6d535d;font-size:13px;font-weight:700;text-decoration:underline;">
                    Belanja Produk Lainnya →
                </a>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <?php 
    $subtotal = (int)cart_subtotal($items); 
    $defaultShippingCost = !empty($shipping) ? (int)$shipping[0]['cost'] : 0;
    $initialTotal = $subtotal + $defaultShippingCost;
    ?>

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="cart.php">Keranjang Belanja</a>
        <span class="sep">›</span>
        <span class="current">Rincian Transaksi</span>
        <span class="sep">›</span>
        <span>Transaksi Selesai</span>
    </nav>

    <div class="page-heading">
        <h1>Checkout</h1>
        <p>Lengkapi data pengiriman dan pembayaran pesanan Anda.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div style="background:#ffebee;color:#c62828;padding:14px;border-radius:4px;margin-bottom:25px;border:1px solid #ffcdd2;font-size:13px;">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="display:grid;grid-template-columns:2fr 1fr;gap:30px;align-items:start;">
        
        <!-- Kolom Kiri -->
        <div>
            <div style="background:#fff;border:1px solid #e0e0e0;border-radius:4px;padding:24px;margin-bottom:20px;">
                <h3 style="margin:0 0 16px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e0e0e0;padding-bottom:10px;">1. Informasi Pelanggan</h3>
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Nama Penerima</label>
                <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:14px;font-size:14px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Nomor Telepon / WhatsApp</label>
                <input type="tel" name="phone" required placeholder="081234567890" value="<?= e($_POST['phone'] ?? '') ?>" style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:14px;font-size:14px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Alamat Lengkap</label>
                <textarea name="address" rows="3" required placeholder="Nama jalan, nomor rumah, kecamatan, kota" style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:14px;font-size:14px;resize:vertical;"><?= e($_POST['address'] ?? '') ?></textarea>
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Link Google Maps (Opsional)</label>
                <input type="url" name="location_url" placeholder="https://goo.gl/maps/..." value="<?= e($_POST['location_url'] ?? '') ?>" style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:4px;font-size:14px;">
            </div>

            <div style="background:#fff;border:1px solid #e0e0e0;border-radius:4px;padding:24px;margin-bottom:20px;">
                <h3 style="margin:0 0 16px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e0e0e0;padding-bottom:10px;">2. Metode Pengiriman</h3>
                <?php foreach ($shipping as $index => $ship): ?>
                    <label style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:10px;cursor:pointer;background:#fafafa;">
                        <span style="display:flex;align-items:center;gap:10px;">
                            <input type="radio" name="ship" value="<?= (int)$ship['id'] ?>" data-cost="<?= (int)$ship['cost'] ?>" <?= $index === 0 ? 'checked' : '' ?> required>
                            <strong style="font-size:13px;"><?= e($ship['name']) ?></strong>
                            <span style="color:#888;font-size:12px;">(<?= e($ship['eta']) ?>)</span>
                        </span>
                        <span style="font-weight:700;color:#8b1a2f;font-size:13px;"><?= rupiah($ship['cost']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div style="background:#fff;border:1px solid #e0e0e0;border-radius:4px;padding:24px;">
                <h3 style="margin:0 0 16px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e0e0e0;padding-bottom:10px;">3. Metode Pembayaran</h3>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach ($payments as $index => $pay): ?>
                        <label style="display:flex;align-items:center;padding:12px 14px;border:1px solid #e0e0e0;border-radius:4px;cursor:pointer;background:#fafafa;gap:10px;">
                            <input type="radio" name="pay" value="<?= (int)$pay['id'] ?>" 
                                   data-name="<?= e(strtolower($pay['name'])) ?>"
                                   data-detail="<?= e($pay['detail'] ?? '') ?>"
                                   data-account-name="<?= e($pay['account_name'] ?? '') ?>"
                                   data-account-number="<?= e($pay['account_number'] ?? '') ?>"
                                   data-qr="<?= e($pay['qr_image'] ?? '') ?>"
                                   <?= $index === 0 ? 'checked' : '' ?> required>
                            <strong style="font-size:13px;"><?= e($pay['name']) ?></strong>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div id="paymentProofWrapper">
                    <div id="paymentInfoBox" style="background:#fdf2f5;padding:18px;border-radius:8px;margin-top:16px;border:1px solid #f3dce3;">
                        <p id="paymentDetail" style="margin:0 0 6px;font-weight:700;color:#2b141e;font-size:13.5px;"></p>
                        <p id="accountName" style="margin:0 0 4px;font-size:13px;color:#4a4a52;"></p>
                        <p id="accountNumber" style="margin:0 0 8px;font-size:13px;color:#4a4a52;"></p>
                        
                        <!-- Box Tampilan QRIS Dinamis -->
                        <div id="qrisContainer" style="display:none;background:#fff;border:1.5px dashed #f28ca7;border-radius:10px;padding:16px;text-align:center;margin:12px 0;max-width:260px;">
                            <div style="font-size:12px;font-weight:800;letter-spacing:.05em;color:#2b141e;margin-bottom:8px;text-transform:uppercase;">
                                QRIS AYU HANU'M STORE
                            </div>
                            <img id="qrImage" src="QRIS.jpeg" alt="QRIS Code" style="display:block;width:100%;max-width:200px;margin:0 auto;border-radius:6px;border:1px solid #eee;">
                            <small style="display:block;margin-top:8px;color:#888;font-size:11px;">Scan menggunakan GoPay, OVO, DANA, BCA, ShopeePay, atau Mobile Banking apa saja</small>
                        </div>

                        <p id="paymentNote" style="margin:8px 0 0;font-size:12px;color:#777;"></p>
                    </div>
                    <div style="margin-top:20px;">
                        <label style="display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Upload Bukti Pembayaran</label>
                        <input type="file" id="paymentProofInput" name="payment_proof" accept="image/jpeg,image/png,image/webp" style="width:100%;padding:8px;border:1px solid #e0e0e0;border-radius:4px;background:#fff;font-size:13px;">
                        <small style="color:#888;display:block;margin-top:4px;">Format: JPG, PNG, WEBP (Maks. 3 MB)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan -->
        <div>
            <div class="cart-summary" style="position:sticky;top:80px;">
                <h3>Ringkasan Belanja</h3>
                <div class="summary-row"><span>Subtotal Produk:</span><strong><?= rupiah($subtotal) ?></strong></div>
                <div class="summary-row"><span>Ongkos Kirim:</span><strong id="shippingCostLabel"><?= rupiah($defaultShippingCost) ?></strong></div>
                <div class="summary-total">
                    <span>Total Tagihan:</span>
                    <span class="price" id="grandTotal"><?= rupiah($initialTotal) ?></span>
                </div>
                <button class="btn btn-primary" type="submit">Buat Pesanan Sekarang 🛍️</button>
            </div>
        </div>

    </form>

    <script>
    const subtotal = <?= $subtotal ?>;
    function updateShipping() {
        const sel = document.querySelector('input[name="ship"]:checked');
        const cost = sel ? parseInt(sel.dataset.cost || 0) : 0;
        document.getElementById('shippingCostLabel').textContent = 'Rp ' + cost.toLocaleString('id-ID');
        document.getElementById('grandTotal').textContent = 'Rp ' + (subtotal + cost).toLocaleString('id-ID');
    }
    function updatePayment() {
        const sel = document.querySelector('input[name="pay"]:checked');
        if (!sel) return;
        const wrapper = document.getElementById('paymentProofWrapper');
        const proofInput = document.getElementById('paymentProofInput');
        if (sel.dataset.name.includes('cod')) {
            wrapper.style.display = 'none';
            if (proofInput) proofInput.removeAttribute('required');
        } else {
            wrapper.style.display = 'block';
            if (proofInput) proofInput.setAttribute('required', 'required');
            document.getElementById('paymentDetail').textContent = sel.dataset.detail || '';
            document.getElementById('accountName').textContent = sel.dataset.accountName ? 'Nama Pemilik: ' + sel.dataset.accountName : '';
            document.getElementById('accountNumber').textContent = sel.dataset.accountNumber ? 'Nomor: ' + sel.dataset.accountNumber : '';
            
            const qrisBox = document.getElementById('qrisContainer');
            const qr = document.getElementById('qrImage');
            const isQris = sel.dataset.name.includes('qris');

            if (sel.dataset.qr) {
                qr.src = sel.dataset.qr;
                if (qrisBox) qrisBox.style.display = 'block';
            } else if (isQris) {
                const totalText = document.getElementById('grandTotal') ? document.getElementById('grandTotal').textContent : 'STORE';
                qr.src = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=AYU-HANUM-QRIS-' + encodeURIComponent(totalText);
                if (qrisBox) qrisBox.style.display = 'block';
            } else {
                qr.removeAttribute('src');
                if (qrisBox) qrisBox.style.display = 'none';
            }
            document.getElementById('paymentNote').textContent = 'Pastikan nominal transfer sesuai dengan total tagihan.';
        }
    }
    document.querySelectorAll('input[name="ship"]').forEach(el => el.addEventListener('change', updateShipping));
    document.querySelectorAll('input[name="pay"]').forEach(el => el.addEventListener('change', updatePayment));
    updateShipping(); updatePayment();
    </script>

<?php endif; ?>
</div><!-- /.page-wrap -->

<?php include 'includes/footer.php'; ?>
