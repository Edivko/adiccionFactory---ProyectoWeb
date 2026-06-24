<?php

declare(strict_types=1);

session_start();

// Vaciar el arreglo de sesión
$_SESSION = [];

// Eliminar la cookie de sesión si el navegador la envió
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header('Location: ../public/login.php');
exit;
