<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$connection = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = request_string($_POST, 'action');

    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            redirect_with_flash('ID menu tidak valid.', 'error', 'categories/categories.php');
        }

        try {
            $statement = $connection->prepare('DELETE FROM tb_categories WHERE id = :id');
            $statement->execute(['id' => $id]);
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                redirect_with_flash('Menu tidak dapat dihapus karena sudah dipakai pada pesanan.', 'error', 'categories/categories.php');
            }
            throw $error;
        }
        set_flash('Menu berhasil dihapus.', 'success');
        header('Location: categories.php');
        exit;
    }

    if (!in_array($action, ['create', 'update'], true)) {
        redirect_with_flash('Aksi menu tidak dikenal.', 'error', 'categories/categories.php');
    }

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $name = trim(request_string($_POST, 'name'));
    $photo = basename(trim(request_string($_POST, 'photo')));
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $calories = filter_var($_POST['calories'] ?? null, FILTER_VALIDATE_INT);
    $description = trim(request_string($_POST, 'description'));
    $available = isset($_POST['is_available']) ? 1 : 0;

    if (
        ($action === 'update' && ($id === false || $id < 1)) ||
        $name === '' || strlen($name) > 100 ||
        $photo === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $photo) ||
        !is_file(__DIR__ . '/../asset/' . $photo) ||
        $price === false || $price < 0 ||
        $calories === false || $calories < 0 ||
        strlen($description) > 5000
    ) {
        redirect_with_flash(
            'Periksa nama, gambar (harus tersedia di folder asset), harga, kalori, dan deskripsi menu.',
            'error',
            'categories/categories.php'
        );
    }

    if ($action === 'create') {
        $statement = $connection->prepare(
            'INSERT INTO tb_categories (categories, photo, price, calories, description, is_available)
             VALUES (:name, :photo, :price, :calories, :description, :available)'
        );
    } else {
        $statement = $connection->prepare(
            'UPDATE tb_categories SET categories = :name, photo = :photo, price = :price,
             calories = :calories, description = :description, is_available = :available WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
    }

    $statement->bindValue(':name', $name);
    $statement->bindValue(':photo', $photo);
    $statement->bindValue(':price', (string) $price);
    $statement->bindValue(':calories', $calories, PDO::PARAM_INT);
    $statement->bindValue(':description', $description);
    $statement->bindValue(':available', $available, PDO::PARAM_INT);
    $statement->execute();
    set_flash($action === 'create' ? 'Menu berhasil ditambahkan.' : 'Menu berhasil diperbarui.', 'success');
    header('Location: categories.php');
    exit;
}

$search = trim(request_string($_GET, 'search'));
$statement = $connection->prepare(
    'SELECT id, photo, categories, price, calories, description, is_available
     FROM tb_categories WHERE categories LIKE :search ORDER BY id DESC'
);
$statement->execute(['search' => '%' . $search . '%']);
$menus = $statement->fetchAll();

admin_header('Kelola Menu Produk', 'menu');
?>
<div class="table-container menu-form">
    <h3>Tambah Menu</h3>
    <form method="post" class="data-form menu-create-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">
        <label>Nama menu<input name="name" required maxlength="100"></label>
        <label>Nama file gambar di asset/<input name="photo" required placeholder="GH6.png"></label>
        <label>Harga (Rp)<input name="price" type="number" min="0" step="1" required></label>
        <label>Kalori<input name="calories" type="number" min="0" required></label>
        <label>Deskripsi<textarea name="description" maxlength="5000"></textarea></label>
        <label class="availability-label"><input type="checkbox" name="is_available" checked> Tersedia</label>
        <button class="btn-primary" type="submit">Tambah Menu</button>
    </form>
</div>
<div class="content-header">
    <h3>Daftar Menu</h3>
    <form class="action-bar" method="get">
        <input class="search-box" name="search" value="<?= e($search) ?>" placeholder="Cari nama menu...">
        <button class="btn-primary" type="submit">Cari</button>
    </form>
</div>
<div class="table-container table-responsive menu-table-container">
    <table class="data-table menu-table">
        <colgroup>
            <col class="menu-column">
            <col class="price-column">
            <col class="calories-column">
            <col class="description-column">
            <col class="status-column">
            <col class="actions-column">
        </colgroup>
        <thead><tr><th>Menu</th><th>Harga</th><th>Kalori</th><th>Deskripsi</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if ($menus === []): ?><tr><td colspan="6">Menu tidak ditemukan.</td></tr>
        <?php else: foreach ($menus as $menu): ?>
            <tr>
                <td>
                    <img src="../asset/<?= e($menu['photo']) ?>" class="table-img" alt="<?= e($menu['categories']) ?>">
                    <form id="update-menu-<?= (int) $menu['id'] ?>" method="post"></form>
                    <input form="update-menu-<?= (int) $menu['id'] ?>" type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input form="update-menu-<?= (int) $menu['id'] ?>" type="hidden" name="action" value="update">
                    <input form="update-menu-<?= (int) $menu['id'] ?>" type="hidden" name="id" value="<?= (int) $menu['id'] ?>">
                    <label>Nama<input form="update-menu-<?= (int) $menu['id'] ?>" name="name" required maxlength="100" value="<?= e($menu['categories']) ?>"></label>
                    <label>Gambar<input form="update-menu-<?= (int) $menu['id'] ?>" name="photo" required value="<?= e($menu['photo']) ?>"></label>
                </td>
                <td><label>Rp<input form="update-menu-<?= (int) $menu['id'] ?>" name="price" type="number" min="0" step="1" required value="<?= e((int) $menu['price']) ?>"></label></td>
                <td><label>kcal<input form="update-menu-<?= (int) $menu['id'] ?>" name="calories" type="number" min="0" required value="<?= (int) $menu['calories'] ?>"></label></td>
                <td><label><textarea form="update-menu-<?= (int) $menu['id'] ?>" name="description" maxlength="5000"><?= e($menu['description']) ?></textarea></label></td>
                <td><label class="availability-label"><input form="update-menu-<?= (int) $menu['id'] ?>" type="checkbox" name="is_available" <?= $menu['is_available'] ? 'checked' : '' ?>> Tersedia</label></td>
                <td class="menu-actions">
                    <button form="update-menu-<?= (int) $menu['id'] ?>" class="btn-primary" type="submit">Simpan</button>
                    <form method="post" class="inline-form" onsubmit="return confirm('Hapus menu ini?')">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $menu['id'] ?>">
                        <button class="btn-action btn-del" type="submit" title="Hapus menu"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php admin_footer(); ?>
