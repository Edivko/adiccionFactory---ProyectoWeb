<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';

$sesionIniciada = isset(
    $_SESSION['id_usuario'],
    $_SESSION['id_rol'],
    $_SESSION['id_perfil']
);

$idRolSesion = $sesionIniciada
    ? (int) $_SESSION['id_rol']
    : 0;


// ─── Validar ID ──────────────────────────────────────────────────────────────

$idVendedor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

$error404 = ($idVendedor === null || $idVendedor === false);

// ─── Consulta principal del vendedor ─────────────────────────────────────────

$vendedor = null;

if (!$error404) {
    try {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                v.id_vendedor,
                v.descripcion,
                v.experiencia,
                v.foto_perfil,
                v.zona_trabajo,
                u.nombre,
                u.apellido,
                ROUND(AVG(cv.puntuacion), 1)              AS calificacion_promedio,
                COUNT(DISTINCT cv.id_calificacion_vendedor) AS total_calificaciones,
                (SELECT COUNT(*)
                 FROM InmuebleVendedor iv2
                 WHERE iv2.id_vendedor = v.id_vendedor
                   AND iv2.activo = TRUE)                   AS total_inmuebles,
                (SELECT COUNT(*)
                 FROM Cita c2
                 INNER JOIN EstadoCita ec2
                        ON ec2.id_estado_cita = c2.id_estado_cita
                 WHERE c2.id_vendedor = v.id_vendedor
                   AND ec2.nombre_estado = \'realizada\')  AS total_citas
            FROM Vendedor v
            INNER JOIN Usuario u ON u.id_usuario = v.id_usuario
            LEFT  JOIN CalificacionVendedor cv ON cv.id_vendedor = v.id_vendedor
            WHERE v.id_vendedor    = ?
              AND u.id_estado_cuenta = 2
            GROUP BY
                v.id_vendedor, v.descripcion, v.experiencia,
                v.foto_perfil, v.zona_trabajo, u.nombre, u.apellido
            LIMIT 1
        ');

        mysqli_stmt_bind_param($stmt, 'i', $idVendedor);
        mysqli_stmt_execute($stmt);
        $vendedor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$vendedor) {
            $error404 = true;
        }

    } catch (mysqli_sql_exception $e) {
        $error404 = true;
    }
}

// ─── Consultas secundarias ────────────────────────────────────────────────────

$inmuebles   = [];
$comentarios = [];

if (!$error404) {
    try {
        // Inmuebles activos y publicados del vendedor (hasta 6)
        $s = mysqli_prepare($conexion, '
            SELECT
                i.id_inmueble,
                i.titulo,
                i.precio,
                i.moneda,
                i.ciudad,
                i.estado          AS estado_geo,
                i.recamaras,
                i.banos,
                c.nombre_categoria,
                (SELECT url_foto
                 FROM FotoInmueble
                 WHERE id_inmueble = i.id_inmueble
                   AND principal   = TRUE
                 LIMIT 1)         AS url_foto
            FROM InmuebleVendedor iv
            INNER JOIN Inmueble i          ON i.id_inmueble  = iv.id_inmueble
            INNER JOIN CategoriaInmueble c ON c.id_categoria = i.id_categoria
            WHERE iv.id_vendedor          = ?
              AND iv.activo               = TRUE
              AND i.id_estado_publicacion = 3
            ORDER BY i.fecha_registro DESC
            LIMIT 6
        ');
        mysqli_stmt_bind_param($s, 'i', $idVendedor);
        mysqli_stmt_execute($s);
        $inmuebles = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
        mysqli_stmt_close($s);

        // Opiniones públicas: comentarios visibles y comentarios de calificaciones
        $s = mysqli_prepare($conexion, "
            SELECT
                opiniones.contenido,
                opiniones.fecha_comentario,
                opiniones.nombre,
                opiniones.apellido
            FROM (
                SELECT
                    com.contenido,
                    com.fecha_comentario,
                    u.nombre,
                    u.apellido
                FROM Comentario AS com
                INNER JOIN Usuario AS u
                    ON u.id_usuario = com.id_usuario
                INNER JOIN EstadoComentario AS ec
                    ON ec.id_estado_comentario = com.id_estado_comentario
                WHERE com.id_vendedor = ?
                  AND LOWER(ec.nombre_estado) = 'visible'

                UNION ALL

                SELECT
                    cv.comentario AS contenido,
                    cv.fecha_calificacion AS fecha_comentario,
                    u.nombre,
                    u.apellido
                FROM CalificacionVendedor AS cv
                INNER JOIN Comprador AS cp
                    ON cp.id_comprador = cv.id_comprador
                INNER JOIN Usuario AS u
                    ON u.id_usuario = cp.id_usuario
                WHERE cv.id_vendedor = ?
                  AND cv.comentario IS NOT NULL
                  AND TRIM(cv.comentario) <> ''
            ) AS opiniones
            ORDER BY opiniones.fecha_comentario DESC
            LIMIT 10
        ");
        mysqli_stmt_bind_param($s, 'ii', $idVendedor, $idVendedor);
        mysqli_stmt_execute($s);
        $comentarios = mysqli_fetch_all(
            mysqli_stmt_get_result($s),
            MYSQLI_ASSOC
        );
        mysqli_stmt_close($s);

    } catch (mysqli_sql_exception $e) {
        // Muestra la página con datos parciales
    }
}

// ─── Respuesta 404 ────────────────────────────────────────────────────────────

if ($error404) {
    http_response_code(404);
}

// ─── Funciones auxiliares ─────────────────────────────────────────────────────

function formatearFechaEs(string $fechaSQL): string
{
    $meses = [
        '', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];
    $ts = strtotime($fechaSQL);
    if ($ts === false) {
        return $fechaSQL;
    }
    return date('j', $ts) . ' de ' . $meses[(int) date('n', $ts)] . ' de ' . date('Y', $ts);
}

function generarEstrellas(float $cal): string
{
    $llenas = max(0, min(5, (int) round($cal)));
    return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

// ─── Variables de presentación ────────────────────────────────────────────────

if (!$error404) {
    $nombreV      = htmlspecialchars($vendedor['nombre'] . ' ' . $vendedor['apellido'], ENT_QUOTES, 'UTF-8');
    $zonaV        = htmlspecialchars($vendedor['zona_trabajo'] ?? '', ENT_QUOTES, 'UTF-8');
    $descripcionV = htmlspecialchars($vendedor['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
    $fotoV        = !empty($vendedor['foto_perfil'])
        ? htmlspecialchars($vendedor['foto_perfil'], ENT_QUOTES, 'UTF-8')
        : 'recursos/img/vendedor1.jpg';
    $expAnios     = (int) ($vendedor['experiencia'] ?? 0);
    $expTexto     = $expAnios === 1 ? '1 año' : $expAnios . ' años';
    $calPromedio  = $vendedor['calificacion_promedio'];
    $totalCal     = (int) $vendedor['total_calificaciones'];
    $totalInm     = (int) $vendedor['total_inmuebles'];
    $totalCitas   = (int) $vendedor['total_citas'];
    $idMostrar    = (int) $vendedor['id_vendedor'];

    $tituloPagina = $nombreV . ' | Adicción Factory Inmobiliaria';
} else {
    $tituloPagina = 'Vendedor no encontrado | Adicción Factory Inmobiliaria';
}

include('includes/header.php');
?>

<main>

<?php if ($error404): ?>

    <section class="encabezado-pagina">
        <div class="contenedor">
            <p class="etiqueta">Página no encontrada</p>
            <h1>Este vendedor no está disponible</h1>
            <p>El perfil solicitado no existe o no está disponible actualmente.</p>
        </div>
    </section>

    <section class="seccion">
        <div class="contenedor">
            <a href="catalogo.php" class="btn btn-principal">
                Volver al catálogo
            </a>
        </div>
    </section>

<?php else: ?>

    <section class="encabezado-pagina encabezado-vendedor">
        <div class="contenedor">

            <nav class="migas-pan" aria-label="Ruta de navegación">
                <a href="index.php">Inicio</a>
                <span>/</span>
                <a href="catalogo.php">Catálogo</a>
                <span>/</span>
                <span>Perfil del vendedor</span>
            </nav>

            <p class="etiqueta">Asesor inmobiliario</p>
            <h1><?php echo $nombreV; ?></h1>

            <p>
                Consulta su experiencia, zona de trabajo, inmuebles asignados
                y opiniones recibidas.
            </p>

        </div>
    </section>

    <section class="seccion perfil-vendedor-seccion">
        <div class="contenedor perfil-vendedor-layout">

            <!-- INFORMACIÓN PRINCIPAL -->
            <section class="perfil-vendedor-contenido">

                <!-- Foto + datos destacados -->
                <article class="perfil-principal">

                    <div class="perfil-foto">
                        <img
                            src="<?php echo $fotoV; ?>"
                            alt="Fotografía de <?php echo $nombreV; ?>"
                        >
                    </div>

                    <div class="perfil-informacion">

                        <span class="badge">Vendedor verificado</span>

                        <h2><?php echo $nombreV; ?></h2>

                        <?php if ($zonaV !== ''): ?>
                            <p class="perfil-zona"><?php echo $zonaV; ?></p>
                        <?php endif; ?>

                        <?php if ($calPromedio !== null && $totalCal > 0): ?>
                            <div class="perfil-calificacion">
                                <span class="estrellas">
                                    <?php echo generarEstrellas((float) $calPromedio); ?>
                                </span>
                                <strong><?php echo $calPromedio; ?></strong>
                                <small>
                                    Basado en
                                    <?php echo $totalCal; ?>
                                    <?php echo $totalCal === 1 ? 'calificación' : 'calificaciones'; ?>
                                </small>
                            </div>
                        <?php endif; ?>

                        <?php if ($descripcionV !== ''): ?>
                            <p class="perfil-descripcion">
                                <?php echo nl2br($descripcionV); ?>
                            </p>
                        <?php endif; ?>

                        <div class="perfil-datos-grid">

                            <?php if ($expAnios > 0): ?>
                                <div class="perfil-dato">
                                    <strong><?php echo $expTexto; ?></strong>
                                    <span>Experiencia</span>
                                </div>
                            <?php endif; ?>

                            <div class="perfil-dato">
                                <strong><?php echo $totalInm; ?></strong>
                                <span>Inmuebles asignados</span>
                            </div>

                            <div class="perfil-dato">
                                <strong><?php echo $totalCitas; ?></strong>
                                <span>Citas realizadas</span>
                            </div>

                        </div>

                    </div>

                </article>

                <!-- Inmuebles asignados -->
                <article class="detalle-bloque">

                    <div class="titulo-con-accion">
                        <div>
                            <p class="etiqueta etiqueta-oscura">Propiedades</p>
                            <h2>Inmuebles que atiende</h2>
                        </div>

                        <a href="catalogo.php" class="enlace-seccion">
                            Ver catálogo completo
                        </a>
                    </div>

                    <?php if (!empty($inmuebles)): ?>

                        <div class="perfil-inmuebles-grid">

                            <?php foreach ($inmuebles as $inm): ?>
                                <?php
                                $tituloI  = htmlspecialchars($inm['titulo'],           ENT_QUOTES, 'UTF-8');
                                $catI     = htmlspecialchars($inm['nombre_categoria'], ENT_QUOTES, 'UTF-8');
                                $partesI  = array_filter([
                                    $inm['ciudad']     ?? '',
                                    $inm['estado_geo'] ?? '',
                                ]);
                                $ubicI    = htmlspecialchars(implode(', ', $partesI),  ENT_QUOTES, 'UTF-8');
                                $precioI  = $inm['precio'] !== null
                                    ? '$' . number_format((float) $inm['precio'], 2)
                                      . ' ' . htmlspecialchars($inm['moneda'] ?? 'MXN', ENT_QUOTES, 'UTF-8')
                                    : 'Precio a consultar';
                                $idInm    = (int) $inm['id_inmueble'];
                                ?>

                                <article class="card card-inmueble">

                                    <div class="card-imagen">

                                        <?php if ($inm['url_foto'] !== null): ?>
                                            <img
                                                src="<?php echo htmlspecialchars($inm['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                                alt="<?php echo $tituloI; ?>"
                                            >
                                        <?php else: ?>
                                            <div class="sin-foto" aria-label="Sin fotografía disponible"></div>
                                        <?php endif; ?>

                                        <span class="estado-inmueble">Disponible</span>
                                    </div>

                                    <div class="card-contenido">
                                        <span class="badge"><?php echo $catI; ?></span>

                                        <h3><?php echo $tituloI; ?></h3>

                                        <p class="precio"><?php echo $precioI; ?></p>

                                        <?php if ($ubicI !== ''): ?>
                                            <p class="ubicacion"><?php echo $ubicI; ?></p>
                                        <?php endif; ?>

                                        <div class="caracteristicas">
                                            <?php if ($inm['recamaras'] !== null): ?>
                                                <span><?php echo (int) $inm['recamaras']; ?> recámaras</span>
                                            <?php endif; ?>

                                            <?php if ($inm['banos'] !== null): ?>
                                                <span><?php echo (float) $inm['banos']; ?> baños</span>
                                            <?php endif; ?>
                                        </div>

                                        <a
                                            href="detalle-inmueble.php?id=<?php echo $idInm; ?>"
                                            class="btn btn-secundario btn-completo"
                                        >
                                            Ver detalle
                                        </a>
                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <p class="detalle-sin-datos">
                            Este vendedor no tiene inmuebles publicados actualmente.
                        </p>

                    <?php endif; ?>

                </article>

                <!-- Comentarios -->
                <article class="detalle-bloque">

                    <div class="titulo-con-accion">
                        <div>
                            <p class="etiqueta etiqueta-oscura">Experiencias</p>
                            <h2>Comentarios recibidos</h2>
                        </div>

                        <?php if ($calPromedio !== null && $totalCal > 0): ?>
                            <span class="calificacion-general">
                                &#9733; <?php echo $calPromedio; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($comentarios)): ?>

                        <div class="comentarios-lista">

                            <?php foreach ($comentarios as $com): ?>
                                <article class="comentario">

                                    <div class="comentario-cabecera">
                                        <div>
                                            <strong>
                                                <?php echo htmlspecialchars(
                                                    $com['nombre'] . ' ' . $com['apellido'],
                                                    ENT_QUOTES, 'UTF-8'
                                                ); ?>
                                            </strong>
                                            <span>
                                                <?php echo formatearFechaEs($com['fecha_comentario']); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <p>
                                        <?php echo htmlspecialchars($com['contenido'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>

                                </article>
                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <p class="detalle-sin-datos">
                            Todavía no hay comentarios para este vendedor.
                        </p>

                    <?php endif; ?>

                </article>

            </section>

            <!-- PANEL LATERAL -->
            <aside class="perfil-vendedor-resumen">

                <div class="perfil-resumen-card">

                    <span class="form-info-icono">CV</span>

                    <h2>Contacta a este vendedor</h2>

                    <p>
                        Para solicitar una cita elige uno de los inmuebles
                        que el vendedor tiene asignados.
                    </p>

                    <a href="catalogo.php" class="btn btn-principal btn-completo">
                        Ver inmuebles
                    </a>

                    <?php if (!$sesionIniciada): ?>
                        <a href="login.php" class="btn btn-secundario btn-completo">
                            Iniciar sesión
                        </a>
                    <?php elseif ($idRolSesion === 1): ?>
                        <a href="../comprador/index.php" class="btn btn-secundario btn-completo">
                            Ir a mi panel
                        </a>
                    <?php elseif ($idRolSesion === 2): ?>
                        <a href="../vendedor/index.php" class="btn btn-secundario btn-completo">
                            Ir a mi panel de vendedor
                        </a>
                    <?php elseif ($idRolSesion === 3): ?>
                        <a href="../admin/dashboard.php" class="btn btn-secundario btn-completo">
                            Ir al panel administrativo
                        </a>
                    <?php endif; ?>

                    <div class="resumen-separador"></div>

                    <div class="resumen-dato">
                        <span>ID del vendedor</span>
                        <strong>#<?php echo $idMostrar; ?></strong>
                    </div>

                    <div class="resumen-dato">
                        <span>Estado</span>
                        <strong>Verificado</strong>
                    </div>

                    <?php if ($zonaV !== ''): ?>
                        <div class="resumen-dato">
                            <span>Zona de trabajo</span>
                            <strong><?php echo $zonaV; ?></strong>
                        </div>
                    <?php endif; ?>

                    <?php if ($expAnios > 0): ?>
                        <div class="resumen-dato">
                            <span>Experiencia</span>
                            <strong><?php echo $expTexto; ?></strong>
                        </div>
                    <?php endif; ?>

                </div>

            </aside>

        </div>
    </section>

<?php endif; ?>

</main>

<?php
include('includes/footer.php');
?>
