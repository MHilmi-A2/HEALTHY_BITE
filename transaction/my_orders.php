<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
if (($_SESSION['role'] ?? '') !== 'customer') {
    redirect_with_flash('Halaman ini hanya untuk pelanggan.', 'error', 'index.php');
}

$statement = db()->prepare(
    "SELECT o.id, o.created_at, o.total, o.status,
        GROUP_CONCAT(CONCAT(oi.menu_name, ' x', oi.quantity) ORDER BY oi.id SEPARATOR ', ') AS items
     FROM tb_orders o
     JOIN tb_order_items oi ON oi.order_id = o.id
     WHERE o.user_id = :user_id
     GROUP BY o.id, o.created_at, o.total, o.status
     ORDER BY o.created_at DESC"
);
$statement->execute(['user_id' => (int) $_SESSION['user_id']]);
$orders = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pesanan Saya - Healthy Bite</title><link rel="stylesheet" href="../css/admin.css"><link rel="stylesheet" href="../css/toast.css"></head>
<body>
<?php render_flash_toast(); ?>
<main class="dashboard-content">
    <h1>Pesanan Saya</h1>
    <p><a href="../Menu.php">Kembali ke menu</a></p>
    <div class="table-container table-responsive">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Waktu</th><th>Item</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php if ($orders === []): ?><tr><td colspan="5">Belum ada pesanan.</td></tr>
            <?php else: foreach ($orders as $order): ?>
                <tr><td>#<?= (int) $order['id'] ?></td><td><?= e($order['created_at']) ?></td><td><?= e($order['items']) ?></td><td>Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></td><td><?= e($order['status']) ?></td></tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
