<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (($_SESSION['role'] ?? '') === 'customer') {
  $statement = db()->prepare("SELECT 1 FROM tb_users WHERE id = :id AND status = 'Aktif'");
  $statement->execute(['id' => (int) ($_SESSION['user_id'] ?? 0)]);
  if (!$statement->fetchColumn()) {
    $_SESSION = [];
    session_regenerate_id(true);
  }
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Healthy Bite - Makanan Sehat</title>
    <link rel="stylesheet" href="css/index.css" />
    <link rel="stylesheet" href="css/toast.css" />
  </head>
  <body>
    <!-- Container untuk Toast / Snackbar -->
    <div id="toast-container"></div>
    <?php render_flash_toast(); ?>

    <!-- ========== NAVBAR ========== -->
    <header class="navbar">
      <div class="navbar-kiri">
        <img src="asset/Logo.png" alt="Logo Healthy Bite" class="logo" />
        <span class="teks-putih">HEALTHY</span>
        <span class="teks-hijau">BITE</span>
      </div>

      <nav class="navbar-tengah">
        <!-- DOM 1: Navigasi ktif -->
        <a href="index.php" class="nav-link aktif">Home</a>
        <a href="Menu.php" class="nav-link">Menu</a>
        <a href="#program" class="nav-link">Program</a>
        <a href="#tentang" class="nav-link">Tentang Kami</a>
      </nav>

      <div class="navbar-kanan">
        <button class="ikon">Q</button>
        <button class="ikon">⬇</button>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="admin.php" class="user-greeting">Halo, <?= e($_SESSION['name'] ?? 'Admin') ?></a>
            <a href="admin.php" class="tombol-masuk">Dashboard</a>
          <?php else: ?>
            <span class="user-greeting">Halo, <?= e($_SESSION['name'] ?? 'Pelanggan') ?></span>
            <a href="transaction/my_orders.php" class="tombol-masuk">Pesanan Saya</a>
          <?php endif; ?>
          <form action="logout.php" method="post" class="home-logout">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="tombol-masuk">Keluar</button>
          </form>
        <?php else: ?>
          <a href="LoginPage.php" class="tombol-masuk" onclick="izinkanMasuk()">👤 Masuk</a>
        <?php endif; ?>
      </div>
    </header>

    <!-- ========== HERO SECTION ========== -->
    <section class="hero">
      <img src="asset/GF1.png" alt="Makanan sehat" class="hero-gambar" />

      <div class="hero-teks">
        <h1 id="hero-judul">
          Lezat rasanya,<br /><span class="hijau">sehat</span> manfaatnya.
        </h1>
        <p>
          Nikmati menu bernutrisi yang dibuat segar setiap hari untuk mendukung
          tujuan sehatmu.
        </p>
        <!-- DOM 3: Mengubah judul & memicu Toast -->
        <button class="tombol-hero" id="btn-hero-menu">
          Lihat menu hari ini →
        </button>
      </div>
    </section>

    <!-- ========== KARTU SECTION ========== -->
    <section class="kartu-wrapper">
      <!-- Kartu 1 -->
      <div class="kartu">
        <div class="kartu-gambar">
          <img
            src="asset/GF2.png"
            alt="Bulking Meal Plan"
            id="gambar-kartu-1"
          />
        </div>
        <div class="kartu-isi">
          <span class="label">Paling Populer</span>
          <h2>Bulking Meal Plan</h2>
          <p>Menu paling lengkap dengan 1400 kalori</p>
          <div class="kartu-bawah">
            <span class="harga">Mulai Rp22rb</span>
            <!-- DOM 2 & Toast: Klik tombol pilih menu -->
            <button class="tombol-lingkaran" id="btn-slide-1">→</button>
          </div>
        </div>
      </div>

      <!-- Kartu 2 -->
      <div class="kartu">
        <div class="kartu-gambar">
          <img src="asset/GH1.png" alt="Motivation" />
        </div>
        <div class="kartu-isi">
          <span class="label">Motivation</span>
          <h2>What do you think?</h2>
          <p>You want to do something different</p>
          <div class="kartu-bawah">
            <!-- Penampung angka like -->
            <span id="jumlah-like">❤️ 0 Suka</span>
            <!-- Tombol Like -->
            <button class="tombol-lingkaran" id="btn-like">❤️</button>
          </div>
        </div>
      </div>
    </section>

    <script>
      function showToast(pesan) {
        const toastContainer = document.getElementById("toast-container");

        // Membuat elemen div Toast baru di DOM
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.innerText = pesan;

        toastContainer.appendChild(toast);

        // Menambahkan class animasi masuk
        setTimeout(() => {
          toast.classList.add("show");
        }, 100);

        // Otomatis menghapus toast setelah 3 detik
        setTimeout(() => {
          toast.classList.remove("show");
          setTimeout(() => {
            toast.remove();
          }, 300);
        }, 3000);
      }

      // PENERAPAN 3 MANIPULASI DOM

      // DOM 1: Mengubah class 'aktif' pada menu navbar saat diklik
      const navLinks = document.querySelectorAll(".nav-link");
      navLinks.forEach((link) => {
        link.addEventListener("click", function (e) {
          // Pindah halaman hanya jika href mengarah ke halaman lain.
          const href = this.getAttribute("href");

          // Jika href bernilai '#' atau kosong, baru kita cegah fungsinya
          if (!href || href === "#") {
            e.preventDefault();
            navLinks.forEach((l) => l.classList.remove("aktif"));
            this.classList.add("aktif");
          }
        });
      });

      // DOM 2: Mengubah/Slide gambar kartu ke gambar berikutnya saat tombol diklik
      const gambarKartu = document.getElementById("gambar-kartu-1");
      const btnSlide = document.getElementById("btn-slide-1");

      // Daftar gambar yang ingin di-slide
      const daftarGambar = ["asset/GF2.png", "asset/GH3.png"];

      let indeksGambar = 0;

      if (btnSlide && gambarKartu) {
        btnSlide.addEventListener("click", function () {
          // 1. Tambahkan class fade-out untuk memicu animasi menghilang
          gambarKartu.classList.add("fade-out");

          // 2. Tunggu 400ms (sesuai durasi transition CSS) lalu ganti gambar
          setTimeout(() => {
            indeksGambar = (indeksGambar + 1) % daftarGambar.length;
            gambarKartu.src = daftarGambar[indeksGambar];

            // 3. Hapus class fade-out agar gambar baru muncul kembali (fade-in)
            gambarKartu.classList.remove("fade-out");
          }, 400);

          showToast(`Gambar diganti ke slide ${indeksGambar + 1}!`);
        });
      }

      // DOM 3: Fitur Like / Suka pada Kartu Motivasi
      const btnLike = document.getElementById("btn-like");
      const teksJumlahLike = document.getElementById("jumlah-like");

      let jumlahLike = 0;
      let sudahSuka = false;

      btnLike.addEventListener("click", function () {
        if (!sudahSuka) {
          // Jika belum disukai: tambah angka like dan ubah tampilan
          jumlahLike++;
          sudahSuka = true;

          // Manipulasi DOM: Mengubah teks angka dan warna tombol
          teksJumlahLike.innerText = `❤️ ${jumlahLike} Suka`;
          btnLike.style.backgroundColor = "#e53935"; // Ubah tombol jadi warna merah
          btnLike.style.color = "#ffffff";

          showToast("Kamu menyukai motivasi ini! ❤️");
        } else {
          // Jika diklik lagi: batalkan like (unlike)
          jumlahLike--;
          sudahSuka = false;

          // Manipulasi DOM: Kembalikan teks dan warna semula
          teksJumlahLike.innerText = `❤️ ${jumlahLike} Suka`;
          btnLike.style.backgroundColor = ""; // Kembalikan ke warna awal CSS
          btnLike.style.color = "";

          showToast("Batal menyukai motivasi.");
        }
      });

      function izinkanMasuk() {
        showToast("Mengarahkan ke halaman login...");
      }
    </script>
  </body>
</html>
