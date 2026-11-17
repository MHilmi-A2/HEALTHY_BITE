<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$search = trim(request_string($_GET, 'search'));
$connection = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    $status = request_string($_POST, 'status');
    if ($userId === false || $userId < 1 || !in_array($status, ['Aktif', 'Non-aktif'], true)) {
        redirect_with_flash('Data pelanggan tidak valid.', 'error', 'admin/admin_pelanggan.php');
    }

    $statement = $connection->prepare('UPDATE tb_users SET status = :status WHERE id = :id');
    $statement->execute(['status' => $status, 'id' => $userId]);
    set_flash('Status akun pelanggan berhasil diperbarui.', 'success');
    header('Location: admin_pelanggan.php');
    exit;
}

$statement = $connection->prepare(
    "SELECT u.id, u.name, u.email, u.created_at, u.status, COUNT(o.id) AS order_count
     FROM tb_users u LEFT JOIN tb_orders o ON o.user_id = u.id
     WHERE u.name LIKE :name_search OR u.email LIKE :email_search
     GROUP BY u.id, u.name, u.email, u.created_at, u.status
     ORDER BY u.created_at DESC"
);
$searchTerm = '%' . $search . '%';
$statement->execute(['name_search' => $searchTerm, 'email_search' => $searchTerm]);
$customers = $statement->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="pelanggan.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Nama', 'Email', 'Tanggal Bergabung', 'Total Pesanan', 'Status']);
    foreach ($customers as $customer) {
        fputcsv($output, [
            $customer['id'],
            $customer['name'],
            $customer['email'],
            $customer['created_at'],
            $customer['order_count'],
            $customer['status'],
        ]);
    }
    fclose($output);
    exit;
}

admin_header('Data Pelanggan', 'customers');
?>
<div class="content-header">
    <h3>Daftar Member Healthy Bite</h3>
    <div class="action-bar">
        <form class="action-bar" method="get">
            <input class="search-box" name="search" value="<?= e($search) ?>" placeholder="Cari nama / email...">
            <button class="btn-primary" type="submit">Cari</button>
        </form>
        <a class="btn-primary" href="?<?= http_build_query(['search' => $search, 'export' => 'csv']) ?>"><i class="fa-solid fa-download"></i> Export CSV</a>
    </div>
</div>
<div class="table-container table-responsive">
    <table class="data-table">
        <thead><tr><th>ID Member</th><th>Nama Lengkap</th><th>Email</th><th>Tanggal Bergabung</th><th>Total Transaksi</th><th>Status Akun</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if ($customers === []): ?><tr><td colspan="7">Pelanggan tidak ditemukan.</td></tr>
        <?php else: foreach ($customers as $customer): ?>
            <tr>
                <td>#USR-<?= (int) $customer['id'] ?></td>
                <td><?= e($customer['name']) ?></td>
                <td><?= e($customer['email']) ?></td>
                <td><?= e($customer['created_at']) ?></td>
                <td><?= (int) $customer['order_count'] ?> Pesanan</td>
                <td><?= e($customer['status']) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int) $customer['id'] ?>">
                        <input type="hidden" name="status" value="<?= $customer['status'] === 'Aktif' ? 'Non-aktif' : 'Aktif' ?>">
                        <button class="btn-action <?= $customer['status'] === 'Aktif' ? 'btn-del' : 'btn-acc' ?>" type="submit" title="<?= $customer['status'] === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>">
                            <i class="fa-solid <?= $customer['status'] === 'Aktif' ? 'fa-ban' : 'fa-check-rotate' ?>"></i>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php admin_footer(); ?>
