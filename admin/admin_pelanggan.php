<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$connection = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = request_string($_POST, 'action');
    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);

    if ($action === 'delete') {
        if ($userId === false || $userId < 1) {
            redirect_with_flash('ID pelanggan tidak valid.', 'error', 'admin/admin_pelanggan.php');
        }

        try {
            $statement = $connection->prepare('DELETE FROM tb_users WHERE id = :id');
            $statement->execute(['id' => $userId]);
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                redirect_with_flash('Pelanggan tidak dapat dihapus karena memiliki riwayat pesanan.', 'error', 'admin/admin_pelanggan.php');
            }
            throw $error;
        }

        set_flash('Pelanggan berhasil dihapus.', 'success');
        header('Location: admin_pelanggan.php');
        exit;
    }

    if (!in_array($action, ['create', 'update'], true)) {
        redirect_with_flash('Aksi pelanggan tidak dikenal.', 'error', 'admin/admin_pelanggan.php');
    }

    $name = trim(request_string($_POST, 'name'));
    $email = strtolower(trim(request_string($_POST, 'email')));
    $password = request_string($_POST, 'password');
    $status = request_string($_POST, 'status');

    if (
        ($action === 'update' && ($userId === false || $userId < 1)) ||
        $name === '' || mb_strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($status, ['Aktif', 'Non-aktif'], true) ||
        ($action === 'create' && strlen($password) < 8) ||
        ($password !== '' && strlen($password) < 8)
    ) {
        redirect_with_flash('Periksa nama, email, status, dan password (minimal 8 karakter; kosongkan password saat edit jika tidak ingin menggantinya).', 'error', 'admin/admin_pelanggan.php');
    }

    $adminEmail = $connection->prepare('SELECT 1 FROM tb_admin WHERE email = :email');
    $adminEmail->execute(['email' => $email]);
    if ($adminEmail->fetchColumn()) {
        redirect_with_flash('Email sudah digunakan oleh akun admin.', 'error', 'admin/admin_pelanggan.php');
    }

    try {
        if ($action === 'create') {
            $statement = $connection->prepare(
                'INSERT INTO tb_users (name, email, password, status)
                 VALUES (:name, :email, :password, :status)'
            );
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'status' => $status,
            ]);
        } elseif ($password === '') {
            $statement = $connection->prepare(
                'UPDATE tb_users SET name = :name, email = :email, status = :status WHERE id = :id'
            );
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'status' => $status,
                'id' => $userId,
            ]);
        } else {
            $statement = $connection->prepare(
                'UPDATE tb_users
                 SET name = :name, email = :email, password = :password, status = :status
                 WHERE id = :id'
            );
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'status' => $status,
                'id' => $userId,
            ]);
        }
    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            redirect_with_flash('Email sudah terdaftar. Gunakan alamat email lain.', 'error', 'admin/admin_pelanggan.php');
        }
        throw $error;
    }

    set_flash($action === 'create' ? 'Pelanggan berhasil ditambahkan.' : 'Data pelanggan berhasil diperbarui.', 'success');
    header('Location: admin_pelanggan.php');
    exit;
}

$search = trim(request_string($_GET, 'search'));
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
<div class="table-container menu-form">
    <h3>Tambah Pelanggan</h3>
    <form method="post" class="data-form menu-create-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">
        <label>Nama lengkap<input name="name" required maxlength="100"></label>
        <label>Email<input name="email" type="email" required maxlength="100"></label>
        <label>Password awal<input name="password" type="password" required minlength="8" autocomplete="new-password"></label>
        <label>Status
            <select name="status">
                <option value="Aktif">Aktif</option>
                <option value="Non-aktif">Non-aktif</option>
            </select>
        </label>
        <button class="btn-primary" type="submit">Tambah Pelanggan</button>
    </form>
</div>
<div class="content-header">
    <h3>Daftar Member Healthy Bite</h3>
    <div class="action-bar">
        <form class="action-bar" method="get">
            <input class="search-box" name="search" value="<?= e($search) ?>" placeholder="Cari nama / email...">
            <button class="btn-primary" type="submit">Cari</button>
        </form>
        <a class="btn-primary" href="?<?= http_build_query(['search' => $search, 'export' => 'csv']) ?>"><i class="fa-solid fa-download"></i> Export CSV</a>
        <a class="btn-primary" href="<?= e(app_url('admin/cetak_pelanggan.php')) ?>"><i class="fa-solid fa-file-pdf"></i> Cetak PDF</a>
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
                <td>
                    <form id="update-customer-<?= (int) $customer['id'] ?>" method="post"></form>
                    <input form="update-customer-<?= (int) $customer['id'] ?>" type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input form="update-customer-<?= (int) $customer['id'] ?>" type="hidden" name="action" value="update">
                    <input form="update-customer-<?= (int) $customer['id'] ?>" type="hidden" name="user_id" value="<?= (int) $customer['id'] ?>">
                    <label>Nama<input form="update-customer-<?= (int) $customer['id'] ?>" name="name" required maxlength="100" value="<?= e($customer['name']) ?>"></label>
                </td>
                <td><label>Email<input form="update-customer-<?= (int) $customer['id'] ?>" name="email" type="email" required maxlength="100" value="<?= e($customer['email']) ?>"></label></td>
                <td><?= e($customer['created_at']) ?></td>
                <td><?= (int) $customer['order_count'] ?> Pesanan</td>
                <td>
                    <select form="update-customer-<?= (int) $customer['id'] ?>" name="status" aria-label="Status akun pelanggan">
                        <option value="Aktif" <?= $customer['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Non-aktif" <?= $customer['status'] === 'Non-aktif' ? 'selected' : '' ?>>Non-aktif</option>
                    </select>
                    <label>Password baru (opsional)<input form="update-customer-<?= (int) $customer['id'] ?>" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Kosongkan jika tetap"></label>
                </td>
                <td>
                    <button form="update-customer-<?= (int) $customer['id'] ?>" class="btn-primary" type="submit">Simpan</button>
                    <form method="post" class="inline-form" onsubmit="return confirm('Hapus pelanggan ini? Pelanggan dengan riwayat pesanan tidak dapat dihapus.')">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?= (int) $customer['id'] ?>">
                        <button class="btn-action btn-del" type="submit" title="Hapus pelanggan"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php admin_footer(); ?>
