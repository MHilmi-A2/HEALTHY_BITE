<?php
// PERUBAHAN: Gunakan session untuk melindungi halaman kategori dan menampilkan nama pengguna.
session_start();
if (!isset($_SESSION['nama_pengguna'])) {
    header('Location: ../Login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Admin Healthy Bite</title>
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
                <a href="admin_pesanan.php" class="nav-item"><i class="fa-solid fa-cart-shopping"></i> Data Pesanan</a>
                <a href="admin_menu.php" class="nav-item active"><i class="fa-solid fa-utensils"></i> Kelola Menu</a>
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
                    <h2>Kelola Menu Produk</h2>
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
                    <h3>Daftar Menu Makanan</h3>
                    <div class="action-bar">
                        <input type="text" class="search-box" placeholder="Cari nama menu...">
                        <button class="btn-primary" onclick="alert('Membuka form tambah menu baru!')"><i class="fa-solid fa-plus"></i> Tambah Menu Baru</button>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama Menu</th>
                                    <th>Kategori</th>
                                    <th>Kalori</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><img src="../asset/GH6.png" class="table-img" alt="Menu"></td>
                                    <td><strong>Bulking Meal Plan</strong></td>
                                    <td>Meal Plan</td>
                                    <td>1400 kcal</td>
                                    <td>Rp 22.000</td>
                                    <td><span class="status status-aktif">Tersedia</span></td>
                                    <td>
                                        <button class="btn-action btn-edit" title="Edit Data"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button class="btn-action btn-del" title="Hapus Menu"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="../asset/MG1.png" class="table-img" alt="Menu"></td>
                                    <td><strong>Bulking Meal Plan Pro</strong></td>
                                    <td>Premium Plan</td>
                                    <td>1800 kcal</td>
                                    <td>Rp 30.000</td>
                                    <td><span class="status status-aktif">Tersedia</span></td>
                                    <td>
                                        <button class="btn-action btn-edit" title="Edit Data"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button class="btn-action btn-del" title="Hapus Menu"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="../asset/GH3.png" class="table-img" alt="Menu"></td>
                                    <td><strong>Diet Salad Reguler</strong></td>
                                    <td>Salad</td>
                                    <td>350 kcal</td>
                                    <td>Rp 18.000</td>
                                    <td><span class="status status-batal">Habis</span></td>
                                    <td>
                                        <button class="btn-action btn-edit" title="Edit Data"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button class="btn-action btn-del" title="Hapus Menu"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
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