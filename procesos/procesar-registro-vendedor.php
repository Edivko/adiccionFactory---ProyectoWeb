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
    $_SESSION['errores_registro_vendedor'] = $errores;
    $_SESSION['datos_registro_vendedor']   = $datos;
    redirigir('../public/registro-vendedor.php');
}

// ─── Solo se acepta POST ──────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../public/registro-vendedor.php');
}

// ─── Constantes de catálogo (confirmadas en 02_datos_iniciales.sql) ───────
// id_rol: 2 = vendedor | id_estado_cuenta: 1 = pendiente (requiere aprobación)

const ID_ROL_VENDEDOR    = 2;
const ID_ESTADO_PENDIENTE = 1;

// ─── Recolectar y normalizar datos ────────────────────────────────────────

$nombre            = trim($_POST['nombre']            ?? '');
$apellido          = trim($_POST['apellido']          ?? '');
$correo            = strtolower(trim($_POST['correo'] ?? ''));
$telefono          = trim($_POST['telefono']          ?? '');
$password          = $_POST['password']               ?? '';
$confirmarPassword = $_POST['confirmar_password']     ?? '';
$experienciaRaw    = trim($_POST['experiencia']       ?? '');
$zonaTrabajo       = trim($_POST['zona_trabajo']      ?? '');
$descripcion       = trim($_POST['descripcion']       ?? '');
$aceptaTerminos    = $_POST['acepta_terminos'] ?? '';

// Datos para repoblar el formulario si hay errores (sin contraseñas ni archivo)
$datosFormulario = [
    'nombre'       => $nombre,
    'apellido'     => $apellido,
    'correo'       => $correo,
    'telefono'     => $telefono,
    'experiencia'  => $experienciaRaw,
    'zona_trabajo' => $zonaTrabajo,
    'descripcion'  => $descripcion,
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

$experiencia = null;

if ($experienciaRaw === '') {
    $errores['experiencia'] = 'Los años de experiencia son obligatorios.';
} elseif (!ctype_digit($experienciaRaw)) {
    $errores['experiencia'] = 'Los años de experiencia deben ser un número entero.';
} else {
    $experiencia = (int) $experienciaRaw;
    if ($experiencia < 0 || $experiencia > 60) {
        $errores['experiencia'] = 'Los años de experiencia deben estar entre 0 y 60.';
    }
}

if ($zonaTrabajo === '') {
    $errores['zona_trabajo'] = 'La zona de trabajo es obligatoria.';
} elseif (!esTextoValido($zonaTrabajo, 150)) {
    $errores['zona_trabajo'] = 'La zona de trabajo no puede superar los 150 caracteres.';
}

if ($descripcion === '') {
    $errores['descripcion'] = 'La descripción profesional es obligatoria.';
} elseif (!esTextoValido($descripcion, 1000)) {
    $errores['descripcion'] = 'La descripción no puede superar los 1000 caracteres.';
}

if ($aceptaTerminos !== '1') {
    $errores['acepta_terminos'] = 'Debes aceptar los términos y condiciones para continuar.';
}

if (!empty($errores)) {
    volverConErrores($errores, $datosFormulario);
}

// ─── Procesar fotografía (opcional) ──────────────────────────────────────

$fotoRuta       = null;
$fotoMovida     = false;
$fotoRutaFisica = '';

$archivoSubido = isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE;

if ($archivoSubido) {
    $archivo = $_FILES['foto_perfil'];

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores['foto_perfil'] = 'Ocurrió un error al subir la fotografía. Inténtalo nuevamente.';
        volverConErrores($errores, $datosFormulario);
    }

    // Tamaño máximo: 5 MB
    if ($archivo['size'] > 5 * 1024 * 1024) {
        $errores['foto_perfil'] = 'La fotografía no puede superar los 5 MB.';
        volverConErrores($errores, $datosFormulario);
    }

    // Validar MIME real con finfo (procedural)
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mimeReal, $mimesPermitidos, true)) {
        $errores['foto_perfil'] = 'Solo se permiten fotografías en formato JPG, PNG o WEBP.';
        volverConErrores($errores, $datosFormulario);
    }

    // Determinar extensión a partir del MIME real
    $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $extension   = $extensiones[$mimeReal];

    // Nombre único sin relación con el nombre original
    $nombreArchivo  = uniqid('vnd_', true) . '.' . $extension;
    $carpetaFisica  = __DIR__ . '/../public/recursos/uploads/vendedores/';
    $fotoRutaFisica = $carpetaFisica . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $fotoRutaFisica)) {
        $_SESSION['error_general'] = 'No fue posible guardar la fotografía. Inténtalo nuevamente.';
        redirigir('../public/registro-vendedor.php');
    }

    $fotoMovida = true;
    $fotoRuta   = 'recursos/uploads/vendedores/' . $nombreArchivo;
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

        if ($fotoMovida) {
            unlink($fotoRutaFisica);
        }

        $errores['correo'] = 'Este correo electrónico ya está registrado.';
        volverConErrores($errores, $datosFormulario);
    }

    mysqli_stmt_close($stmtCorreo);

} catch (mysqli_sql_exception $e) {
    if ($fotoMovida) {
        unlink($fotoRutaFisica);
    }
    $_SESSION['error_general'] = 'No fue posible completar el registro. Inténtalo nuevamente.';
    redirigir('../public/registro-vendedor.php');
}

// ─── Cifrar contraseña ────────────────────────────────────────────────────

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// ─── Transacción: insertar en Usuario y Vendedor ──────────────────────────

try {
    mysqli_begin_transaction($conexion);

    // 1. Insertar en Usuario
    $stmtUsuario = mysqli_prepare(
        $conexion,
        'INSERT INTO Usuario (id_rol, id_estado_cuenta, nombre, apellido, correo, telefono, password_hash)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $idRol    = ID_ROL_VENDEDOR;
    $idEstado = ID_ESTADO_PENDIENTE;

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

    // 2. Insertar en Vendedor
    $stmtVendedor = mysqli_prepare(
        $conexion,
        'INSERT INTO Vendedor (id_usuario, descripcion, experiencia, foto_perfil, zona_trabajo)
         VALUES (?, ?, ?, ?, ?)'
    );

    mysqli_stmt_bind_param(
        $stmtVendedor,
        'isiss',
        $idUsuario,
        $descripcion,
        $experiencia,
        $fotoRuta,
        $zonaTrabajo
    );

    mysqli_stmt_execute($stmtVendedor);
    mysqli_stmt_close($stmtVendedor);

    mysqli_commit($conexion);

    $_SESSION['mensaje_exito'] = 'Tu cuenta ha sido registrada correctamente. Un administrador la revisará y activará próximamente.';
    redirigir('../public/login.php');

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexion);

    // Eliminar la foto si ya fue movida y la transacción falló
    if ($fotoMovida) {
        unlink($fotoRutaFisica);
    }

    $_SESSION['error_general'] = 'No fue posible completar el registro. Inténtalo nuevamente.';
    redirigir('../public/registro-vendedor.php');
}
