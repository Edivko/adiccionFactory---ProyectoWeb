<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendedor/includes/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vendedor/mis-inmuebles.php');
    exit;
}

$idUsuario = (int) ($_SESSION['id_usuario'] ?? 0);
$idVendedor = (int) ($_SESSION['id_perfil'] ?? 0);

if ($idUsuario <= 0 || $idVendedor <= 0) {
    $_SESSION['error_general'] =
        'No fue posible identificar el perfil del vendedor.';

    header('Location: ../public/login.php');
    exit;
}

// ─── Funciones auxiliares ────────────────────────────────────────────────────

function redirigir(string $ruta): void
{
    header('Location: ' . $ruta);
    exit;
}

function asignarInmuebleAVendedor(
    mysqli $conexion,
    int $idInmueble,
    int $idVendedor
): void {
    /*
     * Evita crear relaciones duplicadas.
     * Si ya existe la relación, únicamente la reactiva.
     */
    $stmtBuscar = mysqli_prepare(
        $conexion,
        'SELECT activo
         FROM InmuebleVendedor
         WHERE id_inmueble = ?
           AND id_vendedor = ?
         LIMIT 1'
    );

    mysqli_stmt_bind_param(
        $stmtBuscar,
        'ii',
        $idInmueble,
        $idVendedor
    );

    mysqli_stmt_execute($stmtBuscar);
    mysqli_stmt_store_result($stmtBuscar);

    $relacionExiste = mysqli_stmt_num_rows($stmtBuscar) > 0;

    mysqli_stmt_close($stmtBuscar);

    if ($relacionExiste) {
        $activo = 1;

        $stmtActualizar = mysqli_prepare(
            $conexion,
            'UPDATE InmuebleVendedor
             SET activo = ?
             WHERE id_inmueble = ?
               AND id_vendedor = ?'
        );

        mysqli_stmt_bind_param(
            $stmtActualizar,
            'iii',
            $activo,
            $idInmueble,
            $idVendedor
        );

        mysqli_stmt_execute($stmtActualizar);
        mysqli_stmt_close($stmtActualizar);

        return;
    }

    $activo = 1;

    $stmtRelacion = mysqli_prepare(
        $conexion,
        'INSERT INTO InmuebleVendedor (
            id_inmueble,
            id_vendedor,
            activo
        ) VALUES (?, ?, ?)'
    );

    mysqli_stmt_bind_param(
        $stmtRelacion,
        'iii',
        $idInmueble,
        $idVendedor,
        $activo
    );

    mysqli_stmt_execute($stmtRelacion);
    mysqli_stmt_close($stmtRelacion);
}

// ─── Detectar acción ─────────────────────────────────────────────────────────

$accion = trim($_POST['accion'] ?? 'guardar');

// ─── Acción: cambio de estado ────────────────────────────────────────────────

if ($accion === 'estado') {
    $idInmueble = filter_var(
        $_POST['id_inmueble'] ?? '',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $nuevoEstado = filter_var(
        $_POST['nuevo_estado'] ?? '',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (
        $idInmueble === false ||
        $idInmueble === null ||
        $nuevoEstado === false ||
        $nuevoEstado === null
    ) {
        $_SESSION['error_general'] = 'Solicitud no válida.';
        redirigir('../vendedor/mis-inmuebles.php');
    }

    /*
     * Estados permitidos para el vendedor:
     * 2 = pendiente
     * 5 = pausado
     */
    if (!in_array((int) $nuevoEstado, [2, 5], true)) {
        $_SESSION['error_general'] = 'Cambio de estado no permitido.';
        redirigir('../vendedor/mis-inmuebles.php');
    }

    try {
        $stmtVerificar = mysqli_prepare(
            $conexion,
            'SELECT id_estado_publicacion
             FROM Inmueble
             WHERE id_inmueble = ?
               AND id_usuario_publicador = ?
             LIMIT 1'
        );

        mysqli_stmt_bind_param(
            $stmtVerificar,
            'ii',
            $idInmueble,
            $idUsuario
        );

        mysqli_stmt_execute($stmtVerificar);

        mysqli_stmt_bind_result(
            $stmtVerificar,
            $estadoActual
        );

        $existe = mysqli_stmt_fetch($stmtVerificar);

        mysqli_stmt_close($stmtVerificar);

        if (!$existe) {
            $_SESSION['error_general'] =
                'El inmueble no existe o no tienes permiso.';

            redirigir('../vendedor/mis-inmuebles.php');
        }

        $transicionesPermitidas = [
            1 => [2], // borrador → pendiente
            3 => [5], // publicado → pausado
            5 => [2], // pausado → pendiente
        ];

        if (
            !in_array(
                (int) $nuevoEstado,
                $transicionesPermitidas[(int) $estadoActual] ?? [],
                true
            )
        ) {
            $_SESSION['error_general'] =
                'No puedes realizar esa transición de estado.';

            redirigir('../vendedor/mis-inmuebles.php');
        }

        mysqli_begin_transaction($conexion);

        try {
            /*
             * Repara también inmuebles antiguos que no tenían relación
             * en InmuebleVendedor.
             */
            asignarInmuebleAVendedor(
                $conexion,
                (int) $idInmueble,
                $idVendedor
            );

            $stmtActualizar = mysqli_prepare(
                $conexion,
                'UPDATE Inmueble
                 SET id_estado_publicacion = ?
                 WHERE id_inmueble = ?
                   AND id_usuario_publicador = ?'
            );

            mysqli_stmt_bind_param(
                $stmtActualizar,
                'iii',
                $nuevoEstado,
                $idInmueble,
                $idUsuario
            );

            mysqli_stmt_execute($stmtActualizar);
            mysqli_stmt_close($stmtActualizar);

            mysqli_commit($conexion);

            $_SESSION['mensaje_exito'] =
                'Estado del inmueble actualizado.';

            redirigir('../vendedor/mis-inmuebles.php');
        } catch (mysqli_sql_exception $error) {
            mysqli_rollback($conexion);
            throw $error;
        }
    } catch (mysqli_sql_exception $error) {
        $_SESSION['error_general'] =
            'No fue posible actualizar el estado.';

        redirigir('../vendedor/mis-inmuebles.php');
    }
}

// ─── Acción: guardar, crear o editar ─────────────────────────────────────────

$idInmueble = filter_var(
    $_POST['id_inmueble'] ?? '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$esEdicion = (
    $idInmueble !== false &&
    $idInmueble !== null
);

// ─── Leer campos ─────────────────────────────────────────────────────────────

$titulo = trim($_POST['titulo'] ?? '');
$idCategoriaRaw = trim($_POST['id_categoria'] ?? '');
$idCondicionRaw = trim($_POST['id_condicion'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precioRaw = trim($_POST['precio'] ?? '');
$moneda = trim($_POST['moneda'] ?? 'MXN');
$estado = trim($_POST['estado'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$colonia = trim($_POST['colonia'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$codigoPostal = trim($_POST['codigo_postal'] ?? '');
$recamarasRaw = trim($_POST['recamaras'] ?? '');
$banosRaw = trim($_POST['banos'] ?? '');
$estacionamientosRaw = trim(
    $_POST['estacionamientos'] ?? ''
);
$metrosTerrenoRaw = trim(
    $_POST['metros_terreno'] ?? ''
);
$metrosConstruccionRaw = trim(
    $_POST['metros_construccion'] ?? ''
);
$antiguedadRaw = trim(
    $_POST['antiguedad'] ?? ''
);

$datosPrevios = [
    'titulo' => $titulo,
    'id_categoria' => $idCategoriaRaw,
    'id_condicion' => $idCondicionRaw,
    'descripcion' => $descripcion,
    'precio' => $precioRaw,
    'moneda' => $moneda,
    'estado' => $estado,
    'ciudad' => $ciudad,
    'colonia' => $colonia,
    'direccion' => $direccion,
    'codigo_postal' => $codigoPostal,
    'recamaras' => $recamarasRaw,
    'banos' => $banosRaw,
    'estacionamientos' => $estacionamientosRaw,
    'metros_terreno' => $metrosTerrenoRaw,
    'metros_construccion' => $metrosConstruccionRaw,
    'antiguedad' => $antiguedadRaw,
];

// ─── Validaciones ────────────────────────────────────────────────────────────

$errores = [];

if ($titulo === '') {
    $errores['titulo'] = 'El título es obligatorio.';
} elseif (mb_strlen($titulo) > 150) {
    $errores['titulo'] =
        'El título no puede superar 150 caracteres.';
}

$idCategoria = filter_var(
    $idCategoriaRaw,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($idCategoria === false || $idCategoria === null) {
    $errores['id_categoria'] =
        'Debes seleccionar una categoría.';
}

$idCondicion = null;

if ($idCondicionRaw !== '') {
    $idCondicion = filter_var(
        $idCondicionRaw,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($idCondicion === false) {
        $errores['id_condicion'] =
            'La condición seleccionada no es válida.';
    }
}

$precio = null;

if ($precioRaw !== '') {
    $precio = filter_var(
        $precioRaw,
        FILTER_VALIDATE_FLOAT
    );

    if ($precio === false || $precio < 0) {
        $errores['precio'] =
            'El precio debe ser un número no negativo.';
    }
}

$monedaGuardar = in_array(
    $moneda,
    ['MXN', 'USD'],
    true
) ? $moneda : 'MXN';

$estadoGuardar = $estado !== '' ? $estado : null;
$ciudadGuardar = $ciudad !== '' ? $ciudad : null;
$coloniaGuardar = $colonia !== '' ? $colonia : null;
$direccionGuardar = $direccion !== ''
    ? $direccion
    : null;
$codigoPostalGuardar = $codigoPostal !== ''
    ? $codigoPostal
    : null;
$descripcionGuardar = $descripcion !== ''
    ? $descripcion
    : null;

$recamaras = null;

if ($recamarasRaw !== '') {
    $recamaras = filter_var(
        $recamarasRaw,
        FILTER_VALIDATE_INT
    );

    if ($recamaras === false || $recamaras < 0) {
        $errores['recamaras'] =
            'El número de recámaras no es válido.';
    }
}

$banos = null;

if ($banosRaw !== '') {
    $banos = filter_var(
        $banosRaw,
        FILTER_VALIDATE_FLOAT
    );

    if ($banos === false || $banos < 0) {
        $errores['banos'] =
            'El número de baños no es válido.';
    }
}

$estacionamientos = null;

if ($estacionamientosRaw !== '') {
    $estacionamientos = filter_var(
        $estacionamientosRaw,
        FILTER_VALIDATE_INT
    );

    if (
        $estacionamientos === false ||
        $estacionamientos < 0
    ) {
        $errores['estacionamientos'] =
            'El número de estacionamientos no es válido.';
    }
}

$metrosTerreno = null;

if ($metrosTerrenoRaw !== '') {
    $metrosTerreno = filter_var(
        $metrosTerrenoRaw,
        FILTER_VALIDATE_FLOAT
    );

    if (
        $metrosTerreno === false ||
        $metrosTerreno < 0
    ) {
        $errores['metros_terreno'] =
            'Los metros de terreno no son válidos.';
    }
}

$metrosConstruccion = null;

if ($metrosConstruccionRaw !== '') {
    $metrosConstruccion = filter_var(
        $metrosConstruccionRaw,
        FILTER_VALIDATE_FLOAT
    );

    if (
        $metrosConstruccion === false ||
        $metrosConstruccion < 0
    ) {
        $errores['metros_construccion'] =
            'Los metros de construcción no son válidos.';
    }
}

$antiguedad = null;

if ($antiguedadRaw !== '') {
    $antiguedad = filter_var(
        $antiguedadRaw,
        FILTER_VALIDATE_INT
    );

    if ($antiguedad === false || $antiguedad < 0) {
        $errores['antiguedad'] =
            'La antigüedad no es válida.';
    }
}

if (!empty($errores)) {
    $_SESSION['errores_inmueble'] = $errores;
    $_SESSION['datos_inmueble'] = $datosPrevios;

    $destino = $esEdicion
        ? '../vendedor/editar-inmueble.php?id='
            . (int) $idInmueble
        : '../vendedor/agregar-inmueble.php';

    redirigir($destino);
}

// ─── Verificar categoría ─────────────────────────────────────────────────────

try {
    $stmtCategoria = mysqli_prepare(
        $conexion,
        'SELECT id_categoria
         FROM CategoriaInmueble
         WHERE id_categoria = ?
           AND activo = TRUE
         LIMIT 1'
    );

    mysqli_stmt_bind_param(
        $stmtCategoria,
        'i',
        $idCategoria
    );

    mysqli_stmt_execute($stmtCategoria);
    mysqli_stmt_store_result($stmtCategoria);

    if (mysqli_stmt_num_rows($stmtCategoria) === 0) {
        mysqli_stmt_close($stmtCategoria);

        $_SESSION['errores_inmueble'] = [
            'id_categoria' => 'Categoría no válida.',
        ];

        $_SESSION['datos_inmueble'] = $datosPrevios;

        $destino = $esEdicion
            ? '../vendedor/editar-inmueble.php?id='
                . (int) $idInmueble
            : '../vendedor/agregar-inmueble.php';

        redirigir($destino);
    }

    mysqli_stmt_close($stmtCategoria);
} catch (mysqli_sql_exception $error) {
    $_SESSION['error_general'] =
        'No fue posible procesar el inmueble.';

    redirigir('../vendedor/mis-inmuebles.php');
}

// ─── Insertar o actualizar ───────────────────────────────────────────────────

try {
    if ($esEdicion) {
        $stmtPropiedad = mysqli_prepare(
            $conexion,
            'SELECT id_inmueble
             FROM Inmueble
             WHERE id_inmueble = ?
               AND id_usuario_publicador = ?
             LIMIT 1'
        );

        mysqli_stmt_bind_param(
            $stmtPropiedad,
            'ii',
            $idInmueble,
            $idUsuario
        );

        mysqli_stmt_execute($stmtPropiedad);
        mysqli_stmt_store_result($stmtPropiedad);

        if (mysqli_stmt_num_rows($stmtPropiedad) === 0) {
            mysqli_stmt_close($stmtPropiedad);

            $_SESSION['error_general'] =
                'No tienes permiso para editar este inmueble.';

            redirigir('../vendedor/mis-inmuebles.php');
        }

        mysqli_stmt_close($stmtPropiedad);

        mysqli_begin_transaction($conexion);

        try {
            $stmtGuardar = mysqli_prepare(
                $conexion,
                'UPDATE Inmueble SET
                    titulo = ?,
                    id_categoria = ?,
                    id_condicion = ?,
                    descripcion = ?,
                    precio = ?,
                    moneda = ?,
                    estado = ?,
                    ciudad = ?,
                    colonia = ?,
                    direccion = ?,
                    codigo_postal = ?,
                    recamaras = ?,
                    banos = ?,
                    estacionamientos = ?,
                    metros_terreno = ?,
                    metros_construccion = ?,
                    antiguedad = ?
                 WHERE id_inmueble = ?
                   AND id_usuario_publicador = ?'
            );

            mysqli_stmt_bind_param(
                $stmtGuardar,
                'siisdssssssididdiii',
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
                $codigoPostalGuardar,
                $recamaras,
                $banos,
                $estacionamientos,
                $metrosTerreno,
                $metrosConstruccion,
                $antiguedad,
                $idInmueble,
                $idUsuario
            );

            mysqli_stmt_execute($stmtGuardar);
            mysqli_stmt_close($stmtGuardar);

            /*
             * Repara automáticamente inmuebles antiguos que todavía
             * no tenían relación con el vendedor.
             */
            asignarInmuebleAVendedor(
                $conexion,
                (int) $idInmueble,
                $idVendedor
            );

            mysqli_commit($conexion);

            $_SESSION['mensaje_exito'] =
                'Inmueble actualizado correctamente.';

            redirigir(
                '../vendedor/editar-inmueble.php?id='
                . (int) $idInmueble
            );
        } catch (mysqli_sql_exception $error) {
            mysqli_rollback($conexion);
            throw $error;
        }
    }

    // ─── Crear inmueble nuevo ────────────────────────────────────────────────

    $idEstadoBorrador = 1;

    mysqli_begin_transaction($conexion);

    try {
        $stmtGuardar = mysqli_prepare(
            $conexion,
            'INSERT INTO Inmueble (
                id_usuario_publicador,
                id_categoria,
                id_condicion,
                id_estado_publicacion,
                titulo,
                descripcion,
                precio,
                moneda,
                estado,
                ciudad,
                colonia,
                direccion,
                codigo_postal,
                recamaras,
                banos,
                estacionamientos,
                metros_terreno,
                metros_construccion,
                antiguedad
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?
            )'
        );

        mysqli_stmt_bind_param(
            $stmtGuardar,
            'iiiissdssssssididdi',
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
            $codigoPostalGuardar,
            $recamaras,
            $banos,
            $estacionamientos,
            $metrosTerreno,
            $metrosConstruccion,
            $antiguedad
        );

        mysqli_stmt_execute($stmtGuardar);

        $nuevoIdInmueble = (int) mysqli_insert_id(
            $conexion
        );

        mysqli_stmt_close($stmtGuardar);

        /*
         * Asignación automática:
         * el inmueble queda relacionado con el vendedor que lo creó.
         */
        asignarInmuebleAVendedor(
            $conexion,
            $nuevoIdInmueble,
            $idVendedor
        );

        mysqli_commit($conexion);

        $_SESSION['mensaje_exito'] =
            'Inmueble creado en borrador y asignado al vendedor. '
            . 'Ahora puedes subir fotografías y enviarlo a revisión.';

        redirigir(
            '../vendedor/subir-fotos.php?id='
            . $nuevoIdInmueble
        );
    } catch (mysqli_sql_exception $error) {
        mysqli_rollback($conexion);

        $_SESSION['error_general'] =
            'No fue posible crear y asignar el inmueble. '
            . 'Inténtalo nuevamente.';

        $_SESSION['datos_inmueble'] =
            $datosPrevios;

        redirigir('../vendedor/agregar-inmueble.php');
    }
} catch (mysqli_sql_exception $error) {
    $_SESSION['error_general'] =
        'No fue posible guardar el inmueble. '
        . 'Inténtalo nuevamente.';

    $_SESSION['datos_inmueble'] =
        $datosPrevios;

    $destino = $esEdicion
        ? '../vendedor/editar-inmueble.php?id='
            . (int) $idInmueble
        : '../vendedor/agregar-inmueble.php';

    redirigir($destino);
}
