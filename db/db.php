<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stripe_payments";

// Conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    echo "Conexión exitosa a la base de datos.<br>";
}

// Crear tabla de productos
$sqlProducts = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    price INT,  -- Precio almacenado en centavos
    stripe_product_id VARCHAR(255),
    stripe_price_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sqlProducts) === TRUE) {
    echo "Tabla 'products' verificada o creada con éxito.<br>";
} else {
    echo "Error al crear la tabla 'products': " . $conn->error . "<br>";
}

// Crear tabla de transacciones
$sqlTransactions = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cust_name VARCHAR(100),
    cust_email VARCHAR(100),
    amount INT,
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sqlTransactions) === TRUE) {
    echo "Tabla 'transactions' verificada o creada con éxito.<br>";
} else {
    echo "Error al crear la tabla 'transactions': " . $conn->error . "<br>";
}

// Cerrar la conexión
// $conn->close();
?>
