window.onload = () => {
    // Event listener para el botón de "Volver" que vacía el carrito
    const botonVolver = document.getElementById("volver");
    if (botonVolver) {
        botonVolver.addEventListener("click", () => {
            // Vaciar el carrito (localStorage)
            localStorage.removeItem('carrito');
            console.log("Carrito vaciado.");
        });
    }

    // Obtener los productos seleccionados desde localStorage
    const productosSeleccionados = JSON.parse(localStorage.getItem('carrito')) || [];
    console.log(productosSeleccionados); // Verifica los productos

    const cartSummary = document.getElementById("cart-summary");
    let total = 0;

    // Si no hay productos seleccionados, muestra un mensaje
    if (productosSeleccionados.length === 0) {
        cartSummary.innerHTML = `<li class="list-group-item">No tienes productos en tu carrito.</li>`;
    } else {
        // Mostrar productos en el resumen
        productosSeleccionados.forEach(producto => {
            console.log(producto); // Verificar cada producto
            if (producto.precio && !isNaN(producto.precio) && producto.precio > 0) {
                total += producto.precio;
                cartSummary.innerHTML += `
                    <li class="list-group-item d-flex justify-content-between">
                        <span>${producto.nombre}</span>
                        <span>$${producto.precio.toFixed(2)}</span>
                    </li>
                `;
            } else {
                console.log(`⚠️ Producto con precio inválido: ${producto.nombre}`, producto);
            }
        });
    }

    // Mostrar el total de la compra
    console.log("Total calculado:", total); // Verifica el valor total
    cartSummary.innerHTML += `
        <li class="list-group-item d-flex justify-content-between fw-bold">
            <span>Total</span>
            <span>$${total.toFixed(2)}</span>
        </li>
    `;

    // Stripe Elements (Pago en el mismo sitio)
    var stripe = Stripe('pk_test_51Qs6i4EDMQKkphbDxcrHaAsXVvayQz3GTpjGL0Ql42dnn61XxWKtQU4zHytX7FpusQSHYMkQMyP3OrFXUPBIueJy00vHDnZ2Hm');
    var elements = stripe.elements();
    var card = elements.create('card');
    card.mount('#card-element');

    card.on('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        var cardholderName = document.getElementById('cardholder-name').value.trim();
        if (!cardholderName) {
            alert("Por favor ingrese el nombre del titular de la tarjeta.");
            return;
        }

        stripe.createToken(card).then(function(result) {
            if (result.error) {
                document.getElementById('card-errors').textContent = result.error.message;
            } else {
                console.log("Token generado:", result.token.id);
                
                // Enviar el token al backend para procesar el pago
                fetch('http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/stripe/checkout.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        stripeToken: result.token.id,
                        amount: total * 100,  // Convert total to cents
                        description: "Compra en tienda online",
                        cardholderName: cardholderName
                    }),
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        //alert("Pago realizado con éxito");
                        window.location.href = "/M12-Proyecto-PHP-Natalia-Beatriz/pagoExitoso.html";
                    } else {
                        alert("Error en el pago: " + data.message);
                    }
                })
                .catch(error => console.error('Error al procesar la compra:', error));
            }
        });
    });

    // Habilitar o deshabilitar el botón de pago según el checkbox
    document.getElementById('terms-checkbox').addEventListener('change', function () {
        document.getElementById('submit-button').disabled = !this.checked;
    });
};
