<?php
require_once __DIR__ . '/db/db.php';

$sql = "SELECT id, name, description, price FROM products";
$result = $conn->query($sql);

$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
?>
