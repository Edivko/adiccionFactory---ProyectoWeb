<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendedor/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vendedor/citas.php');
    exit;
}

$idVendedor = (int) $_SESSION['id_perfil'];

// ─── Leer y validar campos ────────────────────────────────────────────────────

$idCita = filter_var($_POST['id_cita'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$accion = trim($_POST['accion'] ?? '');

if ($idCita === false || $idCita === null) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../vendedor/citas.php');
    exit;
}

$accionesPermitidas = ['aceptar', 'rechazar', 'realizada'];
if (!in_array($accion, $accionesPermitidas, true)) {
    $_SESSION['error_general'] = 'Acción no reconocida.';
    header('Location: ../vendedor/citas.php');
    exit;
}

// ─── Verificar que la cita pertenece a este vendedor ─────────────────────────

$estadoActual = null;

try {
    $stmt = mysqli_prepare($conexion,
        'SELECT id_estado_cita FROM Cita
         WHERE id_cita = ? AND id_vendedor = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'ii', $idCita, $idVendedor);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $estadoActual);
    $existe = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$existe) {
        $_SESSION['error_general'] = 'La cita no existe o no te pertenece.';
        header('Location: ../vendedor/citas.php');
        exit;
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible procesar la solicitud. Inténtalo nuevamente.';
    header('Location: ../vendedor/citas.php');
    exit;
}

// ─── Determinar nuevo estado y validar transición ─────────────────────────────

// EstadoCita: 1=pendiente, 2=aceptada, 3=rechazada, 4=cancelada, 5=realizada
$transiciones = [
    'aceptar'   => ['desde' => 1, 'hacia' => 2],
    'rechazar'  => ['desde' => 1, 'hacia' => 3],
    'realizada' => ['desde' => 2, 'hacia' => 5],
];

$t = $transiciones[$accion];

if ((int) $estadoActual !== $t['desde']) {
    $mensajes = [
        'aceptar'   => 'Solo puedes aceptar citas pendientes.',
        'rechazar'  => 'Solo puedes rechazar citas pendientes.',
        'realizada' => 'Solo puedes marcar como realizada una cita aceptada.',
    ];
    $_SESSION['error_general'] = $mensajes[$accion];
    header('Location: ../vendedor/citas.php');
    exit;
}

$nuevoEstado = $t['hacia'];

// ─── Actualizar estado ────────────────────────────────────────────────────────

try {
    $stmtU = mysqli_prepare($conexion,
        'UPDATE Cita SET id_estado_cita = ?
         WHERE id_cita = ? AND id_vendedor = ?'
    );
    mysqli_stmt_bind_param($stmtU, 'iii', $nuevoEstado, $idCita, $idVendedor);
    mysqli_stmt_execute($stmtU);
    mysqli_stmt_close($stmtU);

    $mensajesOk = [
        'aceptar'   => 'Cita aceptada. El comprador será notificado.',
        'rechazar'  => 'Cita rechazada.',
        'realizada' => 'Cita marcada como realizada.',
    ];
    $_SESSION['mensaje_exito'] = $mensajesOk[$accion];
    header('Location: ../vendedor/citas.php');
    exit;

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible actualizar la cita. Inténtalo nuevamente.';
    header('Location: ../vendedor/citas.php');
    exit;
}
