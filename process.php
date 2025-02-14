<?php
require 'vendor/autoload.php';
require 'config.php';
require 'public/db.php';

// Establecer clave secreta de Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Verificar si el token de Stripe fue recibido
if (isset($_POST['stripeToken'])) {
    try {
        $token = $_POST['stripeToken'];
        $custName = $_POST['custName'];
        $custEmail = $_POST['custEmail'];

        // Crear un cliente en Stripe con el token
        $customer = \Stripe\Customer::create([
            'email' => $custEmail,
            'source' => $token,
        ]);

        // Crear un cargo para el cliente
        $charge = \Stripe\Charge::create([
            'customer' => $customer->id,
            'amount' => 5000, // $50.00 en centavos
            'currency' => 'usd',
            'description' => 'Pago de prueba',
        ]);

        // Si el pago fue exitoso, redirigir a la página de éxito
        if ($charge->status == 'succeeded') {
            // Guardar en la base de datos
            $stmt = $conn->prepare("INSERT INTO transactions (cust_name, cust_email, amount, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $custName, $custEmail, $charge->amount, $charge->status);
            $stmt->execute();

            header("Location: success.php");
            exit;
        } else {
            header("Location: error.php");
            exit;
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No se recibió un token de Stripe.";
}
?>