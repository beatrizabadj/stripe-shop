<?php
require './db/db.php';

// Realizamos la consulta a la base de datos
$result = $conn->query("SELECT * FROM products");

?>

<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda Online</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>

<body class="d-flex flex-column h-100">

  <header>
    <div class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a href="#" class="navbar-brand">
          <strong>Tienda Online</strong>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarHeader">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a href="#" class="nav-link active">Catalogo</a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">Contacto</a>
            </li>
          </ul>
          <a href="pagoTarjeta.php" class="btn btn-primary">Carrito</a>
        </div>
      </div>
    </div>
  </header>

  <main>
    <div class="container">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">

        <?php
        // Verificar si hay productos en la base de datos
        if ($result->num_rows > 0) {
            // Recorrer todos los productos
            while ($row = $result->fetch_assoc()) {
                $productName = $row['name'];
                $productDesc = $row['description'];
                $productPrice = $row['price'] / 100; // Convertimos de centavos a USD
                $productImage = "https://via.placeholder.com/300"; // Puedes agregar lógica para tener imágenes dinámicas
                ?>
                <div class="col">
                  <div class="card w-100 shadow-sm">
                    <img src="<?php echo $productImage; ?>" class="img-thumbnail" style="max-height: 300px">
                    <div class="card-body">
                      <h5 class="card-title"><?php echo $productName; ?></h5>
                      <p class="card-text"><?php echo $productDesc; ?></p>
                      <p id="price" class="card-text">$ <?php echo number_format($productPrice, 2); ?></p>
                      <div class="d-flex justify-content-between align-items-center">
                        <a href="#" class="btn btn-success">Agregar</a>
                      </div>
                    </div>
                  </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No se encontraron productos.</p>";
        }
        ?>

      </div>
    </div>
  </main>

  <footer class="footer text-lg-start bg-primary bg-gradient mt-auto">
    <div class="container text-md-start pt-2 pb-1">
        <div class="row mt-3">
            <div class="col-12 col-lg-3 col-sm-12 mb-2">
                <p class="text-white h3">
                    Tienda Online CDP
                </p>
                <p class="mt-1 text-white">
                    &copy; Natalia - Beatriz
                </p>
            </div>
        </div>
    </div>
  </footer>
<script src="carrito.js"> </script>

</body>

</html>
