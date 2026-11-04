<?php

function rupiah($angka)
{
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function cart_count()
{
    $jumlah = 0;

    foreach ($_SESSION["cart"] ?? [] as $item) {
        $jumlah += (int)($item["qty"] ?? 0);
    }

    return $jumlah;
}

function get_products($conn, $featured = false, $limit = 0)
{
    $sql = "SELECT * FROM products";

    if ($featured) {
        $sql .= " WHERE is_featured = 1";
    }

    $sql .= " ORDER BY id DESC";

    if ($limit > 0) {
        $sql .= " LIMIT " . (int)$limit;
    }

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cart_items($conn)
{
    $items = [];

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return $items;
    }

    foreach ($_SESSION['cart'] as $key => $cartItem) {
        $id = (int)$cartItem['id'];
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if ($product) {
            $qty = (int)$cartItem['qty'];
            $price = (int)$product['price'];
            $items[] = [
                'key' => $key,
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $price,
                'qty' => $qty,
                'sel_color' => $cartItem['color'] ?? 'Default',
                'sel_size' => $cartItem['size'] ?? 'One Size',
                'line_total' => $price * $qty,
                'image' => $product['image'] ?? ''
            ];
        }
    }

    return $items;
}

function cart_subtotal($items)
{
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += (int)$item['line_total'];
    }
    return $subtotal;
}
?>