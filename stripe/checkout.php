<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Recibir datos del frontend
$input = json_decode(file_get_contents("php://input"), true);
$stripeToken = $input['stripeToken'] ?? null;
$amount = $input['amount'] ?? 0;
$description = $input['description'] ?? "Pago sin descripción";
$cardholderName = $input['cardholderName'] ?? 'Anonimo'; // Recibir el nombre del titular

// Validar que tenemos el token y un monto válido
if (!$stripeToken || $amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

try {
    // Crear el pago con el token
    $charge = \Stripe\Charge::create([
        'amount' => $amount, // Monto en centavos
        'currency' => 'usd',
        'description' => $description,
        'source' => $stripeToken, // Token de la tarjeta
    ]);

    if ($charge->status == 'succeeded') {
        $conn = new mysqli("localhost", "root", "", "stripe_payments");
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        $status = "paid";
        $stmt = $conn->prepare("INSERT INTO transactions (name, amount, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $cardholderName, $amount, $status);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        echo json_encode(['status' => 'success', 'message' => 'Pago exitoso']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Pago fallido']);
    }

} catch (\Stripe\Exception\CardException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error con la tarjeta: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error en el pago: ' . $e->getMessage()]);
}
?>