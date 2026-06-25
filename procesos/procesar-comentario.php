<?php

declare(strict_types=1);

require_once __DIR__ . '/../comprador/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../comprador/comentarios.php');
    exit;
}

$idComprador = (int) $_SESSION['id_perfil'];
$idUsuario   = (int) $_SESSION['id_usuario'];

// ─── Leer y validar campos ───────────────────────────────────────────────────

$idCita    = filter_var($_POST['id_cita'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$tipo      = trim($_POST['tipo']     ?? '');
$contenido = trim($_POST['contenido'] ?? '');

if ($idCita === false || $idCita === null) {
    $_SESSION['error_general'] = 'Debes seleccionar una cita válida.';
    header('Location: ../comprador/comentarios.php');
    exit;
}

if (!in_array($tipo, ['inmueble', 'vendedor'], true)) {
    $_SESSION['error_general'] = 'Debes indicar si el comentario es sobre el inmueble o el vendedor.';
    header('Location: ../comprador/comentarios.php');
    exit;
}

if ($contenido === '') {
    $_SESSION['error_general'] = 'El comentario no puede estar vacío.';
    header('Location: ../comprador/comentarios.php');
    exit;
}

if (mb_strlen($contenido) > 2000) {
    $_SESSION['error_general'] = 'El comentario no puede superar los 2000 caracteres.';
    header('Location: ../comprador/comentarios.php');
    exit;
}

// ─── Verificar que la cita pertenece al comprador y está realizada ────────────

$idInmuebleCita = null;
$idVendedorCita = null;

try {
    $stmt = mysqli_prepare($conexion,
        'SELECT id_inmueble, id_vendedor
         FROM Cita
         WHERE id_cita = ? AND id_comprador = ? AND id_estado_cita = 5
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'ii', $idCita, $idComprador);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $idInmuebleCita, $idVendedorCita);
    $encontrada = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$encontrada) {
        $_SESSION['error_general'] = 'La cita seleccionada no existe o no ha sido realizada.';
        header('Location: ../comprador/comentarios.php');
        exit;
    }

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar la cita. Inténtalo nuevamente.';
    header('Location: ../comprador/comentarios.php');
    exit;
}

// ─── Asignar id_inmueble / id_vendedor según tipo (XOR) ──────────────────────

$idInmuebleComentario = null;
$idVendedorComentario = null;

if ($tipo === 'inmueble') {
    $idInmuebleComentario = (int) $idInmuebleCita;
} else {
    $idVendedorComentario = (int) $idVendedorCita;
}

// ─── Insertar comentario (estado pendiente = 1) ───────────────────────────────

try {
    $stmtIns = mysqli_prepare($conexion, '
        INSERT INTO Comentario
            (id_usuario, id_cita, id_vendedor, id_inmueble, id_estado_comentario, contenido)
        VALUES (?, ?, ?, ?, 1, ?)
    ');
    mysqli_stmt_bind_param(
        $stmtIns,
        'iiiis',
        $idUsuario,
        $idCita,
        $idVendedorComentario,
        $idInmuebleComentario,
        $contenido
    );
    mysqli_stmt_execute($stmtIns);
    mysqli_stmt_close($stmtIns);

    $_SESSION['mensaje_exito'] = 'Comentario enviado correctamente. Será visible una vez que sea revisado por el equipo.';
    header('Location: ../comprador/comentarios.php');
    exit;

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible guardar el comentario. Inténtalo nuevamente.';
    header('Location: ../comprador/comentarios.php');
    exit;
}
