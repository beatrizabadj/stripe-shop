<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$invoiceId = $input['invoiceId'] ?? null;

if (!$invoiceId) {
    echo json_encode(['status' => 'error', 'message' => 'ID de factura inválido']);
    exit;
}

// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "stripe_payments");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Conexión fallida: ' . $conn->connect_error]);
    exit;
}

// Obtener la información de la transacción (AHORA INCLUYENDO PRODUCTOS)
$stmt = $conn->prepare("SELECT name, amount, status, created_at, description, products FROM transactions WHERE invoice_id = ?");
$stmt->bind_param("s", $invoiceId);
$stmt->execute();
$stmt->bind_result($cardholderName, $amount, $status, $createdAt, $description, $productsJson);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!$cardholderName) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró la factura']);
    exit;
}

// Decodificar los productos del carrito
$products = json_decode($productsJson, true);
$fechaFactura = date('d/m/Y H:i:s', strtotime($createdAt));

// Construir tabla de productos
$productosHTML = '';
$subtotal = 0;

foreach ($products as $producto) {
    $precio = $producto['precio']; // Convertir de centavos a euros
    $cantidad = $producto['cantidad'] ?? 1;
    $totalProducto = $precio * $cantidad;
    $subtotal += $totalProducto;
    
    $productosHTML .= "
        <tr>
            <td>{$producto['nombre']}</td>
            <td>{$cantidad}</td>
            <td>€" . number_format($precio, 2) . "</td>
            <td>€" . number_format($totalProducto, 2) . "</td>
        </tr>
    ";
}

$iva = $subtotal * 0.21;
$total = $subtotal + $iva;

// Generar la factura en HTML
$facturaHTML = "
<!DOCTYPE html>
<html>
<head>
    <title>Factura #{$invoiceId}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .invoice-header { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; }
    </style>
</head>
<body>
    <div class='invoice-header'>
        <h1>Factura #{$invoiceId}</h1>
        <p><strong>Cliente:</strong> {$cardholderName}</p>
        <p><strong>Fecha:</strong> {$fechaFactura}</p>
        <p><strong>Estado:</strong> {$status}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            {$productosHTML}
            <tr>
                <td colspan='3' class='text-right'>Subtotal:</td>
                <td>€" . number_format($subtotal, 2) . "</td>
            </tr>
            <tr>
                <td colspan='3' class='text-right'>IVA (21%):</td>
                <td>€" . number_format($iva, 2) . "</td>
            </tr>
            <tr class='total-row'>
                <td colspan='3' class='text-right'>Total:</td>
                <td>€" . number_format($total, 2) . "</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
";

echo json_encode(['status' => 'success', 'facturaHTML' => $facturaHTML]);
?>