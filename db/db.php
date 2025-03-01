<?php
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
    name VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2),
    stripe_product_id VARCHAR(255),
    stripe_price_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sqlProducts) === FALSE) {
    die("Error al crear la tabla 'products': " . $conn->error);
}

// Crear tabla de transacciones
$sqlTransactions = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cust_name VARCHAR(100),
    cust_email VARCHAR(100),
    amount DECIMAL(10,2),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sqlTransactions) === FALSE) {
    die("Error al crear la tabla 'transactions': " . $conn->error);
}

// Array de productos
$products = [
    ["Camiseta Negra", "Camiseta de algodón 100% en color negro", 19.99, "prod_001", "price_001"],
    ["Pantalón Vaquero", "Pantalón de mezclilla azul clásico", 39.99, "prod_002", "price_002"],
    ["Zapatillas Deportivas", "Zapatillas cómodas para correr", 59.99, "prod_003", "price_003"],
    ["Mochila Urbana", "Mochila resistente para el día a día", 29.99, "prod_004", "price_004"],
    ["Reloj Digital", "Reloj con pantalla LED y cronómetro", 49.99, "prod_005", "price_005"],
    ["Gorra Snapback", "Gorra ajustable con diseño moderno", 14.99, "prod_006", "price_006"],
    ["Auriculares Bluetooth", "Auriculares inalámbricos con gran sonido", 69.99, "prod_007", "price_007"],
    ["Sudadera con Capucha", "Sudadera gruesa con capucha para el frío", 34.99, "prod_008", "price_008"],
    ["Bolso de Cuero", "Bolso elegante de cuero genuino", 79.99, "prod_009", "price_009"],
    ["Gafas de Sol", "Gafas con protección UV y estilo moderno", 24.99, "prod_010", "price_010"]
];

// Usar consulta preparada para insertar productos
$stmt = $conn->prepare("INSERT INTO products (name, description, price, stripe_product_id, stripe_price_id) 
                        SELECT ?, ?, ?, ?, ? 
                        WHERE NOT EXISTS (SELECT 1 FROM products WHERE stripe_product_id = ?)");

// Verificar si la consulta preparada se creó correctamente
if (!$stmt) {
    die("Error en la consulta preparada: " . $conn->error);
}
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

foreach ($products as $product) {
    $productPriceInCents = $product[2] * 100;  // Convertir el precio a centavos

    try {
        // Crear producto en Stripe
        $stripeProduct = \Stripe\Product::create([
            'name' => $product[0],
            'description' => $product[1],
        ]);

        // Crear precio en Stripe
        $stripePrice = \Stripe\Price::create([
            'unit_amount' => $productPriceInCents,  // Precio en centavos
            'currency' => 'usd',
            'product' => $stripeProduct->id,
        ]);

        // Insertar en la base de datos
        $stmt->bind_param("ssisss", $product[0], $product[1], $productPriceInCents, $stripeProduct->id, $stripePrice->id, $stripeProduct->id);

        if ($stmt->execute() === FALSE) {
            echo "Error al insertar '{$product[0]}': " . $stmt->error . "<br>";
        } else {
            echo "✅ Producto '{$product[0]}' insertado correctamente.<br>";
        }

    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo "Error al crear el producto '{$product[0]}' en Stripe: " . $e->getMessage() . "<br>";
    }
}

$stmt->close();
// $conn->close();

echo "Productos insertados con éxito.";
?>
