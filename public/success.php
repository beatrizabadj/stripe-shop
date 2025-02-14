<?php
require '../vendor/autoload.php';
require '../config/config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

if (isset($_GET['session_id'])) {
    try {
        $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);
        if ($session->payment_status == 'paid') {
            echo "<h1>¡Pago Exitoso!</h1>";
            echo "<p>Gracias por tu compra. El pago ha sido completado correctamente.</p>";
        } else {
            echo "<h1>Pago no completado.</h1>";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "<h1>Error en la sesión de pago.</h1>";
}
?>
