<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_post();
verify_csrf();

$_SESSION = [];
session_regenerate_id(true);
set_flash('Anda berhasil keluar dari akun.', 'success');
header('Location: ' . app_url('LoginPage.php'));
exit;
