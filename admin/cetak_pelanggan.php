<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$autoloaders = [
    __DIR__ . '/../dompdf/autoload.inc.php',
    __DIR__ . '/../vendor/autoload.php',
];
$autoloadPath = null;
foreach ($autoloaders as $candidate) {
    if (is_file($candidate)) {
        $autoloadPath = $candidate;
        break;
    }
}

if ($autoloadPath === null) {
    redirect_with_flash(
        'Dompdf belum ditemukan. Letakkan folder Dompdf di root project dengan file dompdf/autoload.inc.php.',
        'error',
        'admin/admin_pelanggan.php'
    );
}
require_once $autoloadPath;

if (!class_exists(\Dompdf\Dompdf::class)) {
    throw new RuntimeException('Autoloader ditemukan, tetapi kelas Dompdf tidak tersedia.');
}

$connection = db();
$customers = $connection->query(
    'SELECT u.id, u.name, u.email, u.created_at, u.status, COUNT(o.id) AS order_count
     FROM tb_users u LEFT JOIN tb_orders o ON o.user_id = u.id
     GROUP BY u.id, u.name, u.email, u.created_at, u.status
     ORDER BY u.created_at DESC'
)->fetchAll();

$rows = '';
foreach ($customers as $customer) {
    $rows .= '<tr>'
        . '<td>#USR-' . (int) $customer['id'] . '</td>'
        . '<td>' . e($customer['name']) . '</td>'
        . '<td>' . e($customer['email']) . '</td>'
        . '<td>' . e($customer['created_at']) . '</td>'
        . '<td>' . (int) $customer['order_count'] . '</td>'
        . '<td>' . e($customer['status']) . '</td>'
        . '</tr>';
}
if ($rows === '') {
    $rows = '<tr><td colspan="6">Belum ada data pelanggan.</td></tr>';
}

$generatedAt = date('d-m-Y H:i');
$html = '<!doctype html><html lang="id"><head><meta charset="utf-8"><style>'
    . 'body{font-family:DejaVu Sans,sans-serif;color:#26352a;font-size:10px}'
    . 'h1{color:#2e7d32;margin-bottom:4px}p{color:#65736a;margin-top:0}'
    . 'table{width:100%;border-collapse:collapse;margin-top:18px}'
    . 'th{background:#e8f5e9;color:#245b29}th,td{border:1px solid #cdd8cf;padding:8px;text-align:left}'
    . '</style></head><body>'
    . '<h1>HEALTHY BITE</h1><p>Laporan Data Pelanggan - Dicetak ' . e($generatedAt) . '</p>'
    . '<table><thead><tr><th>ID Member</th><th>Nama</th><th>Email</th><th>Tanggal Bergabung</th><th>Total Pesanan</th><th>Status</th></tr></thead>'
    . '<tbody>' . $rows . '</tbody></table></body></html>';

$pdf = new \Dompdf\Dompdf();
$pdf->loadHtml($html, 'UTF-8');
$pdf->setPaper('A4', 'landscape');
$pdf->render();
$pdf->stream('laporan-pelanggan.pdf', ['Attachment' => true]);
exit;
