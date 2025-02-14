<?php
require '../vendor/autoload.php';
require '../config/config.php';

// Establecer clave secreta de Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

if (isset($_GET['payment_intent_id'])) {
    try {
        // Obtener el PaymentIntent de Stripe
        $paymentIntent = \Stripe\PaymentIntent::retrieve($_GET['payment_intent_id']);

        // Verificar si se requiere una acción adicional (por ejemplo, autenticación 3D Secure)
        if ($paymentIntent->status == 'requires_action' || $paymentIntent->status == 'requires_source_action') {
            // Si se requiere acción (como 3D Secure), confirmar el pago
            $confirmPaymentIntent = $paymentIntent->confirm([
                'return_url' => 'http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/public/success.php' // La URL de retorno después de la autenticación
            ]);
            
            // Redirigir al cliente para completar la acción (autenticación)
            header('Location: ' . $confirmPaymentIntent->next_action->redirect_to_url->url);
            exit;
        }

        // Si el pago fue exitoso, redirigir a la página de éxito
        if ($paymentIntent->status == 'succeeded') {
            header("Location: success.php");
            exit;
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No se ha encontrado el PaymentIntent ID.";
}
