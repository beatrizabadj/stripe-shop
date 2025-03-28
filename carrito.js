document.addEventListener("DOMContentLoaded", () => {
    const botonesAgregar = document.querySelectorAll(".agregar-carrito");

    botonesAgregar.forEach(boton => {
        boton.addEventListener("click", function (event) {
            event.preventDefault();

            // Obtener información del producto
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;
            const precio = parseFloat(this.dataset.precio); // El precio en centavos (sin convertir)
            const imagen = this.dataset.imagen;  
            const descripcion = this.dataset.descripcion; 

            // Verificar si el precio es un número válido
            if (isNaN(precio)) {
                console.error("El precio no es un número válido:", this.dataset.precio);
                return; // Evita agregar el producto si el precio es inválido
            }

            // Obtener carrito actual
            let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

            // Verificar si el producto ya está en el carrito
            let productoExistente = carrito.find(p => p.id === id);

            if (productoExistente) {
                alert("Este producto ya está en el carrito.");
            } else {
                // Agregar nuevo producto con más detalles
                carrito.push({ id, nombre, precio, imagen, descripcion });
                console.log(carrito); // Verifica los productos antes de guardarlos
                localStorage.setItem("carrito", JSON.stringify(carrito));
                alert("Producto agregado al carrito.");
            }
        });
    });
});
