<?php

declare(strict_types=1);

require_once __DIR__ . '/../comprador/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../comprador/agendar.php');
    exit;
}

$idComprador = (int) $_SESSION['id_perfil'];

// ─── Leer campos del formulario ───────────────────────────────────────────────

$idInmuebleRaw = $_POST['id_inmueble'] ?? '';
$idVendedorRaw = $_POST['id_vendedor'] ?? '';
$fechaRaw      = trim($_POST['fecha']  ?? '');
$horaRaw       = trim($_POST['hora']   ?? '');
$comentario    = trim($_POST['comentario_solicitud'] ?? '');

function redireccionError(string $msg, int $idInm = 0): never {
    $_SESSION['error_agendar'] = $msg;
    $dest = $idInm > 0
        ? '../comprador/agendar.php?id=' . $idInm
        : '../comprador/agendar.php';
    header('Location: ' . $dest);
    exit;
}

// ─── Validar IDs ──────────────────────────────────────────────────────────────

$idInmueble = filter_var($idInmuebleRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$idVendedor = filter_var($idVendedorRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($idInmueble === false || $idInmueble === null) {
    redireccionError('Debes seleccionar una propiedad válida.');
}
if ($idVendedor === false || $idVendedor === null) {
    redireccionError('Debes seleccionar un vendedor.', (int) $idInmueble);
}

// ─── Validar fecha y hora ─────────────────────────────────────────────────────

if ($fechaRaw === '') {
    redireccionError('La fecha es obligatoria.', (int) $idInmueble);
}
if ($horaRaw === '') {
    redireccionError('La hora es obligatoria.', (int) $idInmueble);
}

// Comprobar formato de fecha (YYYY-MM-DD) y hora (HH:MM)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaRaw) || !strtotime($fechaRaw)) {
    redireccionError('La fecha no tiene un formato válido.', (int) $idInmueble);
}
if (!preg_match('/^\d{2}:\d{2}$/', $horaRaw)) {
    redireccionError('La hora no tiene un formato válido.', (int) $idInmueble);
}

$fechaInicio = $fechaRaw . ' ' . $horaRaw . ':00';
$tsFechaInicio = strtotime($fechaInicio);

if ($tsFechaInicio === false || $tsFechaInicio <= time()) {
    redireccionError('La fecha y hora deben ser futuras.', (int) $idInmueble);
}

[$horaH, $horaM] = explode(':', $horaRaw);
$horaDecimal = (int) $horaH + (int) $horaM / 60;

if ($horaDecimal < 9.0 || $horaDecimal > 17.0) {
    redireccionError('El horario de atención es de 09:00 a 17:00.', (int) $idInmueble);
}

$fechaFin = date('Y-m-d H:i:s', $tsFechaInicio + 3600);

$comentarioGuardar = $comentario !== '' ? $comentario : null;

// ─── Verificar que el inmueble esté publicado ─────────────────────────────────

try {
    $stmtI = mysqli_prepare($conexion,
        'SELECT id_inmueble FROM Inmueble WHERE id_inmueble = ? AND id_estado_publicacion = 3 LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtI, 'i', $idInmueble);
    mysqli_stmt_execute($stmtI);
    mysqli_stmt_store_result($stmtI);

    if (mysqli_stmt_num_rows($stmtI) === 0) {
        mysqli_stmt_close($stmtI);
        redireccionError('La propiedad seleccionada no está disponible.', (int) $idInmueble);
    }
    mysqli_stmt_close($stmtI);

} catch (mysqli_sql_exception $e) {
    redireccionError('No fue posible verificar la propiedad. Inténtalo nuevamente.', (int) $idInmueble);
}

// ─── Verificar que el vendedor esté asignado al inmueble ──────────────────────

try {
    $stmtV = mysqli_prepare($conexion,
        'SELECT id_inmueble_vendedor
         FROM InmuebleVendedor
         WHERE id_inmueble = ? AND id_vendedor = ? AND activo = TRUE
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtV, 'ii', $idInmueble, $idVendedor);
    mysqli_stmt_execute($stmtV);
    mysqli_stmt_store_result($stmtV);

    if (mysqli_stmt_num_rows($stmtV) === 0) {
        mysqli_stmt_close($stmtV);
        redireccionError(
            'El vendedor seleccionado no está asignado a esta propiedad.',
            (int) $idInmueble
        );
    }
    mysqli_stmt_close($stmtV);

} catch (mysqli_sql_exception $e) {
    redireccionError('No fue posible verificar el vendedor. Inténtalo nuevamente.', (int) $idInmueble);
}

// ─── Insertar la cita ─────────────────────────────────────────────────────────

try {
    $stmtC = mysqli_prepare($conexion, '
        INSERT INTO Cita
            (id_comprador, id_inmueble, id_vendedor, id_estado_cita,
             fecha_inicio, fecha_fin, comentario_solicitud)
        VALUES (?, ?, ?, 1, ?, ?, ?)
    ');
    mysqli_stmt_bind_param(
        $stmtC,
        'iiiiss',
        $idComprador,
        $idInmueble,
        $idVendedor,
        $fechaInicio,
        $fechaFin,
        $comentarioGuardar
    );
    mysqli_stmt_execute($stmtC);
    mysqli_stmt_close($stmtC);

    $_SESSION['mensaje_exito'] = 'Cita agendada correctamente. El vendedor te confirmará pronto.';
    header('Location: ../comprador/citas.php');
    exit;

} catch (mysqli_sql_exception $e) {
    // Clave duplicada: misma combinación comprador-inmueble-vendedor-fecha
    if ($e->getCode() === 1062) {
        redireccionError(
            'Ya tienes una cita para esa propiedad, ese vendedor y esa fecha y hora.',
            (int) $idInmueble
        );
    }
    redireccionError('No fue posible agendar la cita. Inténtalo nuevamente.', (int) $idInmueble);
}
