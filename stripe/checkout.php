<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$input = json_decode(file_get_contents("php://input"), true);

$line_items = [];
foreach ($input['productos'] as $producto) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => ['name' => $producto['nombre']],
            'unit_amount' => $producto['precio'] * 100
        ],
        'quantity' => 1
    ];
}

$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $line_items,
    'mode' => 'payment',
    'success_url' => 'http://localhost/success.php',
    'cancel_url' => 'http://localhost/cancel.php'
]);

echo json_encode(['url' => $session->url]);
?>
