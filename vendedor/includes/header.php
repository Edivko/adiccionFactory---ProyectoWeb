<?php
require_once __DIR__ . '/autenticacion.php';

if (!isset($tituloPagina)) {
    $tituloPagina = 'Panel del vendedor | Adicción Factory Inmobiliaria';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../public/recursos/css/estilos.css">
</head>
<body>

<header class="header">
    <div class="contenedor header-contenido">

        <a href="index.php" class="logo">
            <img src="../public/recursos/img/logo.png" alt="Adicción Factory Inmobiliaria">
        </a>

        <nav class="nav">
            <span class="nav-usuario">
                <?php echo htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <a href="../procesos/cerrar-sesion.php" class="btn btn-secundario">
                Cerrar sesión
            </a>
        </nav>

    </div>
</header>
