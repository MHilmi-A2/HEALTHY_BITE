<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
require_post();
verify_csrf();

if (($_SESSION['role'] ?? '') !== 'customer') {
    redirect_with_flash('Hanya pelanggan yang dapat membuat pesanan.', 'error', 'Menu.php');
}

$phone = trim(request_string($_POST, 'phone'));
$address = trim(request_string($_POST, 'address'));
$items = json_decode(request_string($_POST, 'items'), true);

if (
    !preg_match('/^[0-9+(). -]{8,20}$/', $phone) ||
    $address === '' ||
    strlen($address) > 1000 ||
    !is_array($items) ||
    $items === [] ||
    count($items) > 50
) {
    redirect_with_flash('Nomor telepon, alamat, atau rincian pesanan tidak valid.', 'error', 'Menu.php');
}

$connection = db();
$requestedItems = [];
$totalCents = 0;
$toDecimal = static fn (int $cents): string => intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
$menuQuery = $connection->prepare(
    'SELECT id, categories, price FROM tb_categories WHERE id = :id AND is_available = 1'
);

foreach ($items as $item) {
    if (!is_array($item)) {
        redirect_with_flash('Data item pesanan tidak valid.', 'error', 'Menu.php');
    }

    $menuId = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
    if ($menuId === false || $quantity === false || $menuId < 1 || $quantity < 1 || $quantity > 100) {
        redirect_with_flash('Menu atau jumlah pesanan tidak valid.', 'error', 'Menu.php');
    }

    $menuQuery->execute(['id' => $menuId]);
    $menu = $menuQuery->fetch();
    if (!$menu) {
        redirect_with_flash('Salah satu menu sudah tidak tersedia. Muat ulang halaman menu.', 'error', 'Menu.php');
    }

    $priceParts = explode('.', (string) $menu['price'], 2);
    $unitPriceCents = ((int) $priceParts[0] * 100) + (int) str_pad($priceParts[1] ?? '0', 2, '0');
    $totalCents += $unitPriceCents * $quantity;
    $requestedItems[] = [
        'id' => (int) $menu['id'],
        'name' => $menu['categories'],
        'quantity' => $quantity,
        'price' => $toDecimal($unitPriceCents),
    ];
}
$total = $toDecimal($totalCents);

$connection->beginTransaction();
try {
    $insertOrder = $connection->prepare(
        "INSERT INTO tb_orders (user_id, phone, address, total) VALUES (:user_id, :phone, :address, :total)"
    );
    $insertOrder->execute([
        'user_id' => (int) $_SESSION['user_id'],
        'phone' => $phone,
        'address' => $address,
        'total' => $total,
    ]);
    $orderId = (int) $connection->lastInsertId();

    $insertItem = $connection->prepare(
        'INSERT INTO tb_order_items (order_id, menu_id, menu_name, quantity, unit_price) VALUES (:order_id, :menu_id, :menu_name, :quantity, :unit_price)'
    );
    foreach ($requestedItems as $item) {
        $insertItem->execute([
            'order_id' => $orderId,
            'menu_id' => $item['id'],
            'menu_name' => $item['name'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
        ]);
    }

    $connection->commit();
} catch (Throwable $error) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    throw $error;
}

set_flash('Pesanan #' . $orderId . ' berhasil dibuat.', 'success');
header('Location: my_orders.php');
exit;
