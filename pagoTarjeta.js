window.onload = () => {
    const botonVolver = document.getElementById("volver");
    if (botonVolver) {
        botonVolver.addEventListener("click", () => {
            localStorage.removeItem('carrito');
            console.log("Carrito vaciado.");
        });
    }

    // Obtener los productos seleccionados desde localStorage
    const productosSeleccionados = JSON.parse(localStorage.getItem('carrito')) || [];
    productosSeleccionados.forEach(producto => {
        if (!producto.cantidad) {
            producto.cantidad = 1; // Asignar un valor predeterminado si no existe
        }
    });
    console.log(productosSeleccionados); // Verifica los productos

    const cartSummary = document.getElementById("cart-summary");
    let totalConIVA;
    // Función para calcular el subtotal y total
    const calcularTotales = () => {
        // Calcula el subtotal (sin IVA)
        const subtotal = productosSeleccionados.reduce((acc, producto) => {
            const precio = parseFloat(producto.precio);
            const cantidad = parseInt(producto.cantidad, 10)
            if (isNaN(precio)) {
                console.error(`Producto con precio inválido:`, producto);
                return acc; // Si el precio no es válido, no lo sumamos
            }
    
            console.log(`Producto: ${producto.nombre}, Cantidad: ${cantidad}, Precio: ${precio}`);
            return acc + (precio * cantidad);
        }, 0);


        // Calcula el total con IVA (21%)
        const IVA = 0.21;
        totalConIVA = subtotal * (1 + IVA); // Guardamos el total con IVA globalmente

        console.log(`Subtotal sin IVA: $${subtotal.toFixed(2)}, Total con IVA: $${totalConIVA.toFixed(2)}`);

        // Actualizar el contenido del resumen
        cartSummary.innerHTML = ''; // Limpiar el contenido anterior

        // Si no hay productos seleccionados, muestra un mensaje
        if (productosSeleccionados.length === 0) {
            cartSummary.innerHTML = `<li class="list-group-item">No tienes productos en tu carrito.</li>`;
        } else {
            // Mostrar productos en el resumen
            productosSeleccionados.forEach((producto, index) => {
                cartSummary.innerHTML += `
                    <li class="list-group-item d-flex justify-content-between">
                        <span>${producto.nombre}</span>
                        <span>$${producto.precio.toFixed(2)}</span>
                        <span>
                            <input type="number" value="${producto.cantidad}" min="1" id="cantidad-${index}" onchange="actualizarCantidad(${index})" />
                            <button onclick="eliminarProducto(${index})">Eliminar</button>
                        </span>
                    </li>
                `;
            });

            // Mostrar el subtotal y el total con IVA
            cartSummary.innerHTML += `
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    <span>Subtotal (sin IVA)</span>
                    <span>$${subtotal.toFixed(2)}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    <span>Total con IVA (21%)</span>
                    <span>$${totalConIVA.toFixed(2)}</span>
                </li>
            `;
        }
    }

    // Llamar a calcularTotales al cargar la página
    calcularTotales();

    // Función para actualizar la cantidad de un producto
    window.actualizarCantidad = (index) => {
        const cantidad = parseInt(document.getElementById(`cantidad-${index}`).value, 10);
        if (cantidad > 0) {
            productosSeleccionados[index].cantidad = cantidad;
            localStorage.setItem('carrito', JSON.stringify(productosSeleccionados));
            calcularTotales(); // Actualizar el resumen sin recargar la página
        }
    }

    // Función para eliminar un producto
    window.eliminarProducto = (index) => {
        productosSeleccionados.splice(index, 1); // Eliminar producto del carrito
        localStorage.setItem('carrito', JSON.stringify(productosSeleccionados));
        calcularTotales(); // Actualizar el resumen sin recargar la página
    }

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
        const submitButton = document.getElementById('submit-button');
        submitButton.disabled = true;
        var cardholderName = document.getElementById('cardholder-name').value.trim();
        if (!cardholderName) {
            alert("Por favor ingrese el nombre del titular de la tarjeta.");
            submitButton.disabled = false; // Rehabilitar el botón si hay un error
            return;
        }

        stripe.createToken(card).then(function(result) {
            if (result.error) {
                document.getElementById('card-errors').textContent = result.error.message;
                submitButton.disabled = false; // Rehabilitar el botón si hay un error

            } else {
                // Enviar el token al backend para procesar el pago
                fetch('http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/stripe/checkout.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        stripeToken: result.token.id,
                        amount: Math.round(totalConIVA * 100), // Enviar el total con IVA multiplicado por 100 (Stripe espera el monto en centavos)
                        description: "Compra en tienda online",
                        cardholderName: cardholderName
                    }),
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => {
                    console.log("Respuesta del servidor:", response); // Imprime la respuesta completa
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor: ' + response.statusText);
                    }
                    return response.text(); // Primero obtener el texto de la respuesta
                })
                .then(text => {
                    console.log("Texto de la respuesta:", text); // Imprime el texto de la respuesta
                    return JSON.parse(text); // Intentar parsear el texto como JSON
                })
                .then(data => {
                    if (data.status === 'success') {
                        window.invoiceId = data.invoiceId;
                        alert("Pago exitoso. ID de la factura: " + data.invoiceId);
                        localStorage.setItem('invoiceId', data.invoiceId);

                        window.location.href = "/M12-Proyecto-PHP-Natalia-Beatriz/pagoExitoso.html";
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error al procesar la compra:', error);
                    alert("Hubo un error al procesar tu pago. Por favor, intenta nuevamente.");
                });
            }
        })
    });
    // Habilitar o deshabilitar el botón de pago según el checkbox
    document.getElementById('terms-checkbox').addEventListener('change', function () {
        document.getElementById('submit-button').disabled = !this.checked;
    });
};
