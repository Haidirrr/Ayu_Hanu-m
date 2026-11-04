<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Proses update status pesanan jika tombol diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['status']);
    
    $allowedStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    if (in_array($newStatus, $allowedStatuses)) {
        // Ambil data pesanan termasuk nomor HP & nama customer sebelum di-update
        $stmtInfo = $conn->prepare("SELECT order_code, customer_name, phone FROM orders WHERE id = ?");
        $stmtInfo->bind_param("i", $orderId);
        $stmtInfo->execute();
        $orderData = $stmtInfo->get_result()->fetch_assoc();

        // Update status di database
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();

        // --- KIRIM OTOMATIS WHATSAPP KE NOMOR CUSTOMER ---
        if ($orderData) {
            $customerPhone = $orderData['phone'];
            // Format nomor HP ke standar internasional (62)
            $customerPhone = preg_replace('/[^0-9]/', '', $customerPhone);
            if (substr($customerPhone, 0, 1) === '0') {
                $customerPhone = '62' . substr($customerPhone, 1);
            }

            // Teks pesan berdasarkan status baru
            $statusText = [
                'pending' => 'Menunggu Pembayaran',
                'processing' => 'Sedang Diproses / Dikemas',
                'shipped' => 'Dalam Pengiriman (Kurir)',
                'completed' => 'Selesai / Diterima',
                'cancelled' => 'Dibatalkan'
            ];
            $readableStatus = $statusText[$newStatus] ?? $newStatus;

            $waMessage = "Halo Kak *{$orderData['customer_name']}*,\n\n" .
                         "Update status pesanan Anda (*{$orderData['order_code']}*) saat ini adalah: *{$readableStatus}*.\n\n" .
                         "Terima kasih telah berbelanja di Ayu Hanu'm Store! ✨";
        }
    }
    header('Location: admin_orders.php');
    exit;
}

// Ambil data pesanan dari database
$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Admin - Kelola Pesanan';
include 'includes/header.php';
?>

<div class="page-wrap">
    <!-- Header Admin & Nav Tabs -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 18px;">
        <div>
            <span style="font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--orange);">ADMIN PANEL</span>
            <h1 style="font-size: 2rem; font-weight: 900; margin: 4px 0 0; color: #111;">Kelola Pesanan</h1>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="admin.php" class="btn btn-outline" style="font-size: 12.5px; padding: 9px 16px;">
                📊 Dashboard
            </a>
            <a href="admin_orders.php" class="btn btn-primary" style="font-size: 12.5px; padding: 9px 16px;">
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

    <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm);">
        <p style="color: var(--gray); margin-bottom: 24px; font-size: 13.5px;">
            Kelola dan perbarui status pesanan pelanggan. Klik nomor telepon pelanggan untuk langsung membuka chat WhatsApp.
        </p>

        <?php if (empty($orders)): ?>
            <div style="padding: 40px; text-align: center;">
                <p style="color: var(--gray); margin: 0;">Belum ada pesanan yang masuk.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total Tagihan</th>
                            <th>Bukti Bayar</th>
                            <th>Status Saat Ini</th>
                            <th>Aksi / Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td style="padding: 14px; vertical-align: top; font-weight: 800; font-size: 13px;">
                                    <?= htmlspecialchars($ord['order_code']) ?>
                                </td>
                                <td style="padding: 14px; vertical-align: top;">
                                    <strong><?= htmlspecialchars($ord['customer_name']) ?></strong><br>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $ord['phone']) ?>" target="_blank" rel="noopener" style="color: #2563eb; font-weight: 600;">
                                        <?= htmlspecialchars($ord['phone']) ?> 📱
                                    </a><br>
                                    <small style="color: var(--gray);"><?= htmlspecialchars($ord['address']) ?></small>
                                </td>
                                <td style="padding: 14px; vertical-align: top; font-weight: 800;">
                                    <?= rupiah($ord['total']) ?>
                                </td>
                                <td style="padding: 14px; vertical-align: top;">
                                    <?php if (!empty($ord['payment_proof'])): ?>
                                        <a href="<?= htmlspecialchars($ord['payment_proof']) ?>" target="_blank" rel="noopener" style="color: #2563eb; font-weight: 700; text-decoration: underline;">
                                            Lihat Foto Bukti
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">COD / Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px; vertical-align: top;">
                                    <?php 
                                    $status = $ord['status'] ?? 'pending';
                                    $statusBadge = [
                                        'pending'    => '<span style="background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Menunggu Pembayaran</span>',
                                        'processing' => '<span style="background:#e0f2fe; color:#0369a1; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Diproses / Dikemas</span>',
                                        'shipped'    => '<span style="background:#e0e7ff; color:#3730a3; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Dalam Pengiriman</span>',
                                        'completed'  => '<span style="background:#dcfce7; color:#15803d; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Selesai / Diterima</span>',
                                        'done'       => '<span style="background:#dcfce7; color:#15803d; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Selesai / Diterima</span>',
                                        'cancelled'  => '<span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Dibatalkan</span>'
                                    ];
                                    echo $statusBadge[$status] ?? '<span style="background:#eee; padding:4px 8px; border-radius:4px; font-size:11px;">' . htmlspecialchars($status) . '</span>';
                                    ?>
                                </td>
                                <td style="padding: 14px; vertical-align: top;">
                                    <form method="POST" style="display: flex; gap: 6px; align-items: center;">
                                        <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                        <select name="status" style="padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border); font-size: 12px; background: #fff;">
                                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Diproses</option>
                                            <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Dikirim (Kurir)</option>
                                            <option value="completed" <?= in_array($status, ['completed', 'done']) ? 'selected' : '' ?>>Selesai</option>
                                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Batal</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary" style="padding: 6px 14px; font-size: 11px;">Simpan</button>
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
