<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$allowedStatuses = ['Menunggu Konfirmasi', 'Diproses', 'Selesai', 'Dibatalkan'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    if ($orderId === false || $orderId < 1 || !in_array($status, $allowedStatuses, true)) {
        redirect_with_flash('ID pesanan atau status tidak valid.', 'error', 'transaction/transaction.php');
    }

    $statement = db()->prepare('UPDATE tb_orders SET status = :status WHERE id = :id');
    $statement->execute(['status' => $status, 'id' => $orderId]);
    set_flash('Status pesanan berhasil diperbarui.', 'success');
    header('Location: transaction.php');
    exit;
}

$orders = db()->query(
    "SELECT o.id, o.created_at, o.phone, o.address, o.total, o.status, u.name, u.email,
        GROUP_CONCAT(CONCAT(oi.menu_name, ' x', oi.quantity) ORDER BY oi.id SEPARATOR ', ') AS items
     FROM tb_orders o
     JOIN tb_users u ON u.id = o.user_id
     JOIN tb_order_items oi ON oi.order_id = o.id
     GROUP BY o.id, o.created_at, o.phone, o.address, o.total, o.status, u.name, u.email
     ORDER BY o.created_at DESC"
)->fetchAll();

admin_header('Data Pesanan', 'orders');
?>
<div class="table-container">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>ID / Waktu</th><th>Pelanggan</th><th>Kontak / Alamat</th><th>Item</th><th>Total</th><th>Status</th><th>Perbarui</th></tr></thead>
            <tbody>
            <?php if ($orders === []): ?>
                <tr><td colspan="7">Belum ada pesanan.</td></tr>
            <?php else: foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= (int) $order['id'] ?><br><?= e($order['created_at']) ?></td>
                    <td><?= e($order['name']) ?><br><?= e($order['email']) ?></td>
                    <td><?= e($order['phone']) ?><br><?= e($order['address']) ?></td>
                    <td><?= e($order['items']) ?></td>
                    <td>Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></td>
                    <td><?= e($order['status']) ?></td>
                    <td>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                            <select name="status" aria-label="Status pesanan">
                                <?php foreach ($allowedStatuses as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-primary" type="submit">Simpan</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_footer(); ?>
