<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/conexion.php";

$resultado = mysqli_query($conexion, "SELECT DATABASE() AS base_actual");
$fila = mysqli_fetch_assoc($resultado);

echo "Conexión correcta.<br>";
echo "Base actual: " . htmlspecialchars(
    $fila["base_actual"],
    ENT_QUOTES,
    "UTF-8"
);