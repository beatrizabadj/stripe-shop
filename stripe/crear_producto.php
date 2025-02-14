<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../db/db.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['product_name'];
    $productDesc = $_POST['product_desc'];
    $productPrice = $_POST['product_price'] * 100; // Convertimos a centavos

    try {
        // Crear producto en Stripe
        $product = \Stripe\Product::create([
            'name' => $productName,
            'description' => $productDesc,
        ]);

        // Crear precio en Stripe
        $price = \Stripe\Price::create([
            'unit_amount' => $productPrice,
            'currency' => 'usd',
            'product' => $product->id,
        ]);

        // Guardar en la base de datos
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stripe_product_id, stripe_price_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $productName, $productDesc, $productPrice, $product->id, $price->id);
        $stmt->execute();

        echo "✅ Producto guardado con éxito.";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardar Producto</title>
</head>
<body>
    <h1>Agregar Nuevo Producto</h1>
    <form method="POST">
        <label>Nombre del Producto:</label>
        <input type="text" name="product_name" required><br>
        
        <label>Descripción:</label>
        <input type="text" name="product_desc" required><br>
        
        <label>Precio (USD):</label>
        <input type="number" name="product_price" required><br>
        
        <button type="submit">Guardar Producto</button>
    </form>
</body>
</html>
