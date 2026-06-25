<?php

declare(strict_types=1);

require_once __DIR__ . '/../comprador/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../comprador/agendar.php');
    exit;
}

$idComprador = (int) ($_SESSION['id_perfil'] ?? 0);

if ($idComprador <= 0) {
    $_SESSION['error_agendar'] =
        'No fue posible identificar tu perfil de comprador.';
    header('Location: ../public/login.php');
    exit;
}

function redireccionError(
    string $mensaje,
    int $idInmueble = 0,
    int $idCita = 0
): never {
    $_SESSION['error_agendar'] = $mensaje;

    if ($idCita > 0) {
        $destino = '../comprador/agendar.php?id_cita=' . $idCita;
    } elseif ($idInmueble > 0) {
        $destino = '../comprador/agendar.php?id=' . $idInmueble;
    } else {
        $destino = '../comprador/agendar.php';
    }

    header('Location: ' . $destino);
    exit;
}

$idCitaRaw = $_POST['id_cita'] ?? '';
$idInmuebleRaw = $_POST['id_inmueble'] ?? '';
$idVendedorRaw = $_POST['id_vendedor'] ?? '';
$fechaRaw = trim($_POST['fecha'] ?? '');
$horaRaw = trim($_POST['hora'] ?? '');
$comentario = trim($_POST['comentario_solicitud'] ?? '');

$idCita = null;

if ($idCitaRaw !== '') {
    $idCita = filter_var(
        $idCitaRaw,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($idCita === false || $idCita === null) {
        redireccionError('La cita que intentas reprogramar no es válida.');
    }
}

$idInmueble = filter_var(
    $idInmuebleRaw,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$idVendedor = filter_var(
    $idVendedorRaw,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($idInmueble === false || $idInmueble === null) {
    redireccionError(
        'Debes seleccionar una propiedad válida.',
        0,
        (int) ($idCita ?? 0)
    );
}

if ($idVendedor === false || $idVendedor === null) {
    redireccionError(
        'Debes seleccionar un vendedor.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

if ($fechaRaw === '') {
    redireccionError(
        'La fecha es obligatoria.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

if ($horaRaw === '') {
    redireccionError(
        'La hora es obligatoria.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaRaw)
    || strtotime($fechaRaw) === false
) {
    redireccionError(
        'La fecha no tiene un formato válido.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

if (!preg_match('/^\d{2}:\d{2}$/', $horaRaw)) {
    redireccionError(
        'La hora no tiene un formato válido.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

$fechaInicio = $fechaRaw . ' ' . $horaRaw . ':00';
$timestampInicio = strtotime($fechaInicio);

if ($timestampInicio === false || $timestampInicio <= time()) {
    redireccionError(
        'La fecha y hora deben ser futuras.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

[$hora, $minuto] = explode(':', $horaRaw);
$horaDecimal = (int) $hora + ((int) $minuto / 60);

if ($horaDecimal < 9.0 || $horaDecimal > 17.0) {
    redireccionError(
        'El horario de atención es de 09:00 a 17:00.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

$fechaFin = date('Y-m-d H:i:s', $timestampInicio + 3600);
$comentarioGuardar = $comentario !== '' ? $comentario : null;

/*
 * Si se está reprogramando, la cita debe pertenecer al comprador y
 * encontrarse pendiente o aceptada. Además, no se permite cambiar
 * el inmueble ni el vendedor mediante manipulación del formulario.
 */
if ($idCita !== null) {
    try {
        $stmtCitaActual = mysqli_prepare(
            $conexion,
            "
            SELECT
                id_inmueble,
                id_vendedor,
                id_estado_cita
            FROM Cita
            WHERE id_cita = ?
              AND id_comprador = ?
            LIMIT 1
            "
        );

        mysqli_stmt_bind_param(
            $stmtCitaActual,
            'ii',
            $idCita,
            $idComprador
        );

        mysqli_stmt_execute($stmtCitaActual);
        mysqli_stmt_bind_result(
            $stmtCitaActual,
            $idInmuebleActual,
            $idVendedorActual,
            $idEstadoActual
        );

        $existe = mysqli_stmt_fetch($stmtCitaActual);
        mysqli_stmt_close($stmtCitaActual);

        if (!$existe) {
            redireccionError(
                'La cita no existe o no te pertenece.',
                0,
                $idCita
            );
        }

        if (!in_array((int) $idEstadoActual, [1, 2], true)) {
            redireccionError(
                'Solo puedes reprogramar citas pendientes o aceptadas.',
                0,
                $idCita
            );
        }

        $idInmueble = (int) $idInmuebleActual;
        $idVendedor = (int) $idVendedorActual;
    } catch (mysqli_sql_exception $error) {
        redireccionError(
            'No fue posible verificar la cita.',
            0,
            $idCita
        );
    }
}

try {
    $stmtInmueble = mysqli_prepare(
        $conexion,
        "
        SELECT id_inmueble
        FROM Inmueble
        WHERE id_inmueble = ?
          AND id_estado_publicacion = 3
        LIMIT 1
        "
    );

    mysqli_stmt_bind_param($stmtInmueble, 'i', $idInmueble);
    mysqli_stmt_execute($stmtInmueble);
    mysqli_stmt_store_result($stmtInmueble);

    if (mysqli_stmt_num_rows($stmtInmueble) === 0) {
        mysqli_stmt_close($stmtInmueble);
        redireccionError(
            'La propiedad seleccionada no está disponible.',
            (int) $idInmueble,
            (int) ($idCita ?? 0)
        );
    }

    mysqli_stmt_close($stmtInmueble);
} catch (mysqli_sql_exception $error) {
    redireccionError(
        'No fue posible verificar la propiedad.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

try {
    $stmtVendedor = mysqli_prepare(
        $conexion,
        "
        SELECT 1
        FROM InmuebleVendedor AS iv
        INNER JOIN Vendedor AS v
            ON v.id_vendedor = iv.id_vendedor
        INNER JOIN Usuario AS u
            ON u.id_usuario = v.id_usuario
        INNER JOIN EstadoCuenta AS ec
            ON ec.id_estado_cuenta = u.id_estado_cuenta
        WHERE iv.id_inmueble = ?
          AND iv.id_vendedor = ?
          AND iv.activo = TRUE
          AND LOWER(ec.nombre_estado) = 'activa'
        LIMIT 1
        "
    );

    mysqli_stmt_bind_param(
        $stmtVendedor,
        'ii',
        $idInmueble,
        $idVendedor
    );

    mysqli_stmt_execute($stmtVendedor);
    mysqli_stmt_store_result($stmtVendedor);

    if (mysqli_stmt_num_rows($stmtVendedor) === 0) {
        mysqli_stmt_close($stmtVendedor);
        redireccionError(
            'El vendedor ya no está disponible para esta propiedad.',
            (int) $idInmueble,
            (int) ($idCita ?? 0)
        );
    }

    mysqli_stmt_close($stmtVendedor);
} catch (mysqli_sql_exception $error) {
    redireccionError(
        'No fue posible verificar el vendedor.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

/*
 * Evitar que el comprador o el vendedor tengan otra cita activa que
 * se traslape con el horario solicitado. Al reprogramar se excluye
 * la propia cita.
 */
try {
    $idCitaExcluir = $idCita ?? 0;

    $stmtConflicto = mysqli_prepare(
        $conexion,
        "
        SELECT id_cita
        FROM Cita
        WHERE id_cita <> ?
          AND id_estado_cita IN (1, 2)
          AND (id_comprador = ? OR id_vendedor = ?)
          AND fecha_inicio < ?
          AND fecha_fin > ?
        LIMIT 1
        "
    );

    mysqli_stmt_bind_param(
        $stmtConflicto,
        'iiiss',
        $idCitaExcluir,
        $idComprador,
        $idVendedor,
        $fechaFin,
        $fechaInicio
    );

    mysqli_stmt_execute($stmtConflicto);
    mysqli_stmt_store_result($stmtConflicto);
    $hayConflicto = mysqli_stmt_num_rows($stmtConflicto) > 0;
    mysqli_stmt_close($stmtConflicto);

    if ($hayConflicto) {
        redireccionError(
            'El comprador o el vendedor ya tiene una cita en ese horario.',
            (int) $idInmueble,
            (int) ($idCita ?? 0)
        );
    }
} catch (mysqli_sql_exception $error) {
    redireccionError(
        'No fue posible verificar la disponibilidad del horario.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}

try {
    if ($idCita !== null) {
        /*
         * Reprogramar actualiza la misma fila y vuelve la cita a
         * pendiente para que el vendedor confirme el nuevo horario.
         */
        $estadoPendiente = 1;

        $stmtActualizar = mysqli_prepare(
            $conexion,
            "
            UPDATE Cita
            SET fecha_inicio = ?,
                fecha_fin = ?,
                comentario_solicitud = ?,
                id_estado_cita = ?
            WHERE id_cita = ?
              AND id_comprador = ?
            "
        );

        mysqli_stmt_bind_param(
            $stmtActualizar,
            'sssiii',
            $fechaInicio,
            $fechaFin,
            $comentarioGuardar,
            $estadoPendiente,
            $idCita,
            $idComprador
        );

        mysqli_stmt_execute($stmtActualizar);
        mysqli_stmt_close($stmtActualizar);

        $_SESSION['mensaje_exito'] =
            'La cita fue reprogramada correctamente y quedó pendiente de confirmación.';
    } else {
        $estadoPendiente = 1;

        $stmtInsertar = mysqli_prepare(
            $conexion,
            "
            INSERT INTO Cita (
                id_comprador,
                id_inmueble,
                id_vendedor,
                id_estado_cita,
                fecha_inicio,
                fecha_fin,
                comentario_solicitud
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
            "
        );

        mysqli_stmt_bind_param(
            $stmtInsertar,
            'iiiisss',
            $idComprador,
            $idInmueble,
            $idVendedor,
            $estadoPendiente,
            $fechaInicio,
            $fechaFin,
            $comentarioGuardar
        );

        mysqli_stmt_execute($stmtInsertar);
        mysqli_stmt_close($stmtInsertar);

        $_SESSION['mensaje_exito'] =
            'Cita agendada correctamente. El vendedor te confirmará pronto.';
    }

    header('Location: ../comprador/citas.php');
    exit;
} catch (mysqli_sql_exception $error) {
    if ($error->getCode() === 1062) {
        redireccionError(
            'Ya existe una cita con esos datos.',
            (int) $idInmueble,
            (int) ($idCita ?? 0)
        );
    }

    redireccionError(
        $idCita !== null
            ? 'No fue posible reprogramar la cita.'
            : 'No fue posible agendar la cita.',
        (int) $idInmueble,
        (int) ($idCita ?? 0)
    );
}
