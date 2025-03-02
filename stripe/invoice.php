<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

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

try {
    // Obtener la factura de Stripe
    $invoice = \Stripe\Invoice::retrieve($invoiceId);

    // Obtener el nombre del titular de la tarjeta desde la base de datos
    $conn = new mysqli("localhost", "root", "", "stripe_payments");
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT name FROM transactions WHERE invoice_id = ?");
    $stmt->bind_param("s", $invoiceId);
    $stmt->execute();
    $stmt->bind_result($cardholderName);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    // Generar la factura en HTML
    $facturaHTML = "
        <html>
        <head>
            <title>Factura</title>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { color: #333; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h1>Factura #{$invoice->id}</h1>
            <p><strong>Cliente:</strong> {$cardholderName}</p>
            <p><strong>Fecha:</strong> " . date('d/m/Y', $invoice->created) . "</p>
            <p><strong>Monto:</strong> $" . number_format($invoice->total / 100, 2) . "</p>
            <p><strong>Estado:</strong> {$invoice->status}</p>
            <table>
                <tr>
                    <th>Descripción</th>
                    <th>Monto</th>
                </tr>
    ";

    foreach ($invoice->lines->data as $item) {
        $facturaHTML .= "
            <tr>
                <td>{$item->description}</td>
                <td>$" . number_format($item->amount / 100, 2) . "</td>
            </tr>
        ";
    }

    $facturaHTML .= "
            </table>
        </body>
        </html>
    ";

    // Devolver la factura en formato HTML
    echo json_encode(['status' => 'success', 'facturaHTML' => $facturaHTML]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener la factura: ' . $e->getMessage()]);
}
?>