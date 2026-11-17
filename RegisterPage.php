<?php
require_once __DIR__ . '/includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun - Healthy Bite</title>
    <!-- Hanya menghubungkan ke file CSS lokal kita sendiri -->
    <link rel="stylesheet" href="css/Register.css">
    <link rel="stylesheet" href="css/toast.css">
</head>
<body>
    <?php render_flash_toast(); ?>

    <div class="container">
        
        <!-- SISI KIRI: Gambar dan Promosi -->
        <div class="left-panel">
            <div class="overlay"></div>


            
            <div class="logo">
                <!-- Ganti src dengan path logo Anda -->
                <img src="asset/Logo.png" alt="Healthy Bite Logo"> 
            </div>

            <div class="hero-text">
                <p class="tagline">Pertumbuhan Otot yang baik hati jadi senang</p>
                <h1>Makan lebih baik,<br>hidup lebih <span class="highlight">berarti.</span></h1>
                <p class="description">
                    Temukan menu sehat, pantau nutrisi, dan raih targetmu bersama ahli gizi terpercaya.
                </p>
            </div>
        </div>

        <!-- SISI KANAN: Formulir Registrasi -->
        <div class="right-panel">
            
            <a href="index.php" class="back-link">
                &#8592; kembali <!-- Kode Unicode untuk panah kiri -->
            </a>

            <div class="form-container">
                <h2>Buat Akun Anda</h2>
                <p class="subtitle">Silakan lengkapi data diri dibawa ini</p>

                <!-- PERUBAHAN: Data registrasi diproses oleh endpoint PHP dalam folder categories. -->
                <form action="register.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <!-- Input Nama Lengkap -->
                    <div class="input-group">
                        <label for="nama">Nama Lengkap</label>
                        <div class="input-wrapper">
                            <span class="icon">&#128100;</span> <!-- Ikon User -->
                            <input type="text" id="nama" name="nama" placeholder="Masukkan Nama Lengkap" required>
                        </div>
                    </div>

                    <!-- Input Email -->
                    <div class="input-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <span class="icon">&#9993;</span> <!-- Ikon Amplop -->
                            <input type="email" id="email" name="email" placeholder="Email" required>
                        </div>
                    </div>

                    <!-- Input Kata Sandi -->
                    <div class="input-group">
                        <label for="password">Kata sandi</label>
                        <div class="input-wrapper">
                            <span class="icon">&#128274;</span> <!-- Ikon Gembok -->
                            <input type="password" id="password" name="password" placeholder="Kata sandi" minlength="8" required>
                            <span class="toggle-password">&#128065;</span> <!-- Ikon Mata -->
                        </div>
                    </div>

                    <!-- Input Konfirmasi Kata Sandi -->
                    <div class="input-group">
                        <label for="confirm-password">Konfirmasi Kata sandi</label>
                        <div class="input-wrapper">
                            <span class="icon">&#128274;</span> <!-- Ikon Gembok -->
                            <input type="password" id="confirm-password" name="konfirmasi_password" placeholder="Konfirmasi Kata sandi" minlength="8" required>
                            <span class="toggle-password">&#128065;</span> <!-- Ikon Mata -->
                        </div>
                    </div>

                    <!-- Checkbox Syarat & Ketentuan -->
                    <div class="terms">
                        <input type="checkbox" id="terms" name="terms" value="1" required>
                        <label for="terms">Saya menyetujui Syarat & Ketentuan serta Kebijakan Privasi.</label>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit" class="btn-submit">Buat Akun ></button>

                    <!-- PERUBAHAN: Tautan kembali ke halaman login HTML. -->
                    <p class="login-link">
                        sudah Punya Akun? <a href="LoginPage.php">Masuk disini</a>
                    </p>

                </form>
            </div>
        </div>

    </div>

</body>
</html>