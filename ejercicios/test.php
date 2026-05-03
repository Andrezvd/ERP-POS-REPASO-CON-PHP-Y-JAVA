<?php 

$MiNombre = "Andres Olivar";
echo "Hola, mi nombre es " . $MiNombre . "\n";

$MiMap = array(
    "Nombre" => "Laptop",
    "Precio" => 30,
    "Stock" => "Santiago"
);

foreach ($MiMap as $Key => $Value) {
    echo $Key . ": " . $Value . "\n";
}


function calcularIva($precio) {
    $iva = 0.19;
    return $precio * $iva;
}

echo $MiMap["Nombre"] . "tiene un precio de" . $MiMap["Precio"] . "\n";
echo $MiMap["Precio"] . " + IVA: " . calcularIva($MiMap["Precio"]);
echo "para un total de" . ($MiMap["Precio"] + calcularIva($MiMap["Precio"])) . "\n";


?>