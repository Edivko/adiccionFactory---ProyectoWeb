<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendedor/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vendedor/mis-inmuebles.php');
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];

// ─── Detectar acción ──────────────────────────────────────────────────────────

$accion = trim($_POST['accion'] ?? 'guardar');

// ─── Acción: cambio de estado ─────────────────────────────────────────────────

if ($accion === 'estado') {

    $idInmueble  = filter_var($_POST['id_inmueble']  ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $nuevoEstado = filter_var($_POST['nuevo_estado'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($idInmueble === false || $idInmueble === null || $nuevoEstado === false || $nuevoEstado === null) {
        $_SESSION['error_general'] = 'Solicitud no válida.';
        header('Location: ../vendedor/mis-inmuebles.php');
        exit;
    }

    // Solo estados permitidos para vendedor: pendiente(2), pausado(5)
    if (!in_array((int) $nuevoEstado, [2, 5], true)) {
        $_SESSION['error_general'] = 'Cambio de estado no permitido.';
        header('Location: ../vendedor/mis-inmuebles.php');
        exit;
    }

    // Verificar propiedad
    try {
        $stmtV = mysqli_prepare($conexion,
            'SELECT id_estado_publicacion FROM Inmueble
             WHERE id_inmueble = ? AND id_usuario_publicador = ? LIMIT 1'
        );
        mysqli_stmt_bind_param($stmtV, 'ii', $idInmueble, $idUsuario);
        mysqli_stmt_execute($stmtV);
        mysqli_stmt_bind_result($stmtV, $estadoActual);
        $existe = mysqli_stmt_fetch($stmtV);
        mysqli_stmt_close($stmtV);

        if (!$existe) {
            $_SESSION['error_general'] = 'El inmueble no existe o no tienes permiso.';
            header('Location: ../vendedor/mis-inmuebles.php');
            exit;
        }

        // Validar transiciones permitidas al vendedor
        $transicionesOk = [
            1 => [2],   // borrador → pendiente
            3 => [5],   // publicado → pausado
            5 => [2],   // pausado → pendiente
        ];
        if (!in_array((int) $nuevoEstado, $transicionesOk[(int) $estadoActual] ?? [], true)) {
            $_SESSION['error_general'] = 'No puedes realizar esa transición de estado.';
            header('Location: ../vendedor/mis-inmuebles.php');
            exit;
        }

        $stmtU = mysqli_prepare($conexion,
            'UPDATE Inmueble SET id_estado_publicacion = ?
             WHERE id_inmueble = ? AND id_usuario_publicador = ?'
        );
        mysqli_stmt_bind_param($stmtU, 'iii', $nuevoEstado, $idInmueble, $idUsuario);
        mysqli_stmt_execute($stmtU);
        mysqli_stmt_close($stmtU);

        $_SESSION['mensaje_exito'] = 'Estado del inmueble actualizado.';
        header('Location: ../vendedor/mis-inmuebles.php');
        exit;

    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_general'] = 'No fue posible actualizar el estado.';
        header('Location: ../vendedor/mis-inmuebles.php');
        exit;
    }
}

// ─── Acción: guardar (crear o editar) ────────────────────────────────────────

$idInmueble = filter_var($_POST['id_inmueble'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$esEdicion  = ($idInmueble !== false && $idInmueble !== null);

// Leer campos
$titulo            = trim($_POST['titulo']             ?? '');
$idCategoriaRaw    = trim($_POST['id_categoria']       ?? '');
$idCondicionRaw    = trim($_POST['id_condicion']       ?? '');
$descripcion       = trim($_POST['descripcion']        ?? '');
$precioRaw         = trim($_POST['precio']             ?? '');
$moneda            = trim($_POST['moneda']             ?? 'MXN');
$estado            = trim($_POST['estado']             ?? '');
$ciudad            = trim($_POST['ciudad']             ?? '');
$colonia           = trim($_POST['colonia']            ?? '');
$direccion         = trim($_POST['direccion']          ?? '');
$codigoPostal      = trim($_POST['codigo_postal']      ?? '');
$recamarasRaw      = trim($_POST['recamaras']          ?? '');
$banosRaw          = trim($_POST['banos']              ?? '');
$estacionamRaw     = trim($_POST['estacionamientos']   ?? '');
$metrosTerrenoRaw  = trim($_POST['metros_terreno']     ?? '');
$metrosConstrucRaw = trim($_POST['metros_construccion'] ?? '');
$antiguedadRaw     = trim($_POST['antiguedad']         ?? '');

$datosPrev = [
    'titulo'             => $titulo,
    'id_categoria'       => $idCategoriaRaw,
    'id_condicion'       => $idCondicionRaw,
    'descripcion'        => $descripcion,
    'precio'             => $precioRaw,
    'moneda'             => $moneda,
    'estado'             => $estado,
    'ciudad'             => $ciudad,
    'colonia'            => $colonia,
    'direccion'          => $direccion,
    'codigo_postal'      => $codigoPostal,
    'recamaras'          => $recamarasRaw,
    'banos'              => $banosRaw,
    'estacionamientos'   => $estacionamRaw,
    'metros_terreno'     => $metrosTerrenoRaw,
    'metros_construccion'=> $metrosConstrucRaw,
    'antiguedad'         => $antiguedadRaw,
];

// ─── Validaciones ─────────────────────────────────────────────────────────────

$errores = [];

if ($titulo === '') {
    $errores['titulo'] = 'El título es obligatorio.';
} elseif (mb_strlen($titulo) > 150) {
    $errores['titulo'] = 'El título no puede superar 150 caracteres.';
}

$idCategoria = filter_var($idCategoriaRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($idCategoria === false || $idCategoria === null) {
    $errores['id_categoria'] = 'Debes seleccionar una categoría.';
}

// Opcionales: convertir a null si vacío
$idCondicion    = $idCondicionRaw !== '' ? filter_var($idCondicionRaw, FILTER_VALIDATE_INT) : null;
$precio         = $precioRaw !== '' ? filter_var($precioRaw, FILTER_VALIDATE_FLOAT) : null;
$monedaGuardar  = in_array($moneda, ['MXN', 'USD'], true) ? $moneda : 'MXN';

$estadoGuardar       = $estado !== '' ? $estado : null;
$ciudadGuardar       = $ciudad !== '' ? $ciudad : null;
$coloniaGuardar      = $colonia !== '' ? $colonia : null;
$direccionGuardar    = $direccion !== '' ? $direccion : null;
$cpGuardar           = $codigoPostal !== '' ? $codigoPostal : null;
$descripcionGuardar  = $descripcion !== '' ? $descripcion : null;

$recamaras      = $recamarasRaw !== ''      ? filter_var($recamarasRaw, FILTER_VALIDATE_INT)     : null;
$banos          = $banosRaw !== ''          ? filter_var($banosRaw, FILTER_VALIDATE_FLOAT)        : null;
$estacionam     = $estacionamRaw !== ''     ? filter_var($estacionamRaw, FILTER_VALIDATE_INT)     : null;
$metrosTerr     = $metrosTerrenoRaw !== ''  ? filter_var($metrosTerrenoRaw, FILTER_VALIDATE_FLOAT): null;
$metrosCons     = $metrosConstrucRaw !== '' ? filter_var($metrosConstrucRaw, FILTER_VALIDATE_FLOAT): null;
$antiguedad     = $antiguedadRaw !== ''     ? filter_var($antiguedadRaw, FILTER_VALIDATE_INT)     : null;

if (!empty($errores)) {
    $_SESSION['errores_inmueble'] = $errores;
    $_SESSION['datos_inmueble']   = $datosPrev;
    $dest = $esEdicion
        ? '../vendedor/editar-inmueble.php?id=' . (int) $idInmueble
        : '../vendedor/agregar-inmueble.php';
    header('Location: ' . $dest);
    exit;
}

// ─── Verificar que la categoría existe ───────────────────────────────────────

try {
    $stmtCat = mysqli_prepare($conexion,
        'SELECT id_categoria FROM CategoriaInmueble WHERE id_categoria = ? AND activo = TRUE LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtCat, 'i', $idCategoria);
    mysqli_stmt_execute($stmtCat);
    mysqli_stmt_store_result($stmtCat);

    if (mysqli_stmt_num_rows($stmtCat) === 0) {
        mysqli_stmt_close($stmtCat);
        $_SESSION['errores_inmueble'] = ['id_categoria' => 'Categoría no válida.'];
        $_SESSION['datos_inmueble']   = $datosPrev;
        $dest = $esEdicion
            ? '../vendedor/editar-inmueble.php?id=' . (int) $idInmueble
            : '../vendedor/agregar-inmueble.php';
        header('Location: ' . $dest);
        exit;
    }
    mysqli_stmt_close($stmtCat);
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible procesar el inmueble.';
    header('Location: ../vendedor/mis-inmuebles.php');
    exit;
}

// ─── Insertar o actualizar ────────────────────────────────────────────────────

try {
    if ($esEdicion) {

        // Verificar que el inmueble pertenece al vendedor autenticado
        $stmtOwn = mysqli_prepare($conexion,
            'SELECT id_inmueble FROM Inmueble
             WHERE id_inmueble = ? AND id_usuario_publicador = ? LIMIT 1'
        );
        mysqli_stmt_bind_param($stmtOwn, 'ii', $idInmueble, $idUsuario);
        mysqli_stmt_execute($stmtOwn);
        mysqli_stmt_store_result($stmtOwn);

        if (mysqli_stmt_num_rows($stmtOwn) === 0) {
            mysqli_stmt_close($stmtOwn);
            $_SESSION['error_general'] = 'No tienes permiso para editar este inmueble.';
            header('Location: ../vendedor/mis-inmuebles.php');
            exit;
        }
        mysqli_stmt_close($stmtOwn);

        $stmtSave = mysqli_prepare($conexion, '
            UPDATE Inmueble SET
                titulo             = ?,
                id_categoria       = ?,
                id_condicion       = ?,
                descripcion        = ?,
                precio             = ?,
                moneda             = ?,
                estado             = ?,
                ciudad             = ?,
                colonia            = ?,
                direccion          = ?,
                codigo_postal      = ?,
                recamaras          = ?,
                banos              = ?,
                estacionamientos   = ?,
                metros_terreno     = ?,
                metros_construccion= ?,
                antiguedad         = ?
            WHERE id_inmueble = ? AND id_usuario_publicador = ?
        ');
        mysqli_stmt_bind_param(
            $stmtSave,
            'siisdssssssiidddiii',
            $titulo,
            $idCategoria,
            $idCondicion,
            $descripcionGuardar,
            $precio,
            $monedaGuardar,
            $estadoGuardar,
            $ciudadGuardar,
            $coloniaGuardar,
            $direccionGuardar,
            $cpGuardar,
            $recamaras,
            $banos,
            $estacionam,
            $metrosTerr,
            $metrosCons,
            $antiguedad,
            $idInmueble,
            $idUsuario
        );
        mysqli_stmt_execute($stmtSave);
        mysqli_stmt_close($stmtSave);

        $_SESSION['mensaje_exito'] = 'Inmueble actualizado correctamente.';
        header('Location: ../vendedor/editar-inmueble.php?id=' . (int) $idInmueble);
        exit;

    } else {

        // Nuevo inmueble: estado = borrador (1)
        $idEstadoBorrador = 1;

        $stmtSave = mysqli_prepare($conexion, '
            INSERT INTO Inmueble (
                id_usuario_publicador, id_categoria, id_condicion, id_estado_publicacion,
                titulo, descripcion, precio, moneda,
                estado, ciudad, colonia, direccion, codigo_postal,
                recamaras, banos, estacionamientos,
                metros_terreno, metros_construccion, antiguedad
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?
            )
        ');
        mysqli_stmt_bind_param(
            $stmtSave,
            'iiiissdssssssiiiddd',
            $idUsuario,
            $idCategoria,
            $idCondicion,
            $idEstadoBorrador,
            $titulo,
            $descripcionGuardar,
            $precio,
            $monedaGuardar,
            $estadoGuardar,
            $ciudadGuardar,
            $coloniaGuardar,
            $direccionGuardar,
            $cpGuardar,
            $recamaras,
            $banos,
            $estacionam,
            $metrosTerr,
            $metrosCons,
            $antiguedad
        );
        mysqli_stmt_execute($stmtSave);
        $nuevoId = (int) mysqli_insert_id($conexion);
        mysqli_stmt_close($stmtSave);

        $_SESSION['mensaje_exito'] = 'Inmueble creado en borrador. Ahora puedes subir fotos y enviarlo a revisión cuando esté listo.';
        header('Location: ../vendedor/subir-fotos.php?id=' . $nuevoId);
        exit;
    }

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible guardar el inmueble. Inténtalo nuevamente.';
    $dest = $esEdicion
        ? '../vendedor/editar-inmueble.php?id=' . (int) $idInmueble
        : '../vendedor/agregar-inmueble.php';
    header('Location: ' . $dest);
    exit;
}
