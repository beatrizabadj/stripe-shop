<?php
require './db/db.php';

// Seleccionar la base de datos
$conn->query("USE stripe_payments");

// Consultar productos
$result = $conn->query("SELECT * FROM products");

?>

<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda Online</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column h-100">

  <header>
    <div class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a href="#" class="navbar-brand">
          <strong>Tienda Online</strong>
        </a>
        <a href="pagoTarjeta.php" class="btn btn-primary">Carrito</a>
      </div>
    </div>
  </header>

  <main>
    <div class="container">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col">
                  <div class="card w-100 shadow-sm">
                    <img src="<?= $row['image_url'] ?? 'https://via.placeholder.com/300' ?>" class="img-thumbnail" style="max-height: 300px">
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                      <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                      <p class="card-text precio">$<?= number_format($row['price'], 2) ?></p>
                      <button class="btn btn-success agregar-carrito" 
                      data-id="<?= $row['id'] ?>" 
                      data-nombre="<?= htmlspecialchars($row['name']) ?>" 
                      data-precio="<?= number_format($row['price'], 2) ?>">Agregar</button>

                    </div>
                  </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No se encontraron productos.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer class="footer text-lg-start bg-primary bg-gradient mt-auto">
    <div class="container text-md-start pt-2 pb-1">
        <p class="text-white h3">Tienda Online CDP</p>
        <p class="mt-1 text-white">&copy; Natalia - Beatriz</p>
    </div>
  </footer>

  <script src="carrito.js"></script>
</body>
</html>
<?php
$conn->close();
?>

