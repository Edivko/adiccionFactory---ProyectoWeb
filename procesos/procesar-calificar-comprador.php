<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendedor/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vendedor/calificar-comprador.php');
    exit;
}

$idVendedor = (int) $_SESSION['id_perfil'];

// ─── Leer y validar campos ────────────────────────────────────────────────────

$idCita = filter_var($_POST['id_cita'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$puntuacionRaw = trim($_POST['puntuacion'] ?? '');
$comentario    = trim($_POST['comentario'] ?? '');

if ($idCita === false || $idCita === null) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../vendedor/calificar-comprador.php');
    exit;
}

$puntuacion = filter_var($puntuacionRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);

if ($puntuacion === false || $puntuacion === null) {
    $_SESSION['error_general'] = 'La puntuación debe ser un número entre 1 y 5.';
    header('Location: ../vendedor/calificar-comprador.php');
    exit;
}

$comentarioGuardar = $comentario !== '' ? $comentario : null;

// ─── Verificar que la cita pertenece al vendedor y está realizada ──────────────

$idComprador = null;

try {
    $stmt = mysqli_prepare($conexion,
        'SELECT id_comprador FROM Cita
         WHERE id_cita = ? AND id_vendedor = ? AND id_estado_cita = 5
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'ii', $idCita, $idVendedor);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $idComprador);
    $encontrada = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$encontrada) {
        $_SESSION['error_general'] = 'La cita no existe, no te pertenece o aún no ha sido realizada.';
        header('Location: ../vendedor/calificar-comprador.php');
        exit;
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar la cita. Inténtalo nuevamente.';
    header('Location: ../vendedor/calificar-comprador.php');
    exit;
}

// ─── Verificar que no existe ya una calificación para esta cita ───────────────

try {
    $stmtEx = mysqli_prepare($conexion,
        'SELECT id_calificacion_comprador FROM CalificacionComprador
         WHERE id_cita = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtEx, 'i', $idCita);
    mysqli_stmt_execute($stmtEx);
    mysqli_stmt_store_result($stmtEx);

    if (mysqli_stmt_num_rows($stmtEx) > 0) {
        mysqli_stmt_close($stmtEx);
        $_SESSION['error_general'] = 'Ya enviaste una calificación para esta cita.';
        header('Location: ../vendedor/calificar-comprador.php');
        exit;
    }
    mysqli_stmt_close($stmtEx);
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar la calificación. Inténtalo nuevamente.';
    header('Location: ../vendedor/calificar-comprador.php');
    exit;
}

// ─── Insertar calificación ────────────────────────────────────────────────────

try {
    $stmtIns = mysqli_prepare($conexion, '
        INSERT INTO CalificacionComprador
            (id_vendedor, id_comprador, id_cita, puntuacion, comentario)
        VALUES (?, ?, ?, ?, ?)
    ');
    mysqli_stmt_bind_param(
        $stmtIns,
        'iiiis',
        $idVendedor,
        $idComprador,
        $idCita,
        $puntuacion,
        $comentarioGuardar
    );
    mysqli_stmt_execute($stmtIns);
    mysqli_stmt_close($stmtIns);

    $_SESSION['mensaje_exito'] = 'Calificación enviada correctamente.';
    header('Location: ../vendedor/calificar-comprador.php');
    exit;

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        $_SESSION['error_general'] = 'Ya enviaste una calificación para esta cita.';
    } else {
        $_SESSION['error_general'] = 'No fue posible guardar la calificación. Inténtalo nuevamente.';
    }
    header('Location: ../vendedor/calificar-comprador.php');
    exit;
}
