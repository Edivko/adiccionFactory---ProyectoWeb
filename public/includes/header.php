<?php
if (!isset($tituloPagina)) {
    $tituloPagina = "Adicción Factory Inmobiliaria";
}

// Iniciar sesión si no está activa, para que el header pueda leer $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$haySession = isset($_SESSION['id_usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?></title>

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
            <?php if ($haySession): ?>
                <span class="nav-usuario">
                    <?php echo htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <a href="../procesos/cerrar-sesion.php" class="btn btn-secundario">Cerrar sesión</a>
            <?php else: ?>
                <a href="../public/index.php">Inicio</a>
                <a href="../public/catalogo.php">Catálogo</a>
                <a href="../public/contacto.php">Contacto</a>
                <a href="../public/login.php" class="btn btn-secundario">Iniciar sesión</a>
            <?php endif; ?>
        </nav>

    </div>
</header>
