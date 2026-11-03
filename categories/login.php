<?php
// PERUBAHAN: Terima form login HTML dan simpan nama pengguna dalam session.
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode permintaan tidak diizinkan.');
}

$namaPengguna = trim($_POST['username'] ?? '');
$kataSandi = $_POST['password'] ?? '';

if ($namaPengguna === '' || $kataSandi === '') {
    http_response_code(400);
    exit('Username/email dan password wajib diisi.');
}

// Demo tugas: belum memeriksa akun ke database.
session_regenerate_id(true);
$_SESSION['nama_pengguna'] = $namaPengguna;

header('Location: ../Menu.html');
exit;
