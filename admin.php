<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Proses update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    $allowed = ['pending', 'processing', 'shipped', 'done', 'completed', 'cancelled'];

    if (in_array($status, $allowed, true) && $id > 0) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }

    header('Location: admin.php');
    exit;
}

// Statistik
$totalOrders = (int)($conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'] ?? 0);

$revenue = (int)($conn->query("SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE status IN ('paid','shipped','done','completed')")->fetch_assoc()['total'] ?? 0);

$pendingOrders = (int)($conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0);

$shippedOrders = (int)($conn->query("SELECT COUNT(*) AS total FROM orders WHERE status IN ('shipped','done','completed')")->fetch_assoc()['total'] ?? 0);

$orders = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard Admin';
include 'includes/header.php';
?>

<div class="page-wrap">
    <!-- Header Admin & Nav Tabs -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 18px;">
        <div>
            <span style="font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--orange);">ADMIN PANEL</span>
            <h1 style="font-size: 2rem; font-weight: 900; margin: 4px 0 0; color: #111;">Dashboard Admin</h1>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="admin.php" class="btn btn-primary" style="font-size: 12.5px; padding: 9px 16px;">
                📊 Dashboard
            </a>
            <a href="admin_orders.php" class="btn btn-outline" style="font-size: 12.5px; padding: 9px 16px;">
                📦 Kelola Semua Pesanan
            </a>
            <a href="products.php" class="btn btn-outline" style="font-size: 12.5px; padding: 9px 16px;">
                🛍️ Katalog Produk
            </a>
            <a href="index.php" class="btn btn-outline" style="font-size: 12.5px; padding: 9px 16px;">
                🏪 Lihat Toko &rarr;
            </a>
        </div>
    </div>

    <!-- 4 Kartu Statistik -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 32px;">
        <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow-sm);">
            <div style="font-size: 12px; font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px;">Total Pesanan</div>
            <div style="font-size: 2.2rem; font-weight: 900; color: #111;"><?= $totalOrders ?></div>
            <small style="color: #888;">Semua transaksi masuk</small>
        </div>

        <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow-sm);">
            <div style="font-size: 12px; font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px;">Pendapatan Masuk</div>
            <div style="font-size: 1.8rem; font-weight: 900; color: #16a34a;"><?= rupiah($revenue) ?></div>
            <small style="color: #888;">Pesanan terverifikasi</small>
        </div>

        <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow-sm);">
            <div style="font-size: 12px; font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px;">Menunggu Pembayaran</div>
            <div style="font-size: 2.2rem; font-weight: 900; color: #d97706;"><?= $pendingOrders ?></div>
            <small style="color: #888;">Perlu konfirmasi / bukti</small>
        </div>

        <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow-sm);">
            <div style="font-size: 12px; font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px;">Terkirim / Selesai</div>
            <div style="font-size: 2.2rem; font-weight: 900; color: #2563eb;"><?= $shippedOrders ?></div>
            <small style="color: #888;">Pesanan terkirim / tuntas</small>
        </div>
    </div>

    <!-- Tabel Pesanan Terbaru -->
    <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; margin: 0;">Pesanan Terbaru</h2>
            <a href="admin_orders.php" style="font-size: 13px; font-weight: 700; color: #111;">Buka Kelola Lengkap &rarr;</a>
        </div>

        <?php if (empty($orders)): ?>
            <p style="color: var(--gray); text-align: center; padding: 40px 0;">Belum ada pesanan yang tercatat.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>No. Telp</th>
                            <th>Total</th>
                            <th>Bukti Bayar</th>
                            <th>Status</th>
                            <th>Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td style="font-weight: 800; font-size: 13px;">
                                    <?= e($order['order_code']) ?>
                                </td>
                                <td>
                                    <strong><?= e($order['customer_name']) ?></strong>
                                    <div style="font-size: 11px; color: var(--gray);"><?= e($order['address']) ?></div>
                                </td>
                                <td>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $order['phone']) ?>" target="_blank" rel="noopener" style="color: #2563eb; font-weight: 600;">
                                        <?= e($order['phone']) ?> 📱
                                    </a>
                                </td>
                                <td style="font-weight: 800;">
                                    <?= rupiah($order['total']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($order['payment_proof'])): ?>
                                        <a href="<?= e($order['payment_proof']) ?>" target="_blank" rel="noopener" style="color: #2563eb; font-weight: 700; text-decoration: underline;">
                                            Lihat Foto
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">COD / Kosong</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $st = $order['status'] ?? 'pending';
                                    $badges = [
                                        'pending'    => '<span style="background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:4px; font-weight:700; font-size:11px;">Pending</span>',
                                        'processing' => '<span style="background:#e0f2fe; color:#0369a1; padding:4px 8px; border-radius:4px; font-weight:700; font-size:11px;">Diproses</span>',
                                        'paid'       => '<span style="background:#dcfce7; color:#15803d; padding:4px 8px; border-radius:4px; font-weight:700; font-size:11px;">Dibayar</span>',
                                        'shipped'    => '<span style="background:#e0e7ff; color:#3730a3; padding:4px 8px; border-radius:4px; font-weight:700; font-size:11px;">Dikirim</span>',
                                        'done'       => '<span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-weight:700; font-size:11px;">Selesai</span>',
                                        'completed'  => '<span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-weight:700; font-size:11px;">Selesai</span>',
                                        'cancelled'  => '<span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-weight:700; font-size:11px;">Batal</span>',
                                    ];
                                    echo $badges[$st] ?? '<span style="background:#f3f4f6; padding:4px 8px; border-radius:4px; font-size:11px;">' . e($st) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" action="admin.php" style="display: flex; gap: 6px; align-items: center;">
                                        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
                                        <select name="status" style="padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border); font-size: 12px; background: #fff;">
                                            <option value="pending" <?= $st === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="processing" <?= $st === 'processing' ? 'selected' : '' ?>>Diproses</option>
                                            <option value="shipped" <?= $st === 'shipped' ? 'selected' : '' ?>>Dikirim</option>
                                            <option value="completed" <?= in_array($st, ['completed', 'done']) ? 'selected' : '' ?>>Selesai</option>
                                            <option value="cancelled" <?= $st === 'cancelled' ? 'selected' : '' ?>>Batal</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 11px;">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
