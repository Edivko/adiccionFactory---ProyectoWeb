<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servidor = "localhost";
$usuarioBD = "admin";       // <-- Aquí pusimos el usuario que creaste
$contrasenaBD = "1234";     // <-- Aquí va la contraseña que le asignaste
$nombreBD = "adiccion_factory";
$puerto = 3306;

try {
    $conexion = mysqli_connect(
        $servidor,
        $usuarioBD,
        $contrasenaBD,
        $nombreBD,
        $puerto
    );
    mysqli_set_charset($conexion, "utf8mb4");
} catch (mysqli_sql_exception $error) {
    exit("No fue posible conectar con la base de datos.");
}