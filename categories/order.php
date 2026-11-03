<?php
// PERUBAHAN: Terima pesanan dari Menu.html, validasi menu, dan simpan ke session.
session_start();

if (!isset($_SESSION['nama_pengguna'])) {
    header('Location: ../Login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode permintaan tidak diizinkan.');
}

$hargaMenu = [
    'Bulking Meal Plan' => 22000,
    'Bulking Meal Plan Pro' => 30000,
    'Bulking Meal Plan Lite' => 22000,
    'Bulking Meal Plan Max' => 22000,
    'Bulking Meal Plan Supreme' => 22000,
];

$items = json_decode($_POST['items'] ?? '', true);
if (!is_array($items) || $items === []) {
    http_response_code(400);
    exit('Keranjang pesanan kosong atau tidak valid.');
}

$rincian = [];
$total = 0;

foreach ($items as $item) {
    if (!is_array($item)) {
        http_response_code(400);
        exit('Data item pesanan tidak valid.');
    }

    $namaMenu = $item['title'] ?? '';
    $jumlah = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);

    if (!isset($hargaMenu[$namaMenu]) || $jumlah === false || $jumlah < 1 || $jumlah > 100) {
        http_response_code(400);
        exit('Menu atau jumlah pesanan tidak valid.');
    }

    $rincian[] = $jumlah . 'x ' . $namaMenu;
    $total += $hargaMenu[$namaMenu] * $jumlah;
}

// PERUBAHAN: Simpan pesanan per session agar dapat dibaca admin_pesanan.php.
$_SESSION['pesanan'] ??= [];
$_SESSION['pesanan'][] = [
    'id' => 'ORD-' . strtoupper(bin2hex(random_bytes(3))),
    'waktu' => date('d M Y, H:i'),
    'pelanggan' => $_SESSION['nama_pengguna'],
    'item' => implode(', ', $rincian),
    'total' => $total,
];

header('Location: admin_pesanan.php');
exit;
