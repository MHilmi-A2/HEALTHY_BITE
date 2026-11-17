<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: admin.php');
    exit;
}

$menus = db()->query('SELECT id, photo, categories, price, calories, description FROM tb_categories WHERE is_available = 1 ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthy Bite - Menu</title>
    <link rel="stylesheet" href="css/Menu.css">
    <link rel="stylesheet" href="css/toast.css">
    <!-- FontAwesome untuk ikon header -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php render_flash_toast(); ?>

    <!-- Header / Navbar -->
    <header class="navbar">
        <div class="logo">
            <span class="logo-text">HEALTHY <strong>BITE</strong></span>
        </div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="Menu.php" class="active">Menu</a>
            <a href="#">Program</a>
            <a href="#">Tentang Kami</a>
        </nav>
        <div class="nav-icons">
            <div class="cart-icon-wrapper" id="cartBtn">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-badge" id="cartCount">0</span>
            </div>
            <i class="fa-solid fa-magnifying-glass"></i>
            <i class="fa-solid fa-download"></i>
            <i class="fa-regular fa-bell"></i>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <span class="user-greeting">Halo, <?= e($_SESSION['name'] ?? 'Pelanggan') ?></span>
                <a href="transaction/my_orders.php">Pesanan Saya</a>
                <form action="logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button class="btn-masuk" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Keluar</button>
                </form>
            <?php else: ?>
                <a class="btn-masuk" href="LoginPage.php"><i class="fa-regular fa-user"></i> Masuk</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Konten Utama: Daftar Card Menu -->
    <main class="container">
        <!-- Input pencarian untuk mendemonstrasikan Event Form & Keyboard -->
        <div class="search-section">
            <form id="searchForm">
                <input type="text" id="searchInput" placeholder="Cari menu... (Coba ketik atau tekan tombol keyboard)">
                <button type="submit">Cari</button>
            </form>
            <p id="eventLogger" class="event-logger">Log Event JavaScript: Menunggu aksi pengguna...</p>
        </div>

        <div class="menu-grid">
            <?php foreach ($menus as $menu): ?>
                <div class="card" data-id="<?= (int) $menu['id'] ?>" data-title="<?= e($menu['categories']) ?>" data-price="<?= e($menu['price']) ?>">
                    <div class="card-image">
                        <img src="asset/<?= e($menu['photo'] ?: 'Logo.png') ?>" alt="<?= e($menu['categories']) ?>">
                    </div>
                    <div class="card-body">
                        <span class="badge"><?= (int) $menu['calories'] ?> kcal</span>
                        <h3><?= e($menu['categories']) ?></h3>
                        <p class="desc"><?= e($menu['description']) ?></p>
                        <div class="card-footer">
                            <span class="price">Rp <?= number_format((float) $menu['price'], 0, ',', '.') ?></span>
                            <button type="button" class="btn-arrow add-to-cart-btn" aria-label="Tambah <?= e($menu['categories']) ?>"><i class="fa-solid fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if ($menus === []): ?>
                <p>Belum ada menu yang tersedia.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Popup Keranjang Belanja -->
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <h2><i class="fa-solid fa-cart-shopping"></i> Keranjang Pesanan</h2>
            
            <div id="cartItemsList" class="cart-items-list">
                <p class="empty-msg">Keranjang belanja Anda masih kosong.</p>
            </div>

            <div class="cart-summary">
                <p>Total Item: <span id="totalItems">0</span></p>
                <p>Total Harga: <strong>Rp<span id="totalPrice">0</span></strong></p>
            </div>

            <!-- Tombol Aksi Pembelian & Batal -->
            <!-- PERUBAHAN: Kirim data keranjang ke pemroses pesanan PHP. -->
            <form id="checkoutForm" action="transaction/order.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="items" id="orderItems">
                <div class="checkout-fields">
                    <div class="checkout-field">
                    <label for="phone">Nomor telepon</label>
                    <input id="phone" name="phone" type="tel" required maxlength="20" autocomplete="tel">
                    </div>
                    <div class="checkout-field">
                    <label for="address">Alamat pengiriman</label>
                    <textarea id="address" name="address" required maxlength="1000" autocomplete="street-address"></textarea>
                    </div>
                </div>
                <div class="checkout-actions">
                    <button type="button" class="btn-cancel-all" id="clearCartBtn">Batal Semua Pesanan</button>
                    <button type="submit" class="btn-checkout" id="checkoutBtn">Lanjutkan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script JavaScript Penerapan Event -->
    <script>
        const logger = document.getElementById('eventLogger');

        function logEvent(text) {
            logger.innerText = "Log Event: " + text;
        }

        // Window Events
        window.addEventListener('load', () => logEvent("Window 'load' - Halaman selesai dimuat!"));
        window.addEventListener('resize', () => logEvent(`Window 'resize' - Ukuran layar (${window.innerWidth}x${window.innerHeight})`));
        window.addEventListener('scroll', () => logEvent(`Window 'scroll' - Posisi Y: ${window.scrollY}px`));

        // Form & Keyboard Events
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');

        searchInput.addEventListener('keydown', (e) => logEvent(`Keyboard 'keydown' - Tombol: '${e.key}'`));
        searchInput.addEventListener('keyup', () => logEvent(`Keyboard 'keyup' - Tombol dilepas`));
        searchInput.addEventListener('change', (e) => logEvent(`Formulir 'change' - Input: "${e.target.value}"`));
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLocaleLowerCase('id');
            cards.forEach((card) => {
                card.hidden = !card.dataset.title.toLocaleLowerCase('id').includes(query);
            });
        });
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            logEvent(`Formulir 'submit' - Pencarian dikirim!`);
        });

        // Mouse Events & Cart Logic
        let cart = [];
        const cartModal = document.getElementById('cartModal');
        const cartBtn = document.getElementById('cartBtn');
        const closeModal = document.getElementById('closeModal');
        const clearCartBtn = document.getElementById('clearCartBtn');
        const cards = document.querySelectorAll('.card');

        cards.forEach((card) => {
            card.addEventListener('click', () => logEvent(`Mouse 'click' - Menu "${card.dataset.title}"`));
            card.addEventListener('dblclick', () => logEvent(`Mouse 'dblclick' - Menu "${card.dataset.title}"`));
            card.addEventListener('mouseover', () => logEvent(`Mouse 'mouseover' - Hover ke "${card.dataset.title}"`));
            card.addEventListener('mouseout', () => logEvent(`Mouse 'mouseout' - Meninggalkan "${card.dataset.title}"`));
            card.addEventListener('mousedown', () => logEvent(`Mouse 'mousedown' - Klik tahan pada "${card.dataset.title}"`));
            card.addEventListener('mouseup', () => logEvent(`Mouse 'mouseup' - Lepas klik pada "${card.dataset.title}"`));

            const addBtn = card.querySelector('.add-to-cart-btn');
            addBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const title = card.dataset.title;
                const price = parseInt(card.dataset.price);

                addToCart(Number(card.dataset.id), title, price);
                openModal();
            });
        });

        function addToCart(id, title, price) {
            cart.push({ id, title, price });
            updateCartUI();
        }

        // Fungsi Batal / Hapus Item Tertentu dari Keranjang
        function removeFromCart(index) {
            logEvent(`Mouse 'click' - Membatalkan pesanan "${cart[index].title}"`);
            cart.splice(index, 1);
            updateCartUI();
        }

        function updateCartUI() {
            document.getElementById('cartCount').innerText = cart.length;
            document.getElementById('totalItems').innerText = cart.length;

            const cartList = document.getElementById('cartItemsList');
            if (cart.length === 0) {
                cartList.innerHTML = '<p class="empty-msg">Keranjang belanja Anda masih kosong.</p>';
                document.getElementById('totalPrice').innerText = '0';
                return;
            }

            let html = '<ul class="cart-item-group">';
            let total = 0;
            cart.forEach((item, index) => {
                total += item.price;
                html += `
                <li class="cart-item">
                    <div class="cart-item-info">
                        <span class="cart-item-title">${item.title}</span>
                        <span class="cart-item-price">Rp${item.price.toLocaleString('id-ID')}</span>
                    </div>
                    <button class="btn-cancel-item" onclick="removeFromCart(${index})" title="Batal Pesan">
                        <i class="fa-solid fa-trash-can"></i> Batal
                    </button>
                </li>`;
            });
            html += '</ul>';

            cartList.innerHTML = html;
            document.getElementById('totalPrice').innerText = total.toLocaleString('id-ID');
        }

        function openModal() {
            cartModal.style.display = 'block';
        }

        cartBtn.addEventListener('click', openModal);

        closeModal.addEventListener('click', () => {
            cartModal.style.display = 'none';
        });

        // Batal Semua Pesanan
        clearCartBtn.addEventListener('click', () => {
            if (cart.length > 0) {
                if (confirm('Apakah Anda yakin ingin membatalkan semua pesanan?')) {
                    cart = [];
                    updateCartUI();
                    logEvent("Mouse 'click' - Semua pesanan dibatalkan");
                }
            } else {
                alert('Keranjang Anda sudah kosong!');
            }
        });

        window.addEventListener('click', (e) => {
            if (e.target === cartModal) {
                cartModal.style.display = 'none';
            }
        });

        document.getElementById('checkoutForm').addEventListener('submit', (event) => {
            if (cart.length === 0) {
                alert('Keranjang masih kosong!');
                event.preventDefault();
                return;
            }

            // PERUBAHAN: Kirim hanya nama menu dan jumlah; harga divalidasi kembali oleh PHP.
            const itemsById = cart.reduce((items, item) => {
                items[item.id] = (items[item.id] || 0) + 1;
                return items;
            }, {});
            document.getElementById('orderItems').value = JSON.stringify(
                Object.entries(itemsById).map(([id, quantity]) => ({ id, quantity }))
            );
        });
    </script>
</body>
</html>