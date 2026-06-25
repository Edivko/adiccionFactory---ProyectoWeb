<?php

declare(strict_types=1);

require_once __DIR__ . '/../admin/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/comentarios.php');
    exit;
}

// ─── Leer y validar campos ────────────────────────────────────────────────────

$idComentario = filter_var($_POST['id_comentario'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$accion       = trim($_POST['accion'] ?? '');
$volverEstado = trim($_POST['volver_estado'] ?? '');

// Mapa cerrado accion → id_estado_comentario
// EstadoComentario: 1=pendiente, 2=visible, 3=oculto, 4=eliminado
$accionAEstado = [
    'visible'   => 2,
    'oculto'    => 3,
    'eliminado' => 4,
];

if (!$idComentario || !isset($accionAEstado[$accion])) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../admin/comentarios.php');
    exit;
}

$nuevoEstado = $accionAEstado[$accion];

// Construir URL de retorno conservando el filtro activo
$redireccion = '../admin/comentarios.php';
if ($volverEstado !== '' && ctype_digit($volverEstado)) {
    $redireccion .= '?estado=' . (int) $volverEstado;
}

// ─── Verificar que el comentario existe ──────────────────────────────────────

try {
    $stmtChk = mysqli_prepare($conexion,
        'SELECT id_comentario FROM Comentario WHERE id_comentario = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtChk, 'i', $idComentario);
    mysqli_stmt_execute($stmtChk);
    mysqli_stmt_store_result($stmtChk);
    $existe = mysqli_stmt_num_rows($stmtChk) > 0;
    mysqli_stmt_close($stmtChk);
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar el comentario.';
    header('Location: ' . $redireccion);
    exit;
}

if (!$existe) {
    $_SESSION['error_general'] = 'El comentario no existe.';
    header('Location: ' . $redireccion);
    exit;
}

// ─── Actualizar id_estado_comentario ─────────────────────────────────────────

try {
    $stmtUpd = mysqli_prepare($conexion,
        'UPDATE Comentario SET id_estado_comentario = ? WHERE id_comentario = ?'
    );
    mysqli_stmt_bind_param($stmtUpd, 'ii', $nuevoEstado, $idComentario);
    mysqli_stmt_execute($stmtUpd);
    mysqli_stmt_close($stmtUpd);

    $mensajesOk = [
        'visible'   => 'Comentario marcado como visible.',
        'oculto'    => 'Comentario ocultado.',
        'eliminado' => 'Comentario marcado como eliminado.',
    ];
    $_SESSION['mensaje_exito'] = $mensajesOk[$accion];

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible actualizar el comentario.';
}

header('Location: ' . $redireccion);
exit;
