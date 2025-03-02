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
            <a href="index.php" id="volver" class="btn btn-secondary">Volver</a>
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
                        <div id="card-element" class="form-control p-2"></div>
                    </div>
                    <button id="submit-button" class="btn btn-primary w-100 mt-3">Pagar</button>
                    <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                </form>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Resumen de compra</h5>
                        <ul class="list-group" id="cart-summary"></ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://js.stripe.com/v3/"></script>
    <script src="pagoTarjeta.js"></script>

</body>
</html>
