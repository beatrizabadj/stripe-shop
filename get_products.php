<?php
require './db/db.php';

header('Content-Type: application/json'); 

$result = $conn->query("SELECT id, name, description, price, stripe_price_id FROM products");

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        "id" => $row['id'],
        "name" => $row['name'],
        "description" => $row['description'],
        "price" => number_format($row['price'] / 100, 2),
        "stripe_price_id" => $row['stripe_price_id']
    ];
}

echo json_encode($products);
?>
