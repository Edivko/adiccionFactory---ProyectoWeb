<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/conexion.php';

// ─── Funciones auxiliares ─────────────────────────────────────────────────

function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function volverConError(string $mensaje, string $correo): void
{
    $_SESSION['error_login']  = $mensaje;
    $_SESSION['correo_login'] = $correo;
    redirigir('../public/login.php');
}

function obtenerIdPerfil(mysqli $conexion, int $idUsuario, int $idRol): ?int
{
    // Mapeo cerrado: solo valores controlados por el sistema, no por el usuario
    $mapeoTablas = [
        1 => ['Comprador',     'id_comprador'],
        2 => ['Vendedor',      'id_vendedor'],
        3 => ['Administrador', 'id_administrador'],
    ];

    if (!isset($mapeoTablas[$idRol])) {
        return null;
    }

    [$tabla, $columna] = $mapeoTablas[$idRol];

    $stmt = mysqli_prepare(
        $conexion,
        "SELECT {$columna} FROM {$tabla} WHERE id_usuario = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $idPerfil);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return $idPerfil;
}

// ─── Solo se acepta POST ──────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../public/login.php');
}

// ─── Constantes ───────────────────────────────────────────────────────────

const ID_ESTADO_ACTIVA = 2;

const RUTAS_POR_ROL = [
    1 => '../comprador/index.php',
    2 => '../vendedor/index.php',
    3 => '../admin/dashboard.php',  // admin/index.php no existe; se usa dashboard.php
];

// ─── Recolectar datos ─────────────────────────────────────────────────────

$correo   = strtolower(trim($_POST['correo']   ?? ''));
$password = $_POST['password'] ?? '';   // no aplicar trim a la contraseña

// ─── Validación básica ────────────────────────────────────────────────────

if ($correo === '' || $password === '') {
    volverConError('Correo o contraseña incorrectos.', $correo);
}

if (strlen($correo) > 150 || filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
    volverConError('Correo o contraseña incorrectos.', $correo);
}

// ─── Buscar usuario por correo ────────────────────────────────────────────

try {
    $stmt = mysqli_prepare(
        $conexion,
        'SELECT u.id_usuario, u.id_rol, u.id_estado_cuenta,
                u.nombre, u.apellido, u.correo, u.password_hash,
                r.nombre_rol
         FROM Usuario u
         JOIN RolUsuario r ON r.id_rol = u.id_rol
         WHERE u.correo = ?
         LIMIT 1'
    );

    mysqli_stmt_bind_param($stmt, 's', $correo);
    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);
    $usuario   = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

} catch (mysqli_sql_exception $e) {
    volverConError('No fue posible iniciar sesión. Inténtalo nuevamente.', $correo);
}

// ─── Verificar credenciales ───────────────────────────────────────────────

// Mensaje genérico para no revelar si el correo existe
if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
    volverConError('Correo o contraseña incorrectos.', $correo);
}

// ─── Verificar estado de cuenta ───────────────────────────────────────────

if ((int) $usuario['id_estado_cuenta'] !== ID_ESTADO_ACTIVA) {
    volverConError('Tu cuenta no está disponible para iniciar sesión.', $correo);
}

// ─── Obtener id_perfil según rol ──────────────────────────────────────────

$idUsuario = (int) $usuario['id_usuario'];
$idRol     = (int) $usuario['id_rol'];

try {
    $idPerfil = obtenerIdPerfil($conexion, $idUsuario, $idRol);
} catch (mysqli_sql_exception $e) {
    volverConError('No fue posible iniciar sesión. Inténtalo nuevamente.', $correo);
}

// ─── Regenerar ID de sesión antes de escribir datos sensibles ─────────────

session_regenerate_id(true);

// ─── Crear variables de sesión ────────────────────────────────────────────

$_SESSION['id_usuario'] = $idUsuario;
$_SESSION['nombre']     = $usuario['nombre'];
$_SESSION['correo']     = $usuario['correo'];
$_SESSION['id_rol']     = $idRol;
$_SESSION['nombre_rol'] = $usuario['nombre_rol'];
$_SESSION['id_perfil']  = $idPerfil;

// ─── Redirigir según rol ──────────────────────────────────────────────────

if (!isset(RUTAS_POR_ROL[$idRol])) {
    // Rol desconocido: destruir sesión y mostrar error
    $_SESSION = [];
    session_destroy();
    session_start();
    $_SESSION['error_login'] = 'No fue posible iniciar sesión. Inténtalo nuevamente.';
    redirigir('../public/login.php');
}

redirigir(RUTAS_POR_ROL[$idRol]);
