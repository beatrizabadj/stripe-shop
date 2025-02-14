<?php
require './db/db.php';

$result = $conn->query("SELECT * FROM products");

echo "<h1>Productos Disponibles</h1>";
while ($row = $result->fetch_assoc()) {
    echo "<div>
        <h2>{$row['name']}</h2>
        <p>{$row['description']}</p>
        <p>Precio: $" . ($row['price'] / 100) . "</p>
        <form action='../checkout.php' method='POST'>
            <input type='hidden' name='product_id' value='{$row['id']}'>
            <input type='hidden' name='stripe_price_id' value='{$row['stripe_price_id']}'>
            <button type='submit'>Comprar</button>
        </form>
    </div>";
}
?>
