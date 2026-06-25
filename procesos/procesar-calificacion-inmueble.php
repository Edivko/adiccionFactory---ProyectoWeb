<?php

declare(strict_types=1);

require_once __DIR__ . '/../comprador/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../comprador/calificar-inmueble.php');
    exit;
}

$idComprador = (int) $_SESSION['id_perfil'];

// ─── Leer y validar campos ───────────────────────────────────────────────────

$idCita = filter_var($_POST['id_cita'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$puntuacionRaw = trim($_POST['puntuacion'] ?? '');
$comentario    = trim($_POST['comentario'] ?? '');

if ($idCita === false || $idCita === null) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../comprador/calificar-inmueble.php');
    exit;
}

$puntuacion = filter_var($puntuacionRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);

if ($puntuacion === false || $puntuacion === null) {
    $_SESSION['error_general'] = 'La puntuación debe ser un número del 1 al 5.';
    header('Location: ../comprador/calificar-inmueble.php');
    exit;
}

$comentarioGuardar = $comentario !== '' ? $comentario : null;

// ─── Verificar que la cita pertenece al comprador y está realizada ────────────

$idInmueble = null;

try {
    $stmt = mysqli_prepare($conexion,
        'SELECT id_inmueble
         FROM Cita
         WHERE id_cita = ? AND id_comprador = ? AND id_estado_cita = 5
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'ii', $idCita, $idComprador);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $idInmueble);
    $encontrada = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$encontrada) {
        $_SESSION['error_general'] = 'La cita no existe, no te pertenece o no ha sido realizada.';
        header('Location: ../comprador/calificar-inmueble.php');
        exit;
    }

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar la cita. Inténtalo nuevamente.';
    header('Location: ../comprador/calificar-inmueble.php');
    exit;
}

// ─── Verificar que no existe ya una calificación para esta cita ───────────────

try {
    $stmtEx = mysqli_prepare($conexion,
        'SELECT id_calificacion_inmueble FROM CalificacionInmueble WHERE id_cita = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtEx, 'i', $idCita);
    mysqli_stmt_execute($stmtEx);
    mysqli_stmt_store_result($stmtEx);

    if (mysqli_stmt_num_rows($stmtEx) > 0) {
        mysqli_stmt_close($stmtEx);
        $_SESSION['error_general'] = 'Ya enviaste una calificación para esta cita.';
        header('Location: ../comprador/calificar-inmueble.php');
        exit;
    }
    mysqli_stmt_close($stmtEx);

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar la calificación. Inténtalo nuevamente.';
    header('Location: ../comprador/calificar-inmueble.php');
    exit;
}

// ─── Insertar calificación ────────────────────────────────────────────────────

try {
    $stmtIns = mysqli_prepare($conexion, '
        INSERT INTO CalificacionInmueble
            (id_comprador, id_inmueble, id_cita, puntuacion, comentario)
        VALUES (?, ?, ?, ?, ?)
    ');
    mysqli_stmt_bind_param(
        $stmtIns,
        'iiiis',
        $idComprador,
        $idInmueble,
        $idCita,
        $puntuacion,
        $comentarioGuardar
    );
    mysqli_stmt_execute($stmtIns);
    mysqli_stmt_close($stmtIns);

    $_SESSION['mensaje_exito'] = 'Calificación del inmueble enviada correctamente. ¡Gracias por tu reseña!';
    header('Location: ../comprador/calificar-inmueble.php');
    exit;

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        $_SESSION['error_general'] = 'Ya enviaste una calificación para esta cita.';
    } else {
        $_SESSION['error_general'] = 'No fue posible guardar la calificación. Inténtalo nuevamente.';
    }
    header('Location: ../comprador/calificar-inmueble.php');
    exit;
}
