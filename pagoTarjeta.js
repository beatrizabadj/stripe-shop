window.onload = () => {
    // Simulación de productos seleccionados (esto normalmente vendría de localStorage)
    const productosSeleccionados = JSON.parse(localStorage.getItem('carrito'));
    console.log(productosSeleccionados);            

    const cartSummary = document.getElementById("cart-summary");
    let total = 0;

    // Si no hay productos seleccionados, muestra un mensaje
    if (productosSeleccionados.length === 0) {
        cartSummary.innerHTML = `<li class="list-group-item">No tienes productos en tu carrito.</li>`;
    }else{
        // Mostrar productos en el resumen
        productosSeleccionados.forEach(producto => {
            if (producto.precio && !isNaN(producto.precio)) {
                total += producto.precio;
                cartSummary.innerHTML += `
                    <li class="list-group-item d-flex justify-content-between">
                        <span>${producto.nombre}</span>
                        <span>$${producto.precio.toFixed(2)}</span>
                    </li>
                `;
            } else {
                // Si el precio no es válido o es nulo, lo ignoramos
                console.log(`Producto con precio inválido: ${producto.nombre}`);
            }
        });
    }
    
    // Mostrar el total de la compra
    cartSummary.innerHTML += `
        <li class="list-group-item d-flex justify-content-between fw-bold">
            <span>Total</span>
            <span>$${total.toFixed(2)}</span>
        </li>
    `;


    // Stripe setup
    var stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
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
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', result.token.id);
                form.appendChild(hiddenInput);
                form.submit();
            }
        });
    });
};
