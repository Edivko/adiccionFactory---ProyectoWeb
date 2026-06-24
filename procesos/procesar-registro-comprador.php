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

function esTextoValido(string $texto, int $maximo): bool
{
    return mb_strlen($texto) <= $maximo;
}

function validarTelefono(string $telefono): bool
{
    return (bool) preg_match('/^[0-9+\s()\-]{10,20}$/', $telefono);
}

function normalizarOpcional(string $valor): ?string
{
    $limpio = trim($valor);
    return $limpio !== '' ? $limpio : null;
}

function volverConErrores(array $errores, array $datos): void
{
    $_SESSION['errores_registro_comprador'] = $errores;
    $_SESSION['datos_registro_comprador']   = $datos;
    redirigir('../public/registro-comprador.php');
}

// ─── Solo se acepta POST ──────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../public/registro-comprador.php');
}

// ─── Constantes de catálogo (confirmadas en 02_datos_iniciales.sql) ───────

const ID_ROL_COMPRADOR = 1;
const ID_ESTADO_ACTIVA = 2;

// ─── Recolectar y normalizar datos ────────────────────────────────────────

$nombre            = trim($_POST['nombre']            ?? '');
$apellido          = trim($_POST['apellido']          ?? '');
$correo            = strtolower(trim($_POST['correo'] ?? ''));
$telefono          = trim($_POST['telefono']          ?? '');
$password          = $_POST['password']               ?? '';
$confirmarPassword = $_POST['confirmar_password']     ?? '';
$presupuestoMinRaw = normalizarOpcional($_POST['presupuesto_minimo'] ?? '');
$presupuestoMaxRaw = normalizarOpcional($_POST['presupuesto_maximo'] ?? '');
$zonaInteres       = normalizarOpcional($_POST['zona_interes']       ?? '');
$aceptaTerminos    = $_POST['acepta_terminos'] ?? '';

// Datos para repoblar el formulario si hay errores (sin contraseñas)
$datosFormulario = [
    'nombre'             => $nombre,
    'apellido'           => $apellido,
    'correo'             => $correo,
    'telefono'           => $telefono,
    'presupuesto_minimo' => $presupuestoMinRaw ?? '',
    'presupuesto_maximo' => $presupuestoMaxRaw ?? '',
    'zona_interes'       => $zonaInteres       ?? '',
];

// ─── Validaciones ─────────────────────────────────────────────────────────

$errores = [];

if ($nombre === '') {
    $errores['nombre'] = 'El nombre es obligatorio.';
} elseif (!esTextoValido($nombre, 100)) {
    $errores['nombre'] = 'El nombre no puede superar los 100 caracteres.';
}

if ($apellido === '') {
    $errores['apellido'] = 'El apellido es obligatorio.';
} elseif (!esTextoValido($apellido, 100)) {
    $errores['apellido'] = 'El apellido no puede superar los 100 caracteres.';
}

if ($correo === '') {
    $errores['correo'] = 'El correo electrónico es obligatorio.';
} elseif (!esTextoValido($correo, 150)) {
    $errores['correo'] = 'El correo no puede superar los 150 caracteres.';
} elseif (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
    $errores['correo'] = 'El correo electrónico no tiene un formato válido.';
}

if ($telefono === '') {
    $errores['telefono'] = 'El teléfono es obligatorio.';
} elseif (!validarTelefono($telefono)) {
    $errores['telefono'] = 'El teléfono debe tener entre 10 y 20 caracteres. Solo acepta números, espacios, guiones, paréntesis y +.';
}

if ($password === '') {
    $errores['password'] = 'La contraseña es obligatoria.';
} elseif (mb_strlen($password) < 8) {
    $errores['password'] = 'La contraseña debe tener al menos 8 caracteres.';
} elseif (mb_strlen($password) > 72) {
    $errores['password'] = 'La contraseña no puede superar los 72 caracteres.';
} elseif ($password !== $confirmarPassword) {
    $errores['confirmar_password'] = 'Las contraseñas no coinciden.';
}

$presupuestoMin = null;
$presupuestoMax = null;

if ($presupuestoMinRaw !== null) {
    if (!is_numeric($presupuestoMinRaw) || (float) $presupuestoMinRaw < 0) {
        $errores['presupuesto_minimo'] = 'El presupuesto mínimo debe ser un número mayor o igual a cero.';
    } else {
        $presupuestoMin = (float) $presupuestoMinRaw;
    }
}

if ($presupuestoMaxRaw !== null) {
    if (!is_numeric($presupuestoMaxRaw) || (float) $presupuestoMaxRaw < 0) {
        $errores['presupuesto_maximo'] = 'El presupuesto máximo debe ser un número mayor o igual a cero.';
    } else {
        $presupuestoMax = (float) $presupuestoMaxRaw;
    }
}

if ($presupuestoMin !== null && $presupuestoMax !== null && $presupuestoMax < $presupuestoMin) {
    $errores['presupuesto_maximo'] = 'El presupuesto máximo no puede ser menor al mínimo.';
}

if ($zonaInteres !== null && !esTextoValido($zonaInteres, 150)) {
    $errores['zona_interes'] = 'La zona de interés no puede superar los 150 caracteres.';
}

if ($aceptaTerminos !== '1') {
    $errores['acepta_terminos'] = 'Debes aceptar los términos y condiciones para continuar.';
}

if (!empty($errores)) {
    volverConErrores($errores, $datosFormulario);
}

// ─── Verificar correo duplicado ───────────────────────────────────────────

try {
    $stmtCorreo = mysqli_prepare(
        $conexion,
        'SELECT id_usuario FROM Usuario WHERE correo = ? LIMIT 1'
    );

    mysqli_stmt_bind_param($stmtCorreo, 's', $correo);
    mysqli_stmt_execute($stmtCorreo);
    mysqli_stmt_store_result($stmtCorreo);

    if (mysqli_stmt_num_rows($stmtCorreo) > 0) {
        mysqli_stmt_close($stmtCorreo);
        $errores['correo'] = 'Este correo electrónico ya está registrado.';
        volverConErrores($errores, $datosFormulario);
    }

    mysqli_stmt_close($stmtCorreo);

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible completar el registro. Inténtalo nuevamente.';
    redirigir('../public/registro-comprador.php');
}

// ─── Cifrar contraseña ────────────────────────────────────────────────────

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// ─── Transacción: insertar en Usuario y Comprador ─────────────────────────

try {
    mysqli_begin_transaction($conexion);

    // 1. Insertar en Usuario
    $stmtUsuario = mysqli_prepare(
        $conexion,
        'INSERT INTO Usuario (id_rol, id_estado_cuenta, nombre, apellido, correo, telefono, password_hash)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $idRol    = ID_ROL_COMPRADOR;
    $idEstado = ID_ESTADO_ACTIVA;

    mysqli_stmt_bind_param(
        $stmtUsuario,
        'iisssss',
        $idRol,
        $idEstado,
        $nombre,
        $apellido,
        $correo,
        $telefono,
        $passwordHash
    );

    mysqli_stmt_execute($stmtUsuario);
    $idUsuario = (int) mysqli_insert_id($conexion);
    mysqli_stmt_close($stmtUsuario);

    // 2. Insertar en Comprador
    $stmtComprador = mysqli_prepare(
        $conexion,
        'INSERT INTO Comprador (id_usuario, presupuesto_minimo, presupuesto_maximo, zona_interes)
         VALUES (?, ?, ?, ?)'
    );

    mysqli_stmt_bind_param(
        $stmtComprador,
        'idds',
        $idUsuario,
        $presupuestoMin,
        $presupuestoMax,
        $zonaInteres
    );

    mysqli_stmt_execute($stmtComprador);
    mysqli_stmt_close($stmtComprador);

    mysqli_commit($conexion);

    $_SESSION['mensaje_exito'] = '¡Cuenta creada exitosamente! Ahora puedes iniciar sesión.';
    redirigir('../public/login.php');

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexion);
    $_SESSION['error_general'] = 'No fue posible completar el registro. Inténtalo nuevamente.';
    redirigir('../public/registro-comprador.php');
}
