<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_admin();

$connection = db();
$stats = [
    'revenue' => $connection->query("SELECT COALESCE(SUM(total), 0) FROM tb_orders WHERE status <> 'Dibatalkan'")->fetchColumn(),
    'orders' => $connection->query('SELECT COUNT(*) FROM tb_orders WHERE DATE(created_at) = CURDATE()')->fetchColumn(),
    'customers' => $connection->query("SELECT COUNT(*) FROM tb_users WHERE status = 'Aktif'")->fetchColumn(),
];
$recentOrders = $connection->query(
    'SELECT o.id, o.created_at, u.name, o.total, o.status
     FROM tb_orders o JOIN tb_users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 10'
)->fetchAll();

admin_header('Dashboard Admin', 'dashboard');
?>
<div class="cards-wrapper">
    <div class="card-stat"><div class="card-info"><h3>Total Nilai Pesanan (non-batal)</h3><p>Rp <?= number_format((float) $stats['revenue'], 0, ',', '.') ?></p></div></div>
    <div class="card-stat"><div class="card-info"><h3>Pesanan Hari Ini</h3><p><?= (int) $stats['orders'] ?> Pesanan</p></div></div>
    <div class="card-stat"><div class="card-info"><h3>Total Pelanggan Aktif</h3><p><?= (int) $stats['customers'] ?> Orang</p></div></div>
</div>
<div class="table-container">
    <div class="table-header"><h3>Pesanan Terbaru</h3><a class="btn-primary" href="<?= e(app_url('transaction/transaction.php')) ?>">Semua Pesanan</a></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>ID Pesanan</th><th>Tanggal</th><th>Pelanggan</th><th>Total Bayar</th><th>Status</th></tr></thead>
            <tbody>
            <?php if ($recentOrders === []): ?>
                <tr><td colspan="5">Belum ada pesanan.</td></tr>
            <?php else: foreach ($recentOrders as $order): ?>
                <tr>
                    <td>#<?= (int) $order['id'] ?></td>
                    <td><?= e($order['created_at']) ?></td>
                    <td><?= e($order['name']) ?></td>
                    <td>Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></td>
                    <td><?= e($order['status']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_footer(); ?>
