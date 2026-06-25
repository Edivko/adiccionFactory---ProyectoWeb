<?php

declare(strict_types=1);

$host = 'localhost';
$usuario = 'TU_USUARIO_LOCAL';
$contrasena = 'TU_CONTRASENA_LOCAL';
$baseDatos = 'adiccion_factory';
$puerto = 3306;

$conexion = new mysqli(
    $host,
    $usuario,
    $contrasena,
    $baseDatos,
    $puerto
);

if ($conexion->connect_error) {
    throw new RuntimeException(
        'No fue posible conectar con la base de datos.'
    );
}

$conexion->set_charset('utf8mb4');
