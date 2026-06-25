<?php

declare(strict_types=1);

require_once __DIR__ . '/../admin/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/inmuebles.php');
    exit;
}

// id_perfil = id_administrador (Administrador.id_administrador)
$idAdministrador = (int) $_SESSION['id_perfil'];

// ─── Leer y validar campos ────────────────────────────────────────────────────

$idInmueble = filter_var($_POST['id_inmueble'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$accion     = trim($_POST['accion'] ?? '');
$motivo     = trim($_POST['motivo'] ?? '');

$redireccionError = '../admin/revisar-inmueble.php?id=' . (int) ($idInmueble ?: 0);

if (!$idInmueble || !in_array($accion, ['aprobar', 'rechazar'], true)) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../admin/inmuebles.php');
    exit;
}

$redireccionError = '../admin/revisar-inmueble.php?id=' . $idInmueble;

// Motivo obligatorio si se rechaza
if ($accion === 'rechazar' && $motivo === '') {
    $_SESSION['error_general'] = 'El motivo es obligatorio para rechazar un inmueble.';
    header('Location: ' . $redireccionError);
    exit;
}

$motivoGuardar = $motivo !== '' ? $motivo : null;

// ─── Verificar que el inmueble existe y está en estado pendiente (2) ──────────

$estadoActual = null;

try {
    $stmtChk = mysqli_prepare($conexion,
        'SELECT id_estado_publicacion FROM Inmueble WHERE id_inmueble = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtChk, 'i', $idInmueble);
    mysqli_stmt_execute($stmtChk);
    mysqli_stmt_bind_result($stmtChk, $estadoActual);
    $existe = mysqli_stmt_fetch($stmtChk);
    mysqli_stmt_close($stmtChk);
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar el inmueble.';
    header('Location: ' . $redireccionError);
    exit;
}

if (!$existe) {
    $_SESSION['error_general'] = 'El inmueble no existe.';
    header('Location: ../admin/inmuebles.php');
    exit;
}

if ((int) $estadoActual !== 2) {
    $_SESSION['error_general'] = 'Solo se pueden revisar inmuebles en estado pendiente.';
    header('Location: ' . $redireccionError);
    exit;
}

// EstadoPublicacion: aprobar → 3 (publicado), rechazar → 4 (rechazado)
$nuevoEstado = $accion === 'aprobar' ? 3 : 4;

// ─── Transacción: INSERT RevisionInmueble + UPDATE Inmueble ──────────────────

try {
    mysqli_begin_transaction($conexion);

    // 1. Insertar fila de revisión (cada decisión es un registro nuevo)
    $stmtRev = mysqli_prepare($conexion,
        'INSERT INTO RevisionInmueble
             (id_inmueble, id_administrador, id_estado_publicacion, motivo)
         VALUES (?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($stmtRev, 'iiis',
        $idInmueble,
        $idAdministrador,
        $nuevoEstado,
        $motivoGuardar
    );
    mysqli_stmt_execute($stmtRev);
    mysqli_stmt_close($stmtRev);

    // 2. Actualizar estado de publicación del inmueble
    $stmtUpd = mysqli_prepare($conexion,
        'UPDATE Inmueble SET id_estado_publicacion = ? WHERE id_inmueble = ?'
    );
    mysqli_stmt_bind_param($stmtUpd, 'ii', $nuevoEstado, $idInmueble);
    mysqli_stmt_execute($stmtUpd);
    mysqli_stmt_close($stmtUpd);

    mysqli_commit($conexion);

    $mensajesOk = [
        'aprobar'  => 'Inmueble aprobado y publicado correctamente.',
        'rechazar' => 'Inmueble rechazado. El vendedor podrá corregirlo y enviarlo de nuevo.',
    ];
    $_SESSION['mensaje_exito'] = $mensajesOk[$accion];
    header('Location: ../admin/revisar-inmueble.php?id=' . $idInmueble);
    exit;

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexion);
    $_SESSION['error_general'] = 'No fue posible registrar la revisión. Inténtalo nuevamente.';
    header('Location: ' . $redireccionError);
    exit;
}
