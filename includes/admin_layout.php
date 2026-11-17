<?php
function admin_header(string $title, string $active): void
{
    require_admin();
    $name = e($_SESSION['name'] ?? 'Admin');
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> - Healthy Bite</title>
        <link rel="stylesheet" href="<?= e(app_url('css/admin.css')) ?>">
        <link rel="stylesheet" href="<?= e(app_url('css/toast.css')) ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
    <?php render_flash_toast(); ?>
    <div class="admin-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <img src="<?= e(app_url('asset/Logo.png')) ?>" alt="Logo">
                <h2>HEALTHY<span>BITE</span></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="<?= e(app_url('admin.php')) ?>" class="nav-item <?= $active === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="<?= e(app_url('transaction/transaction.php')) ?>" class="nav-item <?= $active === 'orders' ? 'active' : '' ?>"><i class="fa-solid fa-cart-shopping"></i> Data Pesanan</a>
                <a href="<?= e(app_url('categories/categories.php')) ?>" class="nav-item <?= $active === 'menu' ? 'active' : '' ?>"><i class="fa-solid fa-utensils"></i> Kelola Menu</a>
                <a href="<?= e(app_url('admin/admin_pelanggan.php')) ?>" class="nav-item <?= $active === 'customers' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Data Pelanggan</a>
            </nav>
            <div class="sidebar-bottom">
                <form action="<?= e(app_url('logout.php')) ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button class="nav-item logout" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Keluar</button>
                </form>
            </div>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button id="menu-toggle" class="menu-toggle" type="button" aria-label="Buka menu"><i class="fa-solid fa-bars"></i></button>
                    <h2><?= e($title) ?></h2>
                </div>
                <div class="topbar-right">
                    <div class="admin-profile"><span><?= $name ?></span></div>
                </div>
            </header>
            <div class="dashboard-content">
    <?php
}

function admin_footer(): void
{
    ?>
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
    <?php
}
