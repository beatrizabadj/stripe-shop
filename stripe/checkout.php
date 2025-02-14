<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../db/db.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Crear la sesión de Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tomamos el producto desde el formulario
    $productId = $_POST['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    try {
        // Crear una sesión de pago en Stripe
        $checkoutSession = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product['name'],
                            'description' => $product['description'],
                        ],
                        'unit_amount' => $product['price'],
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => 'http://localhost/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost/cancel.php',
        ]);

        // Redirigir al cliente a Stripe Checkout
        header("Location: " . $checkoutSession->url);
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear sesión Checkout</title>
</head>
<body>
    <h1>Selecciona un Producto para Pagar</h1>
    <form method="POST">
        <label for="product_id">Producto:</label>
        <select name="product_id" required>
            <option value="">Selecciona un producto</option>
            <?php
            // Mostrar productos disponibles
            $result = $conn->query("SELECT * FROM products");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . $row['name'] . " - $" . number_format($row['price'] / 100, 2) . "</option>";
            }
            ?>
        </select>
        <button type="submit">Pagar con Stripe</button>
    </form>
</body>
</html>
