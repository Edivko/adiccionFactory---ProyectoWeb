<?php

declare(strict_types=1);

// id_rol = 1 confirmado en procesos/procesar-login.php (mapeoTablas[1])
define('ID_ROL_COMPRADOR', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sin sesión válida → login
if (
    !isset($_SESSION['id_usuario']) ||
    !isset($_SESSION['id_rol'])     ||
    !isset($_SESSION['id_perfil'])
) {
    header('Location: ../public/login.php');
    exit;
}

// Rol incorrecto → login
if ((int) $_SESSION['id_rol'] !== ID_ROL_COMPRADOR) {
    header('Location: ../public/login.php');
    exit;
}
