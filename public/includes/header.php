<?php
if (!isset($tituloPagina)) {
    $tituloPagina = "Adicción Factory Inmobiliaria";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloPagina; ?></title>

    <link rel="stylesheet" href="recursos/css/estilos.css">
</head>
<body>

<header class="header">
    <div class="contenedor header-contenido">

        <a href="index.php" class="logo">
            <img src="recursos/img/logo.png" alt="Adicción Factory Inmobiliaria">
        </a>

        <nav class="nav">
            <a href="index.php">Inicio</a>
            <a href="catalogo.php">Catálogo</a>
            <a href="contacto.php">Contacto</a>
            <a href="login.php" class="btn btn-secundario">Iniciar sesión</a>
        </nav>

    </div>
</header>