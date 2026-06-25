<?php

declare(strict_types=1);

require_once __DIR__ . '/../comprador/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

// ─── Solo POST ────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../comprador/perfil.php');
    exit;
}

$idUsuario   = (int) $_SESSION['id_usuario'];
$idComprador = (int) $_SESSION['id_perfil'];

// ─── Recolectar datos ─────────────────────────────────────────────────────────

$nombre          = trim($_POST['nombre']           ?? '');
$apellido        = trim($_POST['apellido']         ?? '');
$correo          = strtolower(trim($_POST['correo'] ?? ''));
$telefono        = trim($_POST['telefono']         ?? '');
$presupMinRaw    = trim($_POST['presupuesto_minimo'] ?? '');
$presupMaxRaw    = trim($_POST['presupuesto_maximo'] ?? '');
$zonaInteres     = trim($_POST['zona_interes']     ?? '');

$datosFormulario = [
    'nombre'            => $nombre,
    'apellido'          => $apellido,
    'correo'            => $correo,
    'telefono'          => $telefono,
    'presupuesto_minimo' => $presupMinRaw,
    'presupuesto_maximo' => $presupMaxRaw,
    'zona_interes'      => $zonaInteres,
];

// ─── Validaciones ─────────────────────────────────────────────────────────────

$errores = [];

if ($nombre === '') {
    $errores['nombre'] = 'El nombre es obligatorio.';
} elseif (mb_strlen($nombre) > 100) {
    $errores['nombre'] = 'El nombre no puede superar los 100 caracteres.';
}

if ($apellido === '') {
    $errores['apellido'] = 'El apellido es obligatorio.';
} elseif (mb_strlen($apellido) > 100) {
    $errores['apellido'] = 'El apellido no puede superar los 100 caracteres.';
}

if ($correo === '') {
    $errores['correo'] = 'El correo electrónico es obligatorio.';
} elseif (mb_strlen($correo) > 150) {
    $errores['correo'] = 'El correo no puede superar los 150 caracteres.';
} elseif (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
    $errores['correo'] = 'El correo electrónico no tiene un formato válido.';
}

$telefonoGuardar = $telefono !== '' ? $telefono : null;
if ($telefono !== '' && !preg_match('/^[0-9+\s()\-]{10,20}$/', $telefono)) {
    $errores['telefono'] = 'El teléfono debe tener entre 10 y 20 caracteres.';
}

// Presupuesto: opcional; si se provee debe ser número positivo
$presupMin = null;
$presupMax = null;

if ($presupMinRaw !== '') {
    if (!is_numeric($presupMinRaw) || (float) $presupMinRaw < 0) {
        $errores['presupuesto_minimo'] = 'El presupuesto mínimo debe ser un número positivo.';
    } else {
        $presupMin = (float) $presupMinRaw;
    }
}

if ($presupMaxRaw !== '') {
    if (!is_numeric($presupMaxRaw) || (float) $presupMaxRaw < 0) {
        $errores['presupuesto_maximo'] = 'El presupuesto máximo debe ser un número positivo.';
    } else {
        $presupMax = (float) $presupMaxRaw;
    }
}

if ($presupMin !== null && $presupMax !== null && $presupMin > $presupMax) {
    $errores['presupuesto_maximo'] = 'El presupuesto máximo debe ser mayor que el mínimo.';
}

$zonaGuardar = $zonaInteres !== '' ? $zonaInteres : null;
if ($zonaInteres !== '' && mb_strlen($zonaInteres) > 150) {
    $errores['zona_interes'] = 'La zona de interés no puede superar los 150 caracteres.';
}

if (!empty($errores)) {
    $_SESSION['errores_perfil_comprador'] = $errores;
    $_SESSION['datos_perfil_comprador']   = $datosFormulario;
    header('Location: ../comprador/perfil.php');
    exit;
}

// ─── Verificar correo único (excluyendo al usuario actual) ────────────────────

try {
    $stmtC = mysqli_prepare(
        $conexion,
        'SELECT id_usuario FROM Usuario WHERE correo = ? AND id_usuario != ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtC, 'si', $correo, $idUsuario);
    mysqli_stmt_execute($stmtC);
    mysqli_stmt_store_result($stmtC);

    if (mysqli_stmt_num_rows($stmtC) > 0) {
        mysqli_stmt_close($stmtC);
        $_SESSION['errores_perfil_comprador'] = ['correo' => 'Este correo electrónico ya está registrado por otro usuario.'];
        $_SESSION['datos_perfil_comprador']   = $datosFormulario;
        header('Location: ../comprador/perfil.php');
        exit;
    }
    mysqli_stmt_close($stmtC);

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible actualizar el perfil. Inténtalo nuevamente.';
    header('Location: ../comprador/perfil.php');
    exit;
}

// ─── Transacción: actualizar Usuario y Comprador ──────────────────────────────

try {
    mysqli_begin_transaction($conexion);

    $stmtU = mysqli_prepare(
        $conexion,
        'UPDATE Usuario SET nombre = ?, apellido = ?, correo = ?, telefono = ?
         WHERE id_usuario = ?'
    );
    mysqli_stmt_bind_param($stmtU, 'ssssi', $nombre, $apellido, $correo, $telefonoGuardar, $idUsuario);
    mysqli_stmt_execute($stmtU);
    mysqli_stmt_close($stmtU);

    $stmtCo = mysqli_prepare(
        $conexion,
        'UPDATE Comprador SET presupuesto_minimo = ?, presupuesto_maximo = ?, zona_interes = ?
         WHERE id_comprador = ?'
    );
    mysqli_stmt_bind_param($stmtCo, 'ddsi', $presupMin, $presupMax, $zonaGuardar, $idComprador);
    mysqli_stmt_execute($stmtCo);
    mysqli_stmt_close($stmtCo);

    mysqli_commit($conexion);

    // Actualizar sesión con los nuevos valores
    $_SESSION['nombre'] = $nombre;
    $_SESSION['correo'] = $correo;

    $_SESSION['mensaje_exito'] = 'Perfil actualizado correctamente.';
    header('Location: ../comprador/perfil.php');
    exit;

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexion);
    $_SESSION['error_general'] = 'No fue posible actualizar el perfil. Inténtalo nuevamente.';
    header('Location: ../comprador/perfil.php');
    exit;
}
