<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_post();
verify_csrf();

$name = trim(request_string($_POST, 'nama'));
$email = strtolower(trim(request_string($_POST, 'email')));
$password = request_string($_POST, 'password');
$confirmation = request_string($_POST, 'konfirmasi_password');

if (
    $name === '' ||
    mb_strlen($name) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($password) < 8 ||
    $password !== $confirmation ||
    ($_POST['terms'] ?? '') !== '1'
) {
    redirect_with_flash(
        'Periksa nama, email, password minimal 8 karakter, konfirmasi, dan persetujuan syarat.',
        'error',
        'RegisterPage.php'
    );
}

$connection = db();
$adminEmail = $connection->prepare('SELECT 1 FROM tb_admin WHERE email = :email');
$adminEmail->execute(['email' => $email]);
if ($adminEmail->fetchColumn()) {
    redirect_with_flash('Email sudah terdaftar. Silakan masuk atau gunakan email lain.', 'error', 'RegisterPage.php');
}

$statement = $connection->prepare(
    'INSERT INTO tb_users (name, email, password) VALUES (:name, :email, :password)'
);
try {
    $statement->execute([
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $error) {
    if ($error->getCode() === '23000') {
        redirect_with_flash('Email sudah terdaftar. Silakan masuk atau gunakan email lain.', 'error', 'RegisterPage.php');
    }

    throw $error;
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $connection->lastInsertId();
$_SESSION['name'] = $name;
$_SESSION['role'] = 'customer';

set_flash('Registrasi berhasil. Selamat datang, ' . $name . '!', 'success');
header('Location: Menu.php');
exit;
