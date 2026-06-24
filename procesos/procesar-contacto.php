<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/conexion.php';

// ─── Funciones auxiliares ─────────────────────────────────────────────────────

function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function volverConErrores(array $errores, array $datos): void
{
    $_SESSION['errores_contacto'] = $errores;
    $_SESSION['datos_contacto']   = $datos;
    redirigir('../public/contacto.php');
}

// ─── Solo se acepta POST ──────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../public/contacto.php');
}

// ─── Recolectar y normalizar datos ────────────────────────────────────────────

$nombre    = trim($_POST['nombre']    ?? '');
$apellido  = trim($_POST['apellido']  ?? '');
$correo    = strtolower(trim($_POST['correo'] ?? ''));
$telefono  = trim($_POST['telefono']  ?? '');
$motivoRaw = trim($_POST['motivo']    ?? '');
$asunto    = trim($_POST['asunto']    ?? '');
$mensaje   = trim($_POST['mensaje']   ?? '');
$aceptaPrivacidad = $_POST['acepta_privacidad'] ?? '';

// Datos para repoblar el formulario (sin acepta_privacidad)
$datosFormulario = [
    'nombre'   => $nombre,
    'apellido' => $apellido,
    'correo'   => $correo,
    'telefono' => $telefono,
    'motivo'   => $motivoRaw,
    'asunto'   => $asunto,
    'mensaje'  => $mensaje,
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

// Teléfono: opcional; si se proporciona debe tener formato válido
$telefonoGuardar = $telefono !== '' ? $telefono : null;
if ($telefono !== '' && !preg_match('/^[0-9+\s()\-]{10,20}$/', $telefono)) {
    $errores['telefono'] = 'El teléfono debe tener entre 10 y 20 caracteres. Solo acepta números, espacios, guiones, paréntesis y +.';
}

// Motivo: debe ser entero positivo (se verifica contra la BD más adelante)
$idMotivo = filter_var($motivoRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($idMotivo === false || $idMotivo === null) {
    $errores['motivo'] = 'Selecciona un motivo de contacto válido.';
}

if ($asunto === '') {
    $errores['asunto'] = 'El asunto es obligatorio.';
} elseif (mb_strlen($asunto) > 150) {
    $errores['asunto'] = 'El asunto no puede superar los 150 caracteres.';
}

if ($mensaje === '') {
    $errores['mensaje'] = 'El mensaje es obligatorio.';
} elseif (mb_strlen($mensaje) > 1500) {
    $errores['mensaje'] = 'El mensaje no puede superar los 1500 caracteres.';
}

if ($aceptaPrivacidad !== '1') {
    $errores['acepta_privacidad'] = 'Debes aceptar el aviso de privacidad para continuar.';
}

if (!empty($errores)) {
    volverConErrores($errores, $datosFormulario);
}

// ─── Consulta e inserción ─────────────────────────────────────────────────────

try {
    // Verificar que el id_motivo exista en el catálogo
    $stmtMot = mysqli_prepare(
        $conexion,
        'SELECT id_motivo FROM MotivoContacto WHERE id_motivo = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtMot, 'i', $idMotivo);
    mysqli_stmt_execute($stmtMot);
    mysqli_stmt_store_result($stmtMot);

    if (mysqli_stmt_num_rows($stmtMot) === 0) {
        mysqli_stmt_close($stmtMot);
        $errores['motivo'] = 'Selecciona un motivo de contacto válido.';
        volverConErrores($errores, $datosFormulario);
    }
    mysqli_stmt_close($stmtMot);

    // Obtener id_estado_mensaje = 'pendiente' desde el catálogo
    $nombreEstado = 'pendiente';
    $stmtEst = mysqli_prepare(
        $conexion,
        'SELECT id_estado_mensaje FROM EstadoMensajeContacto WHERE nombre_estado = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtEst, 's', $nombreEstado);
    mysqli_stmt_execute($stmtEst);
    $rowEst = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtEst));
    mysqli_stmt_close($stmtEst);

    if (!$rowEst) {
        $_SESSION['error_general'] = 'No fue posible enviar el mensaje. Inténtalo nuevamente.';
        redirigir('../public/contacto.php');
    }

    $idEstadoPendiente = (int) $rowEst['id_estado_mensaje'];

    // Insertar el mensaje
    $stmtIns = mysqli_prepare(
        $conexion,
        'INSERT INTO MensajeContacto
             (id_motivo, id_estado_mensaje, nombre, apellido, correo, telefono, asunto, mensaje)
         VALUES
             (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param(
        $stmtIns,
        'iissssss',
        $idMotivo,
        $idEstadoPendiente,
        $nombre,
        $apellido,
        $correo,
        $telefonoGuardar,
        $asunto,
        $mensaje
    );
    mysqli_stmt_execute($stmtIns);
    mysqli_stmt_close($stmtIns);

    $_SESSION['mensaje_exito_contacto'] = '¡Mensaje enviado correctamente! Nos pondremos en contacto contigo a la brevedad.';
    redirigir('../public/contacto.php');

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible enviar el mensaje. Inténtalo nuevamente.';
    redirigir('../public/contacto.php');
}
