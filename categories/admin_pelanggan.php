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
    <title>Data Pelanggan - Admin Healthy Bite</title>
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
                <a href="admin_menu.php" class="nav-item"><i class="fa-solid fa-utensils"></i> Kelola Menu</a>
                <a href="admin_pelanggan.php" class="nav-item active"><i class="fa-solid fa-users"></i> Data Pelanggan</a>
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
                    <h2>Data Pengguna / Pelanggan</h2>
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
                    <h3>Daftar Member Healthy Bite</h3>
                    <div class="action-bar">
                        <input type="text" class="search-box" placeholder="Cari nama / email...">
                        <button class="btn-primary"><i class="fa-solid fa-download"></i> Export Data CSV</button>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Member</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Tanggal Bergabung</th>
                                    <th>Total Transaksi</th>
                                    <th>Status Akun</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#USR-0001</td>
                                    <td>Budi Santoso</td>
                                    <td>budi.s@email.com</td>
                                    <td>10 Jan 2026</td>
                                    <td>15 Pesanan</td>
                                    <td><span class="status status-aktif">Aktif</span></td>
                                    <td>
                                        <button class="btn-action btn-edit" title="Lihat Profil"><i class="fa-solid fa-user-pen"></i></button>
                                        <button class="btn-action btn-del" title="Blokir Pengguna"><i class="fa-solid fa-ban"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#USR-0002</td>
                                    <td>Siti Aminah</td>
                                    <td>sitiaminah99@email.com</td>
                                    <td>05 Mar 2026</td>
                                    <td>8 Pesanan</td>
                                    <td><span class="status status-aktif">Aktif</span></td>
                                    <td>
                                        <button class="btn-action btn-edit" title="Lihat Profil"><i class="fa-solid fa-user-pen"></i></button>
                                        <button class="btn-action btn-del" title="Blokir Pengguna"><i class="fa-solid fa-ban"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#USR-0003</td>
                                    <td>Andi Wijaya</td>
                                    <td>andi_w@email.com</td>
                                    <td>12 Okt 2026</td>
                                    <td>0 Pesanan</td>
                                    <td><span class="status status-batal">Non-aktif</span></td>
                                    <td>
                                        <button class="btn-action btn-acc" title="Aktifkan Kembali"><i class="fa-solid fa-check-rotate"></i></button>
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