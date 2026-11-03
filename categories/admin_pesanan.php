<?php
// PERUBAHAN: Gunakan session untuk melindungi halaman kategori dan menampilkan nama pengguna.
session_start();
if (!isset($_SESSION['nama_pengguna'])) {
    header('Location: ../Login.html');
    exit;
}
$pesanan = $_SESSION['pesanan'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pesanan - Admin Healthy Bite</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <img src="../asset/Logo.png" alt="Logo">
                <h2>HEALTHY<span>BITE</span></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../admin.html" class="nav-item"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="admin_pesanan.php" class="nav-item active"><i class="fa-solid fa-cart-shopping"></i> Data Pesanan</a>
                <a href="admin_menu.php" class="nav-item"><i class="fa-solid fa-utensils"></i> Kelola Menu</a>
                <a href="admin_pelanggan.php" class="nav-item"><i class="fa-solid fa-users"></i> Data Pelanggan</a>
                <a href="#" class="nav-item"><i class="fa-solid fa-gear"></i> Pengaturan</a>
            </nav>
            <div class="sidebar-bottom">
                <a href="logout.php" class="nav-item logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button id="menu-toggle" class="menu-toggle"><i class="fa-solid fa-bars"></i></button>
                    <h2>Data Pesanan</h2>
                </div>
                <div class="topbar-right">
                    <div class="admin-profile">
                        <img src="https://via.placeholder.com/40" alt="Admin Profil" class="avatar">
                        <!-- PERUBAHAN: Nama ditampilkan dari session yang dibuat oleh form login/registrasi. -->
                        <span><?= htmlspecialchars($_SESSION['nama_pengguna'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="content-header">
                    <h3>Semua Pesanan</h3>
                    <div class="action-bar">
                        <input type="date" class="search-box">
                        <input type="text" class="search-box" placeholder="Cari ID/Nama Pelanggan...">
                        <button class="btn-primary"><i class="fa-solid fa-search"></i> Cari</button>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Waktu</th>
                                    <th>Pelanggan</th>
                                    <th>Item</th>
                                    <th>Total Tagihan</th>
                                    <th>Status Pembayaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- PERUBAHAN: Isi tabel berasal dari pesanan yang dikirim dari Menu.html. -->
                                <?php if ($pesanan === []): ?>
                                    <tr>
                                        <td colspan="7">Belum ada pesanan pada session ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_reverse($pesanan) as $pesananItem): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($pesananItem['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($pesananItem['waktu'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($pesananItem['pelanggan'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($pesananItem['item'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>Rp <?= number_format($pesananItem['total'], 0, ',', '.') ?></td>
                                            <td><span class="status status-proses">Menunggu Konfirmasi</span></td>
                                            <td>
                                                <button class="btn-action btn-edit" type="button" title="Lihat Detail"><i class="fa-solid fa-eye"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.getElementById('menu-toggle').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>