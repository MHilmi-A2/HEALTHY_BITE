<?php
// PERUBAHAN: Terima form registrasi HTML dan simpan nama pengguna dalam session.
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode permintaan tidak diizinkan.');
}

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

if (
    $nama === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $password === '' ||
    $password !== $konfirmasiPassword ||
    !isset($_POST['terms'])
) {
    http_response_code(400);
    exit('Periksa nama, email, kata sandi, konfirmasi, dan persetujuan syarat.');
}

// Demo tugas: data akun belum disimpan ke database.
session_regenerate_id(true);
$_SESSION['nama_pengguna'] = $nama;

header('Location: ../Menu.html');
exit;
