<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendedor/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vendedor/mis-inmuebles.php');
    exit;
}

// id_perfil = id_vendedor (Vendedor.id_vendedor)
$idVendedor = (int) $_SESSION['id_perfil'];

// ─── Campos comunes ───────────────────────────────────────────────────────────

$accion     = trim($_POST['accion'] ?? '');
$idInmueble = filter_var($_POST['id_inmueble'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$idInmueble || !in_array($accion, ['subir', 'principal', 'eliminar'], true)) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../vendedor/mis-inmuebles.php');
    exit;
}

$redireccion = '../vendedor/subir-fotos.php?id=' . $idInmueble;

// ─── Verificar propiedad: Inmueble.id_usuario_publicador → Vendedor.id_vendedor ─

try {
    $stmtOwn = mysqli_prepare($conexion,
        'SELECT i.id_inmueble
         FROM Inmueble i
         INNER JOIN Vendedor v ON v.id_usuario = i.id_usuario_publicador
         WHERE i.id_inmueble = ? AND v.id_vendedor = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtOwn, 'ii', $idInmueble, $idVendedor);
    mysqli_stmt_execute($stmtOwn);
    mysqli_stmt_store_result($stmtOwn);
    $esOwner = mysqli_stmt_num_rows($stmtOwn) > 0;
    mysqli_stmt_close($stmtOwn);
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar el inmueble.';
    header('Location: ' . $redireccion);
    exit;
}

if (!$esOwner) {
    header('Location: ../vendedor/mis-inmuebles.php');
    exit;
}

// =============================================================================
// ACCIÓN: SUBIR FOTOS
// =============================================================================

if ($accion === 'subir') {

    if (empty($_FILES['fotos']['name'][0])) {
        $_SESSION['error_general'] = 'Selecciona al menos una imagen.';
        header('Location: ' . $redireccion);
        exit;
    }

    $descripcion = trim($_POST['descripcion'] ?? '');
    $descGuardar = $descripcion !== '' ? mb_substr($descripcion, 0, 150) : null;

    // MIME real → extensión de salida
    $mimeAExt = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    $maxBytes   = 5 * 1024 * 1024; // 5 MB
    $dirDestino = __DIR__ . '/../public/recursos/uploads/inmuebles/';

    $total   = count($_FILES['fotos']['name']);
    $subidas = 0;
    $errores = [];

    for ($i = 0; $i < $total; $i++) {

        // Error de subida de PHP
        if ((int) $_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
            $errores[] = 'No se pudo recibir el archivo ' . ($i + 1) . '.';
            continue;
        }

        // Tamaño
        if ((int) $_FILES['fotos']['size'][$i] > $maxBytes) {
            $errores[] = htmlspecialchars($_FILES['fotos']['name'][$i], ENT_QUOTES, 'UTF-8')
                       . ': supera el límite de 5 MB.';
            continue;
        }

        // MIME real (ignora la extensión y el tipo declarado por el cliente)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($_FILES['fotos']['tmp_name'][$i]);

        if (!isset($mimeAExt[$mimeReal])) {
            $errores[] = htmlspecialchars($_FILES['fotos']['name'][$i], ENT_QUOTES, 'UTF-8')
                       . ': formato no permitido. Usa JPG, PNG o WEBP.';
            continue;
        }

        $ext           = $mimeAExt[$mimeReal];
        $nombreArchivo = uniqid('inmueble_', true) . '.' . $ext;
        $rutaDestino   = $dirDestino . $nombreArchivo;

        if (!move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $rutaDestino)) {
            $errores[] = 'No se pudo guardar el archivo ' . ($i + 1) . ' en el servidor.';
            continue;
        }

        // Ruta relativa guardada en BD (sin "public/")
        $urlRelativa = 'recursos/uploads/inmuebles/' . $nombreArchivo;

        try {
            $stmtIns = mysqli_prepare($conexion,
                'INSERT INTO FotoInmueble (id_inmueble, url_foto, descripcion, principal)
                 VALUES (?, ?, ?, FALSE)'
            );
            mysqli_stmt_bind_param($stmtIns, 'iss', $idInmueble, $urlRelativa, $descGuardar);
            mysqli_stmt_execute($stmtIns);
            mysqli_stmt_close($stmtIns);
            $subidas++;
        } catch (mysqli_sql_exception $e) {
            @unlink($rutaDestino); // revertir archivo si falla el INSERT
            $errores[] = 'Error al registrar el archivo ' . ($i + 1) . ' en la base de datos.';
        }
    }

    if ($subidas > 0) {
        $_SESSION['mensaje_exito'] = $subidas === 1
            ? 'Se subió 1 foto correctamente.'
            : "Se subieron {$subidas} fotos correctamente.";
    }

    if (!empty($errores)) {
        $_SESSION['error_general'] = implode(' | ', $errores);
    }

    header('Location: ' . $redireccion);
    exit;
}

// =============================================================================
// ACCIÓN: MARCAR COMO PRINCIPAL
// =============================================================================

if ($accion === 'principal') {

    $idFoto = filter_var($_POST['id_foto'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$idFoto) {
        $_SESSION['error_general'] = 'Foto no válida.';
        header('Location: ' . $redireccion);
        exit;
    }

    try {
        $stmtChk = mysqli_prepare($conexion,
            'SELECT id_foto FROM FotoInmueble
             WHERE id_foto = ? AND id_inmueble = ?
             LIMIT 1'
        );
        mysqli_stmt_bind_param($stmtChk, 'ii', $idFoto, $idInmueble);
        mysqli_stmt_execute($stmtChk);
        mysqli_stmt_store_result($stmtChk);
        $existe = mysqli_stmt_num_rows($stmtChk) > 0;
        mysqli_stmt_close($stmtChk);
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_general'] = 'No fue posible verificar la foto.';
        header('Location: ' . $redireccion);
        exit;
    }

    if (!$existe) {
        $_SESSION['error_general'] = 'La foto no existe o no pertenece a este inmueble.';
        header('Location: ' . $redireccion);
        exit;
    }

    try {
        mysqli_begin_transaction($conexion);

        $stmtQ = mysqli_prepare($conexion,
            'UPDATE FotoInmueble SET principal = FALSE WHERE id_inmueble = ?'
        );
        mysqli_stmt_bind_param($stmtQ, 'i', $idInmueble);
        mysqli_stmt_execute($stmtQ);
        mysqli_stmt_close($stmtQ);

        $stmtM = mysqli_prepare($conexion,
            'UPDATE FotoInmueble SET principal = TRUE WHERE id_foto = ? AND id_inmueble = ?'
        );
        mysqli_stmt_bind_param($stmtM, 'ii', $idFoto, $idInmueble);
        mysqli_stmt_execute($stmtM);
        mysqli_stmt_close($stmtM);

        mysqli_commit($conexion);
        $_SESSION['mensaje_exito'] = 'Foto principal actualizada.';

    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($conexion);
        $_SESSION['error_general'] = 'No fue posible actualizar la foto principal.';
    }

    header('Location: ' . $redireccion);
    exit;
}

// =============================================================================
// ACCIÓN: ELIMINAR FOTO
// =============================================================================

if ($accion === 'eliminar') {

    $idFoto = filter_var($_POST['id_foto'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$idFoto) {
        $_SESSION['error_general'] = 'Foto no válida.';
        header('Location: ' . $redireccion);
        exit;
    }

    $urlFoto = null;

    try {
        $stmtSel = mysqli_prepare($conexion,
            'SELECT url_foto FROM FotoInmueble
             WHERE id_foto = ? AND id_inmueble = ?
             LIMIT 1'
        );
        mysqli_stmt_bind_param($stmtSel, 'ii', $idFoto, $idInmueble);
        mysqli_stmt_execute($stmtSel);
        mysqli_stmt_bind_result($stmtSel, $urlFoto);
        $existe = mysqli_stmt_fetch($stmtSel);
        mysqli_stmt_close($stmtSel);
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_general'] = 'No fue posible verificar la foto.';
        header('Location: ' . $redireccion);
        exit;
    }

    if (!$existe) {
        $_SESSION['error_general'] = 'La foto no existe o no pertenece a este inmueble.';
        header('Location: ' . $redireccion);
        exit;
    }

    try {
        $stmtDel = mysqli_prepare($conexion,
            'DELETE FROM FotoInmueble WHERE id_foto = ? AND id_inmueble = ?'
        );
        mysqli_stmt_bind_param($stmtDel, 'ii', $idFoto, $idInmueble);
        mysqli_stmt_execute($stmtDel);
        mysqli_stmt_close($stmtDel);

        // url_foto = "recursos/uploads/inmuebles/nombre.jpg"
        // archivo físico = public/recursos/uploads/inmuebles/nombre.jpg
        $rutaArchivo = __DIR__ . '/../public/' . $urlFoto;
        if (is_file($rutaArchivo)) {
            @unlink($rutaArchivo);
        }

        $_SESSION['mensaje_exito'] = 'Foto eliminada correctamente.';

    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_general'] = 'No fue posible eliminar la foto.';
    }

    header('Location: ' . $redireccion);
    exit;
}
