<?php 
require 'config.php';
?>
<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Añadir esta línea -->

    <title>Pago con Stripe</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <form action="process.php" method="POST" id="payment-form">
        <label for="custName">Nombre:</label>
        <input type="text" name="custName" required><br>

        <label for="custEmail">Email:</label>
        <input type="email" name="custEmail" required><br>

        <!-- Aquí es donde Stripe Elements se integrará -->
        <div id="card-element">
            <!-- Un espacio para el campo de la tarjeta -->
        </div>

        <div id="card-errors" role="alert"></div>

        <button type="submit">Pagar</button>
    </form>

    <script>
        // Inicializar Stripe con la clave pública
        var stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        var elements = stripe.elements();

        // Crear un objeto de tarjeta de Stripe Elements
        var card = elements.create('card');
        
        // Montar el objeto de tarjeta en el DOM
        card.mount('#card-element');

        // Manejar los errores de validación
        card.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Manejar el envío del formulario
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Crear el token de la tarjeta usando Stripe
            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // Mostrar el error en la interfaz de usuario
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    // Enviar el token a tu servidor para procesar el pago
                    var form = document.getElementById('payment-form');
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'stripeToken');
                    hiddenInput.setAttribute('value', result.token.id);
                    form.appendChild(hiddenInput);
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>
