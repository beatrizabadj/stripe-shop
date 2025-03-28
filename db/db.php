<<<<<<< Updated upstream
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stripe_payments";

// Conectar al servidor MySQL
$conn = new mysqli($servername, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Crear la base de datos si no existe
$sqlCreateDB = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sqlCreateDB) === FALSE) {
    die("Error creando la base de datos: " . $conn->error);
}

// Seleccionar la base de datos
$conn->select_db($dbname);

// Crear tabla de productos
$sqlProducts = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    stripe_product_id VARCHAR(255) UNIQUE NOT NULL,
    stripe_price_id VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sqlProducts) === FALSE) {
    die("Error al crear la tabla 'products': " . $conn->error);
}

// Crear tabla de transacciones
$sqlTransactions = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    invoice_id VARCHAR(255),
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sqlTransactions) === FALSE) {
    die("Error al crear la tabla 'transactions': " . $conn->error);
}


// Array de productos
$products = [
    ["Camiseta Negra", "Camiseta de algodón 100% en color negro", 1999, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/camiseta-negra.png"],
    ["Pantalón Vaquero", "Pantalón de mezclilla azul clásico", 3999, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/pantalon-vaquero.png"],
    ["Zapatillas Deportivas", "Zapatillas cómodas para correr", 5999, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/zapatillas-deportivas.png"],
    ["Mochila Urbana", "Mochila resistente para el día a día", 2999, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/mochila-urbana.png"],
    ["Reloj Digital", "Reloj con pantalla LED y cronómetro", 4999, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/reloj-digital.png"],
    ["Gorra Snapback", "Gorra ajustable con diseño moderno", 1499, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/gorra.png"],
    ["Auriculares Bluetooth", "Auriculares inalámbricos con gran sonido", 6999, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/auriculares.png"],
    ["Sudadera con Capucha", "Sudadera gruesa con capucha para el frío", 3499, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/sudadera.png"],
    ["Bolso de Cuero", "Bolso elegante de cuero genuino", 7999, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/bolso-cuero.png"],
    ["Gafas de Sol", "Gafas con protección UV y estilo moderno", 2499, "http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/img/gafas.png"]
];

// Preparar consultas para evitar duplicados
$checkStmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
$insertStmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, stripe_product_id, stripe_price_id) VALUES (?, ?, ?, ?, ?, ?)");

// Verificar si las consultas preparadas se crearon correctamente
if (!$checkStmt || !$insertStmt) {
    die("Error en la consulta preparada: " . $conn->error);
}

// Conectar con Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

foreach ($products as $product) {
    $productName = $product[0];
    $productDescription = $product[1];
    $productPriceInCents = $product[2];  // Convertir el precio a centavos
    $productImageUrl = $product[3];

    // Verificar si el producto ya existe en la base de datos
    $checkStmt->bind_param("s", $productName);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {  // Si no existe, lo creamos en Stripe y lo insertamos en la BD
            // Crear producto en Stripe
            $stripeProduct = \Stripe\Product::create([
                'name' => $productName,
                'description' => $productDescription,
            ]);

            // Crear precio en Stripe
            $stripePrice = \Stripe\Price::create([
                'unit_amount' => $productPriceInCents,  // Precio en centavos
                'currency' => 'usd',
                'product' => $stripeProduct->id,
            ]);

    //         // Insertar en la base de datos
$insertStmt->bind_param("ssisss", $productName, $productDescription, $productPriceInCents, $productImageUrl, $stripeProduct->id, $stripePrice->id);
    $insertStmt->execute();
    //             echo "✅ Producto '{$productName}' insertado correctamente.<br>";
    //         } else {
    //             echo "❌ Error al insertar '{$productName}': " . $insertStmt->error . "<br>";
    //         }

    //     } catch (\Stripe\Exception\ApiErrorException $e) {
    //         echo "❌ Error al crear el producto '{$productName}' en Stripe: " . $e->getMessage() . "<br>";
    //     }
    // } else {
    //     echo "⚠️ Producto '{$productName}' ya existe en la base de datos. No se insertó de nuevo.<br>";
    
        }
    }


// Cerrar conexiones
$checkStmt->close();
$insertStmt->close();
// $conn->close();
// echo "✅ Base de datos insertados con éxito.";

// echo "✅ Productos insertados con éxito.";
// echo '<a href="../index.php" class="btn btn-primary mt-3">Volver a la tienda</a>'

?>
=======
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stripe_payments";

// Conectar al servidor MySQL
$conn = new mysqli($servername, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Crear la base de datos si no existe
$sqlCreateDB = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sqlCreateDB) === FALSE) {
    die("Error creando la base de datos: " . $conn->error);
}

// Seleccionar la base de datos
$conn->select_db($dbname);

// Crear tabla de productos
$sqlProducts = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stripe_product_id VARCHAR(255) UNIQUE NOT NULL,
    stripe_price_id VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sqlProducts) === FALSE) {
    die("Error al crear la tabla 'products': " . $conn->error);
}

// Crear tabla de transacciones
$sqlTransactions = "CREATE TABLE IF NOT EXISTS transactions (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_id` varchar(50) NOT NULL,
    `name` varchar(100) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `status` varchar(20) NOT NULL,
    `description` text,
    `products` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `invoice_id` (`invoice_id`)
    )";
if ($conn->query($sqlTransactions) === FALSE) {
    die("Error al crear la tabla 'transactions': " . $conn->error);
}


// Array de productos
$products = [
    ["Camiseta Negra", "Camiseta de algodón 100% en color negro", 1999],
    ["Pantalón Vaquero", "Pantalón de mezclilla azul clásico", 3999],
    ["Zapatillas Deportivas", "Zapatillas cómodas para correr", 5999],
    ["Mochila Urbana", "Mochila resistente para el día a día", 2999],
    ["Reloj Digital", "Reloj con pantalla LED y cronómetro", 4999],
    ["Gorra Snapback", "Gorra ajustable con diseño moderno", 1499],
    ["Auriculares Bluetooth", "Auriculares inalámbricos con gran sonido", 6999],
    ["Sudadera con Capucha", "Sudadera gruesa con capucha para el frío", 3499],
    ["Bolso de Cuero", "Bolso elegante de cuero genuino", 7999],
    ["Gafas de Sol", "Gafas con protección UV y estilo moderno", 2499]
];

// Preparar consultas para evitar duplicados
$checkStmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
$insertStmt = $conn->prepare("INSERT INTO products (name, description, price, stripe_product_id, stripe_price_id) VALUES (?, ?, ?, ?, ?)");

// Verificar si las consultas preparadas se crearon correctamente
if (!$checkStmt || !$insertStmt) {
    die("Error en la consulta preparada: " . $conn->error);
}

// Conectar con Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

foreach ($products as $product) {
    $productName = $product[0];
    $productDescription = $product[1];
    $productPriceInCents = $product[2];  // Convertir el precio a centavos

    // Verificar si el producto ya existe en la base de datos
    $checkStmt->bind_param("s", $productName);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {  // Si no existe, lo creamos en Stripe y lo insertamos en la BD
            // Crear producto en Stripe
            $stripeProduct = \Stripe\Product::create([
                'name' => $productName,
                'description' => $productDescription,
            ]);

            // Crear precio en Stripe
            $stripePrice = \Stripe\Price::create([
                'unit_amount' => $productPriceInCents,  // Precio en centavos
                'currency' => 'usd',
                'product' => $stripeProduct->id,
            ]);

    //         // Insertar en la base de datos
    $insertStmt->bind_param("ssiss", $productName, $productDescription, $productPriceInCents, $stripeProduct->id, $stripePrice->id);
    $insertStmt->execute();
    //             echo "✅ Producto '{$productName}' insertado correctamente.<br>";
    //         } else {
    //             echo "❌ Error al insertar '{$productName}': " . $insertStmt->error . "<br>";
    //         }

    //     } catch (\Stripe\Exception\ApiErrorException $e) {
    //         echo "❌ Error al crear el producto '{$productName}' en Stripe: " . $e->getMessage() . "<br>";
    //     }
    // } else {
    //     echo "⚠️ Producto '{$productName}' ya existe en la base de datos. No se insertó de nuevo.<br>";
    
        }
    }


// Cerrar conexiones
$checkStmt->close();
$insertStmt->close();
// $conn->close();
// echo "✅ Base de datos insertados con éxito.";

// echo "✅ Productos insertados con éxito.";
// echo '<a href="../index.php" class="btn btn-primary mt-3">Volver a la tienda</a>'

?>
>>>>>>> Stashed changes
