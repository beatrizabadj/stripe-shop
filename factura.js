document.getElementById('generar-factura').addEventListener('click', function () {
// Recuperar el ID de la factura desde localStorage
    const invoiceId = localStorage.getItem('invoiceId');

    if (!invoiceId) {
        alert("No se encontró ninguna factura. Por favor, realice un pago primero.");
        window.location.href = "/M12-Proyecto-PHP-Natalia-Beatriz/pagoTarjeta.php"; // Redirigir a la página de pagos
    } else {
        console.log("ID de la factura:", invoiceId);

        // Obtener la factura desde el servidor
        fetch('http://localhost/M12-Proyecto-PHP-Natalia-Beatriz/stripe/invoice.php', {
            method: 'POST',
            body: JSON.stringify({ invoiceId: invoiceId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Mostrar la factura en una nueva ventana
                const ventanaFactura = window.open('', '_blank');
                ventanaFactura.document.write(data.facturaHTML);
                ventanaFactura.document.close();

                // Imprimir la factura
                ventanaFactura.print();

                // Limpiar el invoiceId de localStorage
                localStorage.removeItem('invoiceId');
            } else {
                alert("Error al obtener la factura: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error al obtener la factura:', error);
            alert("Hubo un error al obtener la factura. Por favor, intenta nuevamente.");
        });
    }
})