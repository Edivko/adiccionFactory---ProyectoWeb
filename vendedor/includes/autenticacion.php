<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/conexion.php';

define('ID_ROL_VENDEDOR',     2);
define('ID_ESTADO_ACTIVA_EC', 2);

// ─── Verificar variables de sesión mínimas ────────────────────────────────────

if (
    !isset($_SESSION['id_usuario'], $_SESSION['id_rol'], $_SESSION['id_perfil'])
    || (int) $_SESSION['id_rol'] !== ID_ROL_VENDEDOR
) {
    header('Location: ../public/login.php');
    exit;
}

// ─── Consultar BD: cuenta activa en tiempo real ───────────────────────────────

try {
    $__stmtAuth = mysqli_prepare(
        $conexion,
        'SELECT id_estado_cuenta FROM Usuario WHERE id_usuario = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($__stmtAuth, 'i', $_SESSION['id_usuario']);
    mysqli_stmt_execute($__stmtAuth);
    mysqli_stmt_bind_result($__stmtAuth, $__estadoCuenta);
    mysqli_stmt_fetch($__stmtAuth);
    mysqli_stmt_close($__stmtAuth);

    if ((int) $__estadoCuenta !== ID_ESTADO_ACTIVA_EC) {
        session_unset();
        session_destroy();
        session_start();
        header('Location: ../public/login.php');
        exit;
    }
    unset($__stmtAuth, $__estadoCuenta);

} catch (mysqli_sql_exception) {
    header('Location: ../public/login.php');
    exit;
}
