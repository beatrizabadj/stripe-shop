window.onload = () => {
    const botonesAgregar = document.querySelectorAll(".btn-success");

    botonesAgregar.forEach(boton => {
        boton.addEventListener("click", function (event) {
            event.preventDefault();
            
            const card = boton.closest(".card");
            const nombre = card.querySelector(".card-title").innerText;
            const precio = card.querySelector(".card-text").innerText.replace("$ ", "").replace(",", "");
            
            let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
            carrito.push({ nombre, precio: parseFloat(precio) });
            localStorage.setItem("carrito", JSON.stringify(carrito));

            alert("Producto agregado al carrito");

            
        });
    });

    
};