<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

$idComprador = (int) ($_SESSION['id_perfil'] ?? 0);

$errorGeneral = $_SESSION['error_agendar'] ?? null;
unset($_SESSION['error_agendar']);

$idCita = filter_input(
    INPUT_GET,
    'id_cita',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$citaReprogramar = null;
$idInmueble = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($idCita !== null && $idCita !== false) {
    try {
        $stmtCita = mysqli_prepare(
            $conexion,
            "
            SELECT
                c.id_cita,
                c.id_inmueble,
                c.id_vendedor,
                c.fecha_inicio,
                c.comentario_solicitud
            FROM Cita AS c
            WHERE c.id_cita = ?
              AND c.id_comprador = ?
              AND c.id_estado_cita IN (1, 2)
            LIMIT 1
            "
        );

        mysqli_stmt_bind_param(
            $stmtCita,
            'ii',
            $idCita,
            $idComprador
        );

        mysqli_stmt_execute($stmtCita);
        $resultadoCita = mysqli_stmt_get_result($stmtCita);
        $citaReprogramar = mysqli_fetch_assoc($resultadoCita) ?: null;
        mysqli_stmt_close($stmtCita);

        if ($citaReprogramar === null) {
            $_SESSION['error_general'] =
                'La cita no existe, no te pertenece o ya no puede reprogramarse.';
            header('Location: citas.php');
            exit;
        }

        $idInmueble = (int) $citaReprogramar['id_inmueble'];
    } catch (mysqli_sql_exception $error) {
        $_SESSION['error_general'] =
            'No fue posible cargar la cita para reprogramarla.';
        header('Location: citas.php');
        exit;
    }
}

$inmueble = null;
$vendedores = [];

if ($idInmueble !== null && $idInmueble !== false) {
    try {
        $stmt = mysqli_prepare(
            $conexion,
            "
            SELECT
                i.id_inmueble,
                i.titulo,
                i.precio,
                i.moneda,
                i.ciudad,
                i.estado AS estado_geo,
                (
                    SELECT fi.url_foto
                    FROM FotoInmueble AS fi
                    WHERE fi.id_inmueble = i.id_inmueble
                    ORDER BY fi.principal DESC, fi.id_foto ASC
                    LIMIT 1
                ) AS url_foto
            FROM Inmueble AS i
            WHERE i.id_inmueble = ?
              AND i.id_estado_publicacion = 3
            LIMIT 1
            "
        );

        mysqli_stmt_bind_param($stmt, 'i', $idInmueble);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $inmueble = mysqli_fetch_assoc($resultado) ?: null;

        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $error) {
        $inmueble = null;
    }

    if ($inmueble !== null) {
        try {
            $stmtVendedores = mysqli_prepare(
                $conexion,
                "
                SELECT
                    v.id_vendedor,
                    u.nombre,
                    u.apellido
                FROM InmuebleVendedor AS iv
                INNER JOIN Vendedor AS v
                    ON v.id_vendedor = iv.id_vendedor
                INNER JOIN Usuario AS u
                    ON u.id_usuario = v.id_usuario
                INNER JOIN EstadoCuenta AS ec
                    ON ec.id_estado_cuenta = u.id_estado_cuenta
                WHERE iv.id_inmueble = ?
                  AND iv.activo = TRUE
                  AND LOWER(ec.nombre_estado) = 'activa'
                ORDER BY u.apellido, u.nombre
                "
            );

            mysqli_stmt_bind_param(
                $stmtVendedores,
                'i',
                $idInmueble
            );

            mysqli_stmt_execute($stmtVendedores);

            $resultadoVendedores = mysqli_stmt_get_result(
                $stmtVendedores
            );

            $vendedores = mysqli_fetch_all(
                $resultadoVendedores,
                MYSQLI_ASSOC
            );

            mysqli_stmt_close($stmtVendedores);
        } catch (mysqli_sql_exception $error) {
            $vendedores = [];
        }
    }
}

$inmuebles = [];

try {
    $stmtInmuebles = mysqli_prepare(
        $conexion,
        "
        SELECT
            i.id_inmueble,
            i.titulo,
            i.ciudad,
            i.estado AS estado_geo
        FROM Inmueble AS i
        WHERE i.id_estado_publicacion = 3
        ORDER BY i.titulo
        "
    );

    mysqli_stmt_execute($stmtInmuebles);

    $resultadoInmuebles = mysqli_stmt_get_result(
        $stmtInmuebles
    );

    $inmuebles = mysqli_fetch_all(
        $resultadoInmuebles,
        MYSQLI_ASSOC
    );

    mysqli_stmt_close($stmtInmuebles);
} catch (mysqli_sql_exception $error) {
    $inmuebles = [];
}

$todosVendedores = [];

if ($inmueble === null) {
    try {
        $stmtTodosVendedores = mysqli_prepare(
            $conexion,
            "
            SELECT
                v.id_vendedor,
                u.nombre,
                u.apellido
            FROM Vendedor AS v
            INNER JOIN Usuario AS u
                ON u.id_usuario = v.id_usuario
            INNER JOIN EstadoCuenta AS ec
                ON ec.id_estado_cuenta = u.id_estado_cuenta
            WHERE LOWER(ec.nombre_estado) = 'activa'
            ORDER BY u.apellido, u.nombre
            "
        );

        mysqli_stmt_execute($stmtTodosVendedores);

        $resultadoTodos = mysqli_stmt_get_result(
            $stmtTodosVendedores
        );

        $todosVendedores = mysqli_fetch_all(
            $resultadoTodos,
            MYSQLI_ASSOC
        );

        mysqli_stmt_close($stmtTodosVendedores);
    } catch (mysqli_sql_exception $error) {
        $todosVendedores = [];
    }
}

$paginaActual = 'agendar.php';
$tituloPagina = 'Agendar cita | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <h2>
                <?php
                echo $citaReprogramar !== null
                    ? 'Reprogramar cita'
                    : 'Agendar Cita';
                ?>
            </h2>
            <p>
                <?php if ($citaReprogramar !== null): ?>
                    Modifica la fecha y hora de tu visita.
                    La cita volverá a estado pendiente.
                <?php else: ?>
                    Selecciona la fecha y hora para visitar una propiedad.
                    El vendedor te confirmará pronto.
                <?php endif; ?>
            </p>
        </div>

        <?php if ($errorGeneral !== null): ?>
            <div class="mensaje-error" style="margin-bottom: 24px;">
                <?php
                echo htmlspecialchars(
                    $errorGeneral,
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($inmuebles)): ?>

            <div class="card" style="padding: 40px; text-align: center;">
                <h3 style="color: #666; font-weight: normal;">
                    No hay propiedades disponibles en este momento.
                </h3>
                <p style="margin-top: 15px;">
                    Vuelve más tarde o explora el catálogo público.
                </p>
                <a
                    href="../public/catalogo.php"
                    class="btn btn-secundario"
                    style="margin-top: 20px;"
                >
                    Ver catálogo
                </a>
            </div>

        <?php else: ?>

            <div class="grid-2">

                <?php if ($inmueble !== null): ?>
                    <div class="card-inmueble">

                        <?php if (!empty($inmueble['url_foto'])): ?>
                            <?php
                            $rutaRelativaFoto = ltrim(
                                (string) $inmueble['url_foto'],
                                '/'
                            );

                            if (
                                str_starts_with(
                                    $rutaRelativaFoto,
                                    'public/'
                                )
                            ) {
                                $rutaRelativaFoto = substr(
                                    $rutaRelativaFoto,
                                    strlen('public/')
                                );
                            }

                            $rutaFoto = '../public/' . $rutaRelativaFoto;
                            ?>

                            <img
                                src="<?php
                                echo htmlspecialchars(
                                    $rutaFoto,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>"
                                alt="<?php
                                echo htmlspecialchars(
                                    $inmueble['titulo'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>"
                            >
                        <?php else: ?>
                            <div
                                class="sin-foto"
                                aria-label="Sin fotografía"
                            ></div>
                        <?php endif; ?>

                        <div class="card-contenido">
                            <h3>
                                <?php
                                echo htmlspecialchars(
                                    $inmueble['titulo'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </h3>

                            <?php
                            $partesUbicacion = array_filter([
                                $inmueble['ciudad'] ?? '',
                                $inmueble['estado_geo'] ?? '',
                            ]);
                            ?>

                            <?php if (!empty($partesUbicacion)): ?>
                                <p class="ubicacion">
                                    <?php
                                    echo htmlspecialchars(
                                        implode(', ', $partesUbicacion),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($inmueble['precio'])): ?>
                                <p class="precio">
                                    $<?php
                                    echo number_format(
                                        (float) $inmueble['precio'],
                                        0,
                                        '.',
                                        ','
                                    );
                                    ?>
                                    <?php
                                    echo htmlspecialchars(
                                        $inmueble['moneda'] ?? 'MXN',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div
                        class="card"
                        style="
                            padding: 30px;
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                            align-items: center;
                            text-align: center;
                            gap: 12px;
                        "
                    >
                        <p style="font-size: 40px;">🏠</p>
                        <p style="color: #666;">
                            Selecciona una propiedad del catálogo para
                            ver su vista previa aquí.
                        </p>
                        <a
                            href="../public/catalogo.php"
                            class="btn btn-claro"
                            style="margin-top: 8px;"
                        >
                            Ver catálogo
                        </a>
                    </div>
                <?php endif; ?>

                <div class="card" style="padding: 30px;">
                    <h3 style="margin-bottom: 20px;">
                        Detalles de la visita
                    </h3>

                    <form
                        class="formulario"
                        action="../procesos/procesar-agendar-cita.php"
                        method="POST"
                    >

                        <?php if ($citaReprogramar !== null): ?>
                            <input
                                type="hidden"
                                name="id_cita"
                                value="<?php echo (int) $citaReprogramar['id_cita']; ?>"
                            >
                        <?php endif; ?>

                        <?php if ($inmueble !== null): ?>
                            <input
                                type="hidden"
                                name="id_inmueble"
                                value="<?php
                                echo (int) $inmueble['id_inmueble'];
                                ?>"
                            >
                        <?php else: ?>
                            <div>
                                <label for="id_inmueble">
                                    Propiedad
                                    <span style="color: #e94b27;">*</span>
                                </label>

                                <select
                                    id="id_inmueble"
                                    name="id_inmueble"
                                    required
                                >
                                    <option value="">
                                        -- Elige una propiedad --
                                    </option>

                                    <?php foreach ($inmuebles as $item): ?>
                                        <option
                                            value="<?php
                                            echo (int) $item['id_inmueble'];
                                            ?>"
                                        >
                                            <?php
                                            $partes = array_filter([
                                                $item['ciudad'] ?? '',
                                                $item['estado_geo'] ?? '',
                                            ]);

                                            $lugar = !empty($partes)
                                                ? ' — ' . implode(', ', $partes)
                                                : '';

                                            echo htmlspecialchars(
                                                $item['titulo'] . $lugar,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 15px;">
                            <label for="id_vendedor">
                                Vendedor
                                <span style="color: #e94b27;">*</span>
                            </label>

                            <select
                                id="id_vendedor"
                                name="id_vendedor"
                                required
                            >
                                <option value="">
                                    -- Elige un vendedor --
                                </option>

                                <?php
                                $listaVendedores = $inmueble !== null
                                    ? $vendedores
                                    : $todosVendedores;
                                ?>

                                <?php foreach ($listaVendedores as $vendedor): ?>
                                    <option
                                        value="<?php
                                        echo (int) $vendedor['id_vendedor'];
                                        ?>"
                                        <?php
                                        echo (
                                            $citaReprogramar !== null
                                            && (int) $citaReprogramar['id_vendedor']
                                                === (int) $vendedor['id_vendedor']
                                        ) ? 'selected' : '';
                                        ?>
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $vendedor['nombre']
                                                . ' '
                                                . $vendedor['apellido'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php
                            if (
                                $inmueble !== null
                                && empty($vendedores)
                            ):
                            ?>
                                <p class="form-error">
                                    Esta propiedad no tiene vendedores
                                    activos asignados actualmente.
                                </p>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 15px;">
                            <label for="fecha">
                                Fecha sugerida
                                <span style="color: #e94b27;">*</span>
                            </label>

                            <input
                                type="date"
                                id="fecha"
                                name="fecha"
                                min="<?php
                                echo date(
                                    'Y-m-d',
                                    strtotime('+1 day')
                                );
                                ?>"
                                value="<?php
                                echo $citaReprogramar !== null
                                    ? htmlspecialchars(
                                        date(
                                            'Y-m-d',
                                            strtotime($citaReprogramar['fecha_inicio'])
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                    : '';
                                ?>"
                                required
                            >
                        </div>

                        <div style="margin-top: 15px;">
                            <label for="hora">
                                Hora sugerida
                                <span style="color: #e94b27;">*</span>
                            </label>

                            <?php
                            $horaActual = $citaReprogramar !== null
                                ? date('H:i', strtotime($citaReprogramar['fecha_inicio']))
                                : '';
                            ?>
                            <select id="hora" name="hora" required>
                                <option value="">-- Elige una hora --</option>
                                <?php
                                for ($h = 9; $h <= 17; $h++) {
                                    foreach ([0, 30] as $m) {
                                        if ($h === 17 && $m === 30) {
                                            break;
                                        }
                                        $slot = sprintf('%02d:%02d', $h, $m);
                                        $sel  = $horaActual === $slot ? 'selected' : '';
                                        $label = $h < 12
                                            ? $slot . ' a.m.'
                                            : ($h === 12 ? $slot . ' p.m.' : sprintf('%02d:%02d p.m.', $h - 12, $m));
                                        echo "<option value=\"{$slot}\" {$sel}>{$label}</option>\n";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div style="margin-top: 15px;">
                            <label for="comentario_solicitud">
                                Mensaje para el vendedor (opcional)
                            </label>

                            <textarea
                                id="comentario_solicitud"
                                name="comentario_solicitud"
                                placeholder="Ej. Me interesa ver los acabados de la cocina..."
                            ><?php
                            echo $citaReprogramar !== null
                                ? htmlspecialchars(
                                    (string) ($citaReprogramar['comentario_solicitud'] ?? ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                                : '';
                            ?></textarea>
                        </div>

                        <div style="margin-top: 25px;">
                            <button
                                type="submit"
                                class="btn btn-principal btn-completo"
                                <?php
                                echo (
                                    $inmueble !== null
                                    && empty($vendedores)
                                ) ? 'disabled' : '';
                                ?>
                            >
                                <?php
                                echo $citaReprogramar !== null
                                    ? 'Guardar reprogramación'
                                    : 'Confirmar cita';
                                ?>
                            </button>
                        </div>

                    </form>
                </div>

            </div>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
