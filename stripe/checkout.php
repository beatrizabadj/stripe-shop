<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Configuración de headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Obtener y validar datos
$input = json_decode(file_get_contents("php://input"), true);
$stripeToken = $input['stripeToken'] ?? null;
$amount = $input['amount'] ?? 0;
$description = $input['description'] ?? "Compra en tienda";
$cardholderName = $input['cardholderName'] ?? 'Cliente';
$products = $input['products'] ?? [];

if (!$stripeToken || $amount <= 0 || !is_numeric($amount) || empty($products)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos de pago inválidos']);
    exit;
}

try {
    // 1. Crear cliente en Stripe
    $customer = \Stripe\Customer::create([
        'email' => 'cliente@tienda.com',
        'source' => $stripeToken,
        'name' => $cardholderName,
    ]);

    // 2. Crear cargo/pago
    $charge = \Stripe\Charge::create([
        'amount' => $amount,
        'currency' => 'usd',
        'description' => $description,
        'customer' => $customer->id,
    ]);

    // 3. Procesar solo si el pago fue exitoso
    if ($charge->status === 'succeeded') {
        $conn = new mysqli("localhost", "root", "", "stripe_payments");
        if ($conn->connect_error) {
            throw new Exception("Error de conexión a la base de datos");
        }

        // Generar ID único para la factura
        $invoiceId = 'INV-' . time() . '-' . bin2hex(random_bytes(3));
        $productsJson = json_encode($products);
        $amountInEuros = $amount / 100; // Convertir a euros

        // Insertar transacción
        $stmt = $conn->prepare("INSERT INTO transactions (invoice_id, name, amount, status, description, products) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", 
            $invoiceId,
            $cardholderName,
            $amountInEuros,
            $charge->status,
            $description,
            $productsJson
        );

        if ($stmt->execute()) {
            $response = [
                'status' => 'success',
                'message' => 'Pago procesado correctamente',
                'invoiceId' => $invoiceId,
                'chargeId' => $charge->id
            ];
        } else {
            throw new Exception("Error al guardar la transacción");
        }

        $stmt->close();
        $conn->close();

        echo json_encode($response);
        exit;
    }

    throw new Exception("El pago no se completó correctamente");

} catch (\Stripe\Exception\CardException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en la tarjeta: ' . $e->getError()->message
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>