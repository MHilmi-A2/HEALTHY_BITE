<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_post();
verify_csrf();

$identity = trim(request_string($_POST, 'username'));
$password = request_string($_POST, 'password');
if ($identity === '' || $password === '') {
    redirect_with_flash('Username/email dan password wajib diisi.', 'error', 'LoginPage.php');
}

$connection = db();
$statement = $connection->prepare(
    'SELECT id, username AS name, email, password FROM tb_admin WHERE username = :username OR email = :email LIMIT 1'
);
$statement->execute(['username' => $identity, 'email' => $identity]);
$account = $statement->fetch();
$role = 'admin';

if (!$account) {
    $statement = $connection->prepare(
        "SELECT id, name, email, password FROM tb_users WHERE email = :email AND status = 'Aktif' LIMIT 1"
    );
    $statement->execute(['email' => $identity]);
    $account = $statement->fetch();
    $role = 'customer';
}

if (!$account || !password_verify($password, $account['password'])) {
    redirect_with_flash('Username/email atau password tidak sesuai.', 'error', 'LoginPage.php');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $account['id'];
$_SESSION['name'] = $account['name'];
$_SESSION['role'] = $role;

set_flash('Login berhasil. Selamat datang, ' . $account['name'] . '!', 'success');
header('Location: ' . ($role === 'admin' ? 'admin.php' : 'Menu.php'));
exit;
