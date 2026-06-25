<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendedor/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vendedor/editar-perfil.php');
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];

// ─── Leer campos ──────────────────────────────────────────────────────────────

$nombre      = trim($_POST['nombre']       ?? '');
$apellido    = trim($_POST['apellido']     ?? '');
$correo      = strtolower(trim($_POST['correo'] ?? ''));
$telefono    = trim($_POST['telefono']     ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$experiencia = trim($_POST['experiencia'] ?? '');
$zonaTrabajo = trim($_POST['zona_trabajo'] ?? '');

$datosPrev = [
    'nombre'      => $nombre,
    'apellido'    => $apellido,
    'correo'      => $correo,
    'telefono'    => $telefono,
    'descripcion' => $descripcion,
    'experiencia' => $experiencia,
    'zona_trabajo'=> $zonaTrabajo,
];

// ─── Validaciones ─────────────────────────────────────────────────────────────

$errores = [];

if ($nombre === '') {
    $errores['nombre'] = 'El nombre es obligatorio.';
} elseif (mb_strlen($nombre) > 100) {
    $errores['nombre'] = 'El nombre no puede superar 100 caracteres.';
}

if ($apellido === '') {
    $errores['apellido'] = 'El apellido es obligatorio.';
} elseif (mb_strlen($apellido) > 100) {
    $errores['apellido'] = 'El apellido no puede superar 100 caracteres.';
}

if ($correo === '') {
    $errores['correo'] = 'El correo electrónico es obligatorio.';
} elseif (mb_strlen($correo) > 150) {
    $errores['correo'] = 'El correo no puede superar 150 caracteres.';
} elseif (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
    $errores['correo'] = 'El correo no tiene un formato válido.';
}

$telefonoGuardar = $telefono !== '' ? $telefono : null;
if ($telefono !== '' && !preg_match('/^[0-9+\s()\-]{10,20}$/', $telefono)) {
    $errores['telefono'] = 'El teléfono debe tener entre 10 y 20 caracteres.';
}

$experienciaGuardar = null;
if ($experiencia !== '') {
    $exp = filter_var($experiencia, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
    if ($exp === false || $exp === null) {
        $errores['experiencia'] = 'Los años de experiencia deben ser un número entre 0 y 99.';
    } else {
        $experienciaGuardar = $exp;
    }
}

$descripcionGuardar = $descripcion !== '' ? $descripcion : null;
$zonaGuardar        = $zonaTrabajo !== '' ? $zonaTrabajo : null;

if (!empty($errores)) {
    $_SESSION['errores_perfil_vendedor'] = $errores;
    $_SESSION['datos_perfil_vendedor']   = $datosPrev;
    header('Location: ../vendedor/editar-perfil.php');
    exit;
}

// ─── Correo único (excluyendo al usuario actual) ──────────────────────────────

try {
    $stmtC = mysqli_prepare($conexion,
        'SELECT id_usuario FROM Usuario WHERE correo = ? AND id_usuario != ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtC, 'si', $correo, $idUsuario);
    mysqli_stmt_execute($stmtC);
    mysqli_stmt_store_result($stmtC);

    if (mysqli_stmt_num_rows($stmtC) > 0) {
        mysqli_stmt_close($stmtC);
        $_SESSION['errores_perfil_vendedor'] = ['correo' => 'Este correo ya está registrado por otro usuario.'];
        $_SESSION['datos_perfil_vendedor']   = $datosPrev;
        header('Location: ../vendedor/editar-perfil.php');
        exit;
    }
    mysqli_stmt_close($stmtC);
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible actualizar el perfil. Inténtalo nuevamente.';
    header('Location: ../vendedor/editar-perfil.php');
    exit;
}

// ─── Foto de perfil (opcional) ────────────────────────────────────────────────

$nuevaFotoRuta = null;

if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {

    $file = $_FILES['foto_perfil'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errores['foto_perfil'] = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'La imagen supera el tamaño permitido (5 MB).',
            UPLOAD_ERR_PARTIAL                        => 'La subida fue interrumpida.',
            default                                   => 'Error al subir la imagen.',
        };
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errores['foto_perfil'] = 'La imagen supera el tamaño máximo permitido (5 MB).';
    } else {
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($file['tmp_name']);
        $mimesOk  = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mimeReal, $mimesOk, true)) {
            $errores['foto_perfil'] = 'Formato no permitido. Usa JPG, PNG o WEBP.';
        } else {
            $ext    = match ($mimeReal) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            };
            $nombre_archivo = uniqid('vendedor_', true) . '.' . $ext;
            $dirDestino     = __DIR__ . '/../public/recursos/img/vendedores/';
            $rutaDestino    = $dirDestino . $nombre_archivo;

            if (!is_dir($dirDestino)) {
                mkdir($dirDestino, 0755, true);
            }

            if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                $errores['foto_perfil'] = 'No fue posible guardar la imagen. Inténtalo nuevamente.';
            } else {
                $nuevaFotoRuta = '../public/recursos/img/vendedores/' . $nombre_archivo;
            }
        }
    }

    if (!empty($errores)) {
        $_SESSION['errores_perfil_vendedor'] = $errores;
        $_SESSION['datos_perfil_vendedor']   = $datosPrev;
        header('Location: ../vendedor/editar-perfil.php');
        exit;
    }
}

// ─── Transacción: actualizar Usuario y Vendedor ───────────────────────────────

try {
    mysqli_begin_transaction($conexion);

    $stmtU = mysqli_prepare($conexion,
        'UPDATE Usuario SET nombre = ?, apellido = ?, correo = ?, telefono = ?
         WHERE id_usuario = ?'
    );
    mysqli_stmt_bind_param($stmtU, 'ssssi', $nombre, $apellido, $correo, $telefonoGuardar, $idUsuario);
    mysqli_stmt_execute($stmtU);
    mysqli_stmt_close($stmtU);

    if ($nuevaFotoRuta !== null) {
        $stmtV = mysqli_prepare($conexion,
            'UPDATE Vendedor SET descripcion = ?, experiencia = ?, zona_trabajo = ?, foto_perfil = ?
             WHERE id_usuario = ?'
        );
        mysqli_stmt_bind_param($stmtV, 'sissi', $descripcionGuardar, $experienciaGuardar, $zonaGuardar, $nuevaFotoRuta, $idUsuario);
    } else {
        $stmtV = mysqli_prepare($conexion,
            'UPDATE Vendedor SET descripcion = ?, experiencia = ?, zona_trabajo = ?
             WHERE id_usuario = ?'
        );
        mysqli_stmt_bind_param($stmtV, 'sisi', $descripcionGuardar, $experienciaGuardar, $zonaGuardar, $idUsuario);
    }
    mysqli_stmt_execute($stmtV);
    mysqli_stmt_close($stmtV);

    mysqli_commit($conexion);

    $_SESSION['nombre'] = $nombre;
    $_SESSION['correo'] = $correo;

    $_SESSION['mensaje_exito'] = 'Perfil actualizado correctamente.';
    header('Location: ../vendedor/perfil.php');
    exit;

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexion);
    $_SESSION['error_general'] = 'No fue posible guardar los cambios. Inténtalo nuevamente.';
    header('Location: ../vendedor/editar-perfil.php');
    exit;
}
