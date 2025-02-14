<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../db/db.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

if (isset($_POST['stripeToken'])) {
    try {
        $token = $_POST['stripeToken'];
        $custName = $_POST['custName'];
        $custEmail = $_POST['custEmail'];

        // Crear Cliente en Stripe
        $customer = \Stripe\Customer::create([
            'email' => $custEmail,
            'source' => $token,
        ]);

        // Cobrar al Cliente
        $charge = \Stripe\Charge::create([
            'customer' => $customer->id,
            'amount' => 5000, // $50.00 en centavos
            'currency' => 'usd',
            'description' => 'Pago de prueba',
        ]);

        // Guardar Transacción en la Base de Datos
        $stmt = $conn->prepare("INSERT INTO transactions (cust_name, cust_email, amount, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $custName, $custEmail, $charge->amount, $charge->status);
        $stmt->execute();

        header("Location: ../public/success.php");
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No se recibió un token de Stripe.";
}
?>
