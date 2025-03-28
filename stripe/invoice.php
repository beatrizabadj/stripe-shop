<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db/db.php';

// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json'); // Asegurar que la respuesta sea JSON

// Recibir datos del frontend
$input = json_decode(file_get_contents("php://input"), true);
$invoiceId = $input['invoiceId'] ?? null;

if (!$invoiceId) {
    echo json_encode(['status' => 'error', 'message' => 'ID de factura inválido']);
    exit;
}

// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "stripe_payments");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Conexión fallida a la base de datos: ' . $conn->connect_error]);
    exit;
}

// Obtener la información de la transacción
$stmt = $conn->prepare("SELECT name, amount, status, created_at, description FROM transactions WHERE invoice_id = ?");
$stmt->bind_param("s", $invoiceId);
$stmt->execute();
$stmt->bind_result($cardholderName, $amount, $status, $createdAt, $description);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!$cardholderName) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró la factura en la base de datos']);
    exit;
}

// Formatear fecha
$fechaFactura = date('d/m/Y', strtotime($createdAt));

// Generar la factura en HTML
$facturaHTML = "
    <html>
    <head>
        <title>Factura con ID</title>
        <style>
            body { font-family: Arial, sans-serif; }
            h1 { color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .total { font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>Factura #{$invoiceId}</h1>
        <p><strong>Cliente:</strong> {$cardholderName}</p>
        <p><strong>Fecha:</strong> {$fechaFactura}</p>
        <p><strong>Estado:</strong> {$status}</p>
        <table>
            <tr>
                <th>Descripción</th>
                <th>Precio</th>
            </tr>
            <tr>
                <td>{$description}</td>
                <td>$" . number_format($amount, 2) . "</td>
            </tr>
            <tr>
                <td class='total'>Total</td>
                <td class='total'>$" . number_format($amount, 2) . "</td>
            </tr>
        </table>
    </body>
    </html>
";

// Devolver la factura en formato HTML dentro de la respuesta JSON
echo json_encode(['status' => 'success', 'facturaHTML' => $facturaHTML]);
?>