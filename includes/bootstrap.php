<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    header('Cache-Control: no-store, private, max-age=0');
    header('Pragma: no-cache');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function db(): PDO
{
    static $connection;

    if (!$connection) {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_NAME') ?: 'healthy_bite';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

        $connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $connection;
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect_with_flash('Silakan login untuk mengakses halaman tersebut.', 'error', 'LoginPage.php');
    }

    if (($_SESSION['role'] ?? '') === 'customer') {
        $statement = db()->prepare("SELECT 1 FROM tb_users WHERE id = :id AND status = 'Aktif'");
        $statement->execute(['id' => (int) $_SESSION['user_id']]);
        if (!$statement->fetchColumn()) {
            $_SESSION = [];
            session_regenerate_id(true);
            redirect_with_flash('Akun tidak aktif atau sesi berakhir. Silakan login kembali.', 'error', 'LoginPage.php');
        }
    }
}

function app_url(string $path): string
{
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $base = rtrim($scriptDirectory, '/');

    foreach (['/admin', '/categories', '/transaction', '/includes'] as $directory) {
        if (str_ends_with($base, $directory)) {
            $base = substr($base, 0, -strlen($directory));
            break;
        }
    }

    return $base . '/' . ltrim($path, '/');
}

function require_admin(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect_with_flash('Silakan login untuk membuka halaman admin.', 'error', 'LoginPage.php');
    }

    if (($_SESSION['role'] ?? '') !== 'admin') {
        redirect_with_flash('Halaman ini hanya dapat diakses oleh admin.', 'error', 'index.php');
    }
}

function set_flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => in_array($type, ['success', 'error', 'info'], true) ? $type : 'info',
    ];
}

function redirect_with_flash(string $message, string $type, string $path): never
{
    set_flash($message, $type);
    header('Location: ' . app_url($path));
    exit;
}

function render_flash_toast(): void
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = in_array($flash['type'] ?? '', ['success', 'error', 'info'], true) ? $flash['type'] : 'info';
    ?>
    <div class="toast-message toast-<?= e($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>" aria-live="<?= $type === 'error' ? 'assertive' : 'polite' ?>">
        <span><?= e($flash['message'] ?? '') ?></span>
        <button type="button" class="toast-close" aria-label="Tutup notifikasi">&times;</button>
    </div>
    <script>
        (() => {
            const toast = document.querySelector('.toast-message');
            const close = toast.querySelector('.toast-close');
            const dismiss = () => toast.classList.add('toast-hidden');
            close.addEventListener('click', dismiss);
            window.setTimeout(dismiss, 5000);
            toast.addEventListener('transitionend', () => {
                if (toast.classList.contains('toast-hidden')) toast.remove();
            });
        })();
    </script>
    <?php
}

function form_page_path(): string
{
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return match ($script) {
        'login.php', 'auth_login.php' => 'LoginPage.php',
        'register.php', 'auth_register.php' => 'RegisterPage.php',
        'order.php' => 'Menu.php',
        'transaction.php' => 'transaction/transaction.php',
        'categories.php' => 'categories/categories.php',
        'admin_pelanggan.php' => 'admin/admin_pelanggan.php',
        default => 'index.php',
    };
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
        redirect_with_flash('Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba kembali.', 'error', form_page_path());
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function request_string(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    if (!is_string($value)) {
        redirect_with_flash('Input tidak valid. Periksa kembali formulir.', 'error', form_page_path());
    }

    return $value;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect_with_flash('Permintaan tidak valid. Silakan gunakan formulir yang tersedia.', 'error', form_page_path());
    }
}
