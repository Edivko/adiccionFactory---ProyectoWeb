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

    <!-- Usando ../ para que sea compatible con todas las compus -->
    <link rel="stylesheet" href="../public/recursos/css/estilos.css">
</head>
<body>

<header class="header">
    <div class="contenedor header-contenido">

        <!-- Usando ../ para la imagen del logo -->
        <a href="../comprador/index.php" class="logo">
            <img src="../public/recursos/img/logo.png" alt="Adicción Factory Inmobiliaria">
        </a>

        <nav class="nav">
            <!-- Nota: Si estos archivos están en la raíz, también llevan ../ -->
            <a href="../public/index.php">Inicio</a>
            <a href="../public/catalogo.php">Catálogo</a>
            <a href="../public/contacto.php">Contacto</a>
            <a href="../public/login.php" class="btn btn-secundario">Iniciar sesión</a>
        </nav>

    </div>
</header>