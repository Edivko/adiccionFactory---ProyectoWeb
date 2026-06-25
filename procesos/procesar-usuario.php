<?php

declare(strict_types=1);

require_once __DIR__ . '/../admin/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/index.php');
    exit;
}

$idAdmin = (int) $_SESSION['id_usuario'];

// ─── Leer y validar campos ────────────────────────────────────────────────────

$idUsuario = filter_var($_POST['id_usuario'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$accion    = trim($_POST['accion'] ?? '');
$volver    = trim($_POST['volver'] ?? 'usuarios');

$accionesPermitidas = ['aprobar', 'rechazar', 'bloquear', 'desbloquear'];

if (!$idUsuario || !in_array($accion, $accionesPermitidas, true)) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../admin/index.php');
    exit;
}

// Destino de redirección según formulario de origen
$destino = $volver === 'vendedores'
    ? '../admin/vendedores.php'
    : '../admin/usuarios.php';

// ─── Evitar autobloqueo ───────────────────────────────────────────────────────

if ((int) $idUsuario === $idAdmin) {
    $_SESSION['error_general'] = 'No puedes cambiar el estado de tu propia cuenta.';
    header('Location: ' . $destino);
    exit;
}

// ─── Verificar que el objetivo existe y no es administrador ───────────────────

$estadoActual = null;
$rolActual    = null;

try {
    $stmtChk = mysqli_prepare($conexion,
        'SELECT id_estado_cuenta, id_rol FROM Usuario WHERE id_usuario = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtChk, 'i', $idUsuario);
    mysqli_stmt_execute($stmtChk);
    mysqli_stmt_bind_result($stmtChk, $estadoActual, $rolActual);
    $existe = mysqli_stmt_fetch($stmtChk);
    mysqli_stmt_close($stmtChk);
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible verificar el usuario.';
    header('Location: ' . $destino);
    exit;
}

if (!$existe) {
    $_SESSION['error_general'] = 'El usuario no existe.';
    header('Location: ' . $destino);
    exit;
}

// Los administradores no se gestionan desde estas pantallas
if ((int) $rolActual === 3) {
    $_SESSION['error_general'] = 'No se puede modificar la cuenta de un administrador.';
    header('Location: ' . $destino);
    exit;
}

// ─── Validar transición según estado actual ───────────────────────────────────

// EstadoCuenta: 1=pendiente, 2=activa, 3=bloqueada, 4=rechazada
$estadoActual = (int) $estadoActual;

$transiciones = [
    // accion       => [estados_permitidos_de_origen, nuevo_estado]
    'aprobar'     => [[1],    2],   // pendiente → activa
    'rechazar'    => [[1],    4],   // pendiente → rechazada
    'bloquear'    => [[2],    3],   // activa    → bloqueada
    'desbloquear' => [[3],    2],   // bloqueada → activa
];

[$estadosOrigen, $nuevoEstado] = $transiciones[$accion];

if (!in_array($estadoActual, $estadosOrigen, true)) {
    $mensajes = [
        'aprobar'     => 'Solo se puede aprobar una cuenta pendiente.',
        'rechazar'    => 'Solo se puede rechazar una cuenta pendiente.',
        'bloquear'    => 'Solo se puede bloquear una cuenta activa.',
        'desbloquear' => 'Solo se puede desbloquear una cuenta bloqueada.',
    ];
    $_SESSION['error_general'] = $mensajes[$accion];
    header('Location: ' . $destino);
    exit;
}

// ─── Aplicar cambio ───────────────────────────────────────────────────────────

try {
    $stmtUpd = mysqli_prepare($conexion,
        'UPDATE Usuario SET id_estado_cuenta = ? WHERE id_usuario = ?'
    );
    mysqli_stmt_bind_param($stmtUpd, 'ii', $nuevoEstado, $idUsuario);
    mysqli_stmt_execute($stmtUpd);
    mysqli_stmt_close($stmtUpd);

    $mensajesOk = [
        'aprobar'     => 'Vendedor aprobado. La cuenta ya está activa.',
        'rechazar'    => 'Solicitud rechazada.',
        'bloquear'    => 'Usuario bloqueado.',
        'desbloquear' => 'Usuario desbloqueado. La cuenta está activa nuevamente.',
    ];
    $_SESSION['mensaje_exito'] = $mensajesOk[$accion];

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible aplicar el cambio. Inténtalo nuevamente.';
}

header('Location: ' . $destino);
exit;
