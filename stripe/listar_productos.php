<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../db/db.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Obtener productos desde Stripe
$stripeProducts = \Stripe\Product::all();

echo "<h2>Productos en Stripe:</h2>";
foreach ($stripeProducts->data as $product) {
    echo "<p>{$product->name} - {$product->description} - Precio: $" . number_format($product->price, 2) . "</p>";}

// Asegurarse de que la conexión a la base de datos es correcta
$conn->query("USE stripe_payments");

// Obtener productos desde la base de datos
$result = $conn->query("SELECT * FROM products");

echo "<h2>Productos en la Base de Datos:</h2>";
while ($row = $result->fetch_assoc()) {
    echo "<p>{$row['name']} - {$row['description']} - $" . ($row['price']) . "</p>";
}
?>
