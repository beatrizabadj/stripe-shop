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
$stripeToken = $input['stripeToken'] ?? null;
$amount = $input['amount'] ?? 0;
$description = $input['description'] ?? "Pago sin descripción";
$cardholderName = $input['cardholderName'] ?? 'Anonimo'; // Recibir el nombre del titular

// Validar que tenemos el token y un monto válido
if (!$stripeToken || $amount <= 0 || !is_numeric($amount)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

try {
    // Crear un cliente de Stripe (si no existe)
    $customer = \Stripe\Customer::create([
        'email' => 'cliente@dominio.com', // Puedes pasar el correo electrónico del cliente aquí
        'source' => $stripeToken, // Token de la tarjeta
        'name' => $cardholderName, // Guardar el nombre del titular en Stripe
    ]);

    // Obtener el ID de la fuente de pago del cliente
    $sourceId = $customer->default_source;

    // Crear el pago usando el ID de la fuente del cliente
    $charge = \Stripe\Charge::create([
        'amount' => $amount, // Monto en centavos
        'currency' => 'usd',
        'description' => $description,
        'customer' => $customer->id, // Usar el ID del cliente
        'source' => $sourceId, // Usar el ID de la fuente del cliente
    ]);

    if ($charge->status == 'succeeded') {
        $conn = new mysqli("localhost", "root", "", "stripe_payments");
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        // Crear la factura en Stripe
        $invoiceItem = \Stripe\InvoiceItem::create([
            'customer' => $customer->id,
            'amount' => $amount, // Monto en centavos
            'currency' => 'usd',
            'description' => $description,
        ]);

        $invoice = \Stripe\Invoice::create([
            'customer' => $customer->id,
            'auto_advance' => true, // Esto avanza automáticamente la factura
        ]);

        // Agregar el IVA (21%)
        $taxRate = \Stripe\TaxRate::create([
            'display_name' => 'IVA',
            'percentage' => 21.0,
            'inclusive' => false,
        ]);

        // Actualizar la factura con el impuesto
        \Stripe\Invoice::update($invoice->id, [
            'default_tax_rates' => [$taxRate->id],
        ]);

        // Finalizar la factura
        $invoice->finalizeInvoice();

        // Guardar la transacción en la base de datos
        $status = "paid";
        $stmt = $conn->prepare("INSERT INTO transactions (name, amount, status, invoice_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $cardholderName, $amount, $status, $invoice->id);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        echo json_encode(['status' => 'success', 'message' => 'Pago exitoso, factura generada', 'invoiceId' => $invoice->id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Pago fallido']);
    }

} catch (\Stripe\Exception\CardException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error con la tarjeta: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error en el pago: ' . $e->getMessage()]);
}
?>