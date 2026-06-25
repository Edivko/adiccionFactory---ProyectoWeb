<?php

declare(strict_types=1);

require_once __DIR__ . '/../admin/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/categorias.php');
    exit;
}

// ─── Leer y validar campos comunes ───────────────────────────────────────────

$tabla  = trim($_POST['tabla']  ?? '');
$accion = trim($_POST['accion'] ?? '');

$tablasPermitidas  = ['categoria', 'servicio', 'amenidad'];
$accionesPermitidas = ['crear', 'toggle', 'eliminar'];

if (!in_array($tabla, $tablasPermitidas, true) || !in_array($accion, $accionesPermitidas, true)) {
    $_SESSION['error_general'] = 'Solicitud no válida.';
    header('Location: ../admin/categorias.php');
    exit;
}

// Mapa cerrado: tabla → [tabla SQL, columna PK, columna nombre, tabla relación, columna FK relación]
$meta = [
    'categoria' => [
        'tabla_sql'   => 'CategoriaInmueble',
        'col_pk'      => 'id_categoria',
        'col_nombre'  => 'nombre_categoria',
        'tabla_rel'   => 'Inmueble',
        'col_rel_fk'  => 'id_categoria',
        'tiene_desc'  => true,
    ],
    'servicio'  => [
        'tabla_sql'   => 'Servicio',
        'col_pk'      => 'id_servicio',
        'col_nombre'  => 'nombre_servicio',
        'tabla_rel'   => 'InmuebleServicio',
        'col_rel_fk'  => 'id_servicio',
        'tiene_desc'  => false,
    ],
    'amenidad'  => [
        'tabla_sql'   => 'Amenidad',
        'col_pk'      => 'id_amenidad',
        'col_nombre'  => 'nombre_amenidad',
        'tabla_rel'   => 'InmuebleAmenidad',
        'col_rel_fk'  => 'id_amenidad',
        'tiene_desc'  => false,
    ],
];

$m = $meta[$tabla];

// =============================================================================
// ACCIÓN: CREAR
// =============================================================================

if ($accion === 'crear') {

    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre === '') {
        $_SESSION['error_general'] = 'El nombre no puede estar vacío.';
        header('Location: ../admin/categorias.php');
        exit;
    }

    $nombre = mb_substr($nombre, 0, 100);

    try {
        if ($m['tiene_desc']) {
            $descripcion = trim($_POST['descripcion'] ?? '');
            $descGuardar = $descripcion !== '' ? mb_substr($descripcion, 0, 255) : null;

            $stmtIns = mysqli_prepare($conexion,
                "INSERT INTO {$m['tabla_sql']} ({$m['col_nombre']}, descripcion, activo)
                 VALUES (?, ?, TRUE)"
            );
            mysqli_stmt_bind_param($stmtIns, 'ss', $nombre, $descGuardar);
        } else {
            $stmtIns = mysqli_prepare($conexion,
                "INSERT INTO {$m['tabla_sql']} ({$m['col_nombre']}, activo)
                 VALUES (?, TRUE)"
            );
            mysqli_stmt_bind_param($stmtIns, 's', $nombre);
        }

        mysqli_stmt_execute($stmtIns);
        mysqli_stmt_close($stmtIns);

        $_SESSION['mensaje_exito'] = "Registro «{$nombre}» creado correctamente.";

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $_SESSION['error_general'] = "Ya existe un registro con el nombre «{$nombre}».";
        } else {
            $_SESSION['error_general'] = 'No fue posible crear el registro.';
        }
    }

    header('Location: ../admin/categorias.php');
    exit;
}

// =============================================================================
// ACCIÓN: TOGGLE ACTIVO
// =============================================================================

if ($accion === 'toggle') {

    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$id) {
        $_SESSION['error_general'] = 'Registro no válido.';
        header('Location: ../admin/categorias.php');
        exit;
    }

    // Leer estado actual
    $activoActual = null;

    try {
        $stmtSel = mysqli_prepare($conexion,
            "SELECT activo FROM {$m['tabla_sql']} WHERE {$m['col_pk']} = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmtSel, 'i', $id);
        mysqli_stmt_execute($stmtSel);
        mysqli_stmt_bind_result($stmtSel, $activoActual);
        $existe = mysqli_stmt_fetch($stmtSel);
        mysqli_stmt_close($stmtSel);
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_general'] = 'No fue posible verificar el registro.';
        header('Location: ../admin/categorias.php');
        exit;
    }

    if (!$existe) {
        $_SESSION['error_general'] = 'El registro no existe.';
        header('Location: ../admin/categorias.php');
        exit;
    }

    $nuevoActivo = $activoActual ? 0 : 1;

    try {
        $stmtUpd = mysqli_prepare($conexion,
            "UPDATE {$m['tabla_sql']} SET activo = ? WHERE {$m['col_pk']} = ?"
        );
        mysqli_stmt_bind_param($stmtUpd, 'ii', $nuevoActivo, $id);
        mysqli_stmt_execute($stmtUpd);
        mysqli_stmt_close($stmtUpd);

        $_SESSION['mensaje_exito'] = $nuevoActivo
            ? 'Registro activado.'
            : 'Registro desactivado.';

    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_general'] = 'No fue posible actualizar el registro.';
    }

    header('Location: ../admin/categorias.php');
    exit;
}

// =============================================================================
// ACCIÓN: ELIMINAR
// =============================================================================

if ($accion === 'eliminar') {

    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$id) {
        $_SESSION['error_general'] = 'Registro no válido.';
        header('Location: ../admin/categorias.php');
        exit;
    }

    // Verificar que no tiene registros relacionados
    try {
        $stmtRel = mysqli_prepare($conexion,
            "SELECT COUNT(*) FROM {$m['tabla_rel']} WHERE {$m['col_rel_fk']} = ?"
        );
        mysqli_stmt_bind_param($stmtRel, 'i', $id);
        mysqli_stmt_execute($stmtRel);
        mysqli_stmt_bind_result($stmtRel, $totalRel);
        mysqli_stmt_fetch($stmtRel);
        mysqli_stmt_close($stmtRel);
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_general'] = 'No fue posible verificar las relaciones del registro.';
        header('Location: ../admin/categorias.php');
        exit;
    }

    if ($totalRel > 0) {
        $_SESSION['error_general'] =
            "No se puede eliminar: tiene {$totalRel} registro(s) relacionado(s). " .
            'Usa "Desactivar" para ocultarlo sin eliminar datos históricos.';
        header('Location: ../admin/categorias.php');
        exit;
    }

    try {
        $stmtDel = mysqli_prepare($conexion,
            "DELETE FROM {$m['tabla_sql']} WHERE {$m['col_pk']} = ?"
        );
        mysqli_stmt_bind_param($stmtDel, 'i', $id);
        mysqli_stmt_execute($stmtDel);
        mysqli_stmt_close($stmtDel);

        $_SESSION['mensaje_exito'] = 'Registro eliminado.';

    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_general'] = 'No fue posible eliminar el registro.';
    }

    header('Location: ../admin/categorias.php');
    exit;
}
