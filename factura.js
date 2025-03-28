document.getElementById('generar-factura').addEventListener('click', function () {
    // Recuperar el ID de la factura desde localStorage
    const invoiceId = localStorage.getItem('invoiceId');

    if (!invoiceId) {
        alert("No se encontró ninguna factura. Por favor, realice un pago primero.");
        window.location.href = "/M12-Proyecto-PHP-Natalia-Beatriz/pagoTarjeta.php"; // Redirigir a la página de pagos
        return; 
    }

    console.log("ID de la factura:", invoiceId);

    // Obtener la factura desde el servidor
    fetch('http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/stripe/invoice.php', {
        method: 'POST',
        body: JSON.stringify({ invoiceId: invoiceId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())  // Convertir la respuesta directamente a JSON
    .then(data => {
        console.log("Factura recibida:", data);

        if (data.status === 'success') {
            const ventanaFactura = window.open('', '_blank');
            ventanaFactura.document.write(data.facturaHTML);
            ventanaFactura.document.close();

            ventanaFactura.print();

            localStorage.removeItem('invoiceId');
        } else {
            alert("Error al obtener la factura: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error al obtener la factura:', error);
        alert("Hubo un error al obtener la factura. Por favor, intenta nuevamente.");
    });
});
