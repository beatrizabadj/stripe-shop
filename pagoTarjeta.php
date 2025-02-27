<?php
require './db/db.php';

// Realizamos la consulta a la base de datos
$result = $conn->query("SELECT * FROM products");
$productos = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = [
            'name' => $row['name'],
            'price' => $row['price'] / 100, // Convertimos de centavos a USD
            'id' => $row['id']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar - Tienda Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="#" class="navbar-brand"><strong>Tienda Online</strong></a>
            </div>
        </div>
    </header>

    <main class="container mt-4">
        <h2 class="mb-4">Procesar Pago</h2>
        <div class="row">
            <div class="col-md-6">
                <form id="payment-form">
                    <div class="mb-3">
                        <label for="cardholder-name" class="form-label">Nombre en la tarjeta</label>
                        <input type="text" class="form-control" id="cardholder-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="card-element" class="form-label">Detalles de la tarjeta</label>
                        <div id="card-element" class="form-control p-2">
                            <!-- Stripe Element -->
                        </div>
                    </div>
                    <button id="submit-button" class="btn btn-primary w-100 mt-3">Pagar</button>
                    <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                </form>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Resumen de compra</h5>
                        <ul class="list-group" id="cart-summary" data-productos='<?php echo json_encode($productos); ?>'>
                            <!-- Aquí se agregarán los productos seleccionados -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer text-lg-start bg-primary bg-gradient mt-auto">
        <div class="container text-md-start pt-2 pb-1">
            <p class="text-white h3">Tienda Online CDP</p>
            <p class="mt-1 text-white">&copy; Natalia - Beatriz</p>
        </div>
    </footer>

    <script src="https://js.stripe.com/v3/"></script>

    <script src="pagoTarjeta.js"></script>

</body>
</html>

