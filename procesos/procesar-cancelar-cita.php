<?php

declare(strict_types=1);

require_once __DIR__ . '/../comprador/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../comprador/citas.php');
    exit;
}

$idComprador = (int) $_SESSION['id_perfil'];

// ─── Leer y validar id_cita ───────────────────────────────────────────────────

$idCita = filter_var($_POST['id_cita'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($idCita === false || $idCita === null) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../comprador/citas.php');
    exit;
}

// ─── Verificar que la cita pertenece al comprador y está en estado cancelable ──

try {
    $stmt = mysqli_prepare($conexion,
        'SELECT id_estado_cita
         FROM Cita
         WHERE id_cita = ? AND id_comprador = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'ii', $idCita, $idComprador);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $idEstadoActual);
    $encontrada = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$encontrada) {
        $_SESSION['error_general'] = 'La cita no existe o no te pertenece.';
        header('Location: ../comprador/citas.php');
        exit;
    }

    // Solo se puede cancelar si está pendiente (1) o aceptada (2)
    if (!in_array((int) $idEstadoActual, [1, 2], true)) {
        $_SESSION['error_general'] = 'Solo puedes cancelar citas pendientes o aceptadas.';
        header('Location: ../comprador/citas.php');
        exit;
    }

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible procesar la solicitud. Inténtalo nuevamente.';
    header('Location: ../comprador/citas.php');
    exit;
}

// ─── Actualizar estado a cancelada (4) ───────────────────────────────────────

try {
    $stmtU = mysqli_prepare($conexion,
        'UPDATE Cita SET id_estado_cita = 4
         WHERE id_cita = ? AND id_comprador = ?'
    );
    mysqli_stmt_bind_param($stmtU, 'ii', $idCita, $idComprador);
    mysqli_stmt_execute($stmtU);
    mysqli_stmt_close($stmtU);

    $_SESSION['mensaje_exito'] = 'La cita ha sido cancelada.';
    header('Location: ../comprador/citas.php');
    exit;

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible cancelar la cita. Inténtalo nuevamente.';
    header('Location: ../comprador/citas.php');
    exit;
}
