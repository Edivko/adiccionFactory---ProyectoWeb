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

$idInmueble = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

$error404 = ($idInmueble === null || $idInmueble === false);

// ─── Consulta principal ───────────────────────────────────────────────────────

$inmueble = null;

if (!$error404) {
    try {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                i.id_inmueble,
                i.titulo,
                i.descripcion,
                i.precio,
                i.moneda,
                i.estado         AS estado_geo,
                i.ciudad,
                i.colonia,
                i.codigo_postal,
                i.recamaras,
                i.banos,
                i.estacionamientos,
                i.metros_terreno,
                i.metros_construccion,
                i.antiguedad,
                i.fecha_publicacion,
                i.fecha_registro,
                c.nombre_categoria,
                con.nombre_condicion
            FROM Inmueble i
            INNER JOIN CategoriaInmueble c  ON c.id_categoria   = i.id_categoria
            LEFT  JOIN CondicionInmueble con ON con.id_condicion = i.id_condicion
            WHERE i.id_inmueble = ?
              AND i.id_estado_publicacion = 3
            LIMIT 1
        ');
        mysqli_stmt_bind_param($stmt, 'i', $idInmueble);
        mysqli_stmt_execute($stmt);
        $inmueble = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$inmueble) {
            $error404 = true;
        }

    } catch (mysqli_sql_exception $e) {
        $error404 = true;
    }
}

// ─── Consultas secundarias ────────────────────────────────────────────────────

$fotos        = [];
$servicios    = [];
$amenidades   = [];
$vendedores   = [];
$comentarios  = [];

if (!$error404) {
    try {
        // Fotografías (principal primero)
        $s = mysqli_prepare($conexion, '
            SELECT url_foto, descripcion
            FROM FotoInmueble
            WHERE id_inmueble = ?
            ORDER BY principal DESC, id_foto ASC
        ');
        mysqli_stmt_bind_param($s, 'i', $idInmueble);
        mysqli_stmt_execute($s);
        $fotos = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
        mysqli_stmt_close($s);

        // Servicios
        $s = mysqli_prepare($conexion, '
            SELECT s.nombre_servicio
            FROM InmuebleServicio ivs
            INNER JOIN Servicio s ON s.id_servicio = ivs.id_servicio
            WHERE ivs.id_inmueble = ?
            ORDER BY s.nombre_servicio ASC
        ');
        mysqli_stmt_bind_param($s, 'i', $idInmueble);
        mysqli_stmt_execute($s);
        $servicios = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
        mysqli_stmt_close($s);

        // Amenidades
        $s = mysqli_prepare($conexion, '
            SELECT a.nombre_amenidad
            FROM InmuebleAmenidad ia
            INNER JOIN Amenidad a ON a.id_amenidad = ia.id_amenidad
            WHERE ia.id_inmueble = ?
            ORDER BY a.nombre_amenidad ASC
        ');
        mysqli_stmt_bind_param($s, 'i', $idInmueble);
        mysqli_stmt_execute($s);
        $amenidades = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
        mysqli_stmt_close($s);

        // Vendedores activos con calificación promedio
        $s = mysqli_prepare($conexion, '
            SELECT
                v.id_vendedor,
                v.experiencia,
                v.foto_perfil,
                v.zona_trabajo,
                u.nombre,
                u.apellido,
                ROUND(AVG(cv.puntuacion), 1) AS calificacion
            FROM InmuebleVendedor iv
            INNER JOIN Vendedor  v  ON v.id_vendedor = iv.id_vendedor
            INNER JOIN Usuario   u  ON u.id_usuario  = v.id_usuario
            LEFT  JOIN CalificacionVendedor cv ON cv.id_vendedor = v.id_vendedor
            WHERE iv.id_inmueble    = ?
              AND iv.activo         = TRUE
              AND u.id_estado_cuenta = 2
            GROUP BY
                v.id_vendedor, v.experiencia, v.foto_perfil,
                v.zona_trabajo, u.nombre, u.apellido
        ');
        mysqli_stmt_bind_param($s, 'i', $idInmueble);
        mysqli_stmt_execute($s);
        $vendedores = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
        mysqli_stmt_close($s);

        // Comentarios visibles del inmueble
        $s = mysqli_prepare($conexion, '
            SELECT
                co.contenido,
                co.fecha_comentario,
                u.nombre,
                u.apellido
            FROM Comentario co
            INNER JOIN Usuario u ON u.id_usuario = co.id_usuario
            WHERE co.id_inmueble          = ?
              AND co.id_estado_comentario = 2
            ORDER BY co.fecha_comentario DESC
        ');
        mysqli_stmt_bind_param($s, 'i', $idInmueble);
        mysqli_stmt_execute($s);
        $comentarios = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
        mysqli_stmt_close($s);

    } catch (mysqli_sql_exception $e) {
        // Los arrays ya están vacíos; la página se muestra con datos parciales
    }
}

// ─── Respuesta 404 ────────────────────────────────────────────────────────────

if ($error404) {
    http_response_code(404);
}

// ─── Formatear fecha en español ───────────────────────────────────────────────

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

// ─── Variables de presentación ────────────────────────────────────────────────

if (!$error404) {
    $tituloH     = htmlspecialchars($inmueble['titulo'],           ENT_QUOTES, 'UTF-8');
    $categoria   = htmlspecialchars($inmueble['nombre_categoria'], ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars($inmueble['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
    $condicion   = htmlspecialchars(
        $inmueble['nombre_condicion'] ?? 'No especificada', ENT_QUOTES, 'UTF-8'
    );

    $partesUbic  = array_filter([
        $inmueble['ciudad']     ?? '',
        $inmueble['estado_geo'] ?? '',
    ]);
    $ubicacionCorta = htmlspecialchars(implode(', ', $partesUbic), ENT_QUOTES, 'UTF-8');

    if ($inmueble['precio'] !== null) {
        $moneda  = htmlspecialchars($inmueble['moneda'] ?? 'MXN', ENT_QUOTES, 'UTF-8');
        $precioF = '$' . number_format((float) $inmueble['precio'], 2) . ' ' . $moneda;
    } else {
        $precioF = 'Precio a consultar';
    }

    $fechaSql       = $inmueble['fecha_publicacion'] ?? $inmueble['fecha_registro'] ?? '';
    $fechaPublicada = $fechaSql !== '' ? formatearFechaEs($fechaSql) : 'No especificada';
    $idMostrar      = (int) $inmueble['id_inmueble'];

    $tituloPagina = $tituloH . ' | Adicción Factory Inmobiliaria';
} else {
    $tituloPagina = 'Inmueble no encontrado | Adicción Factory Inmobiliaria';
}

include('includes/header.php');
?>

<main>

<?php if ($error404): ?>

    <section class="encabezado-pagina">
        <div class="contenedor">
            <p class="etiqueta">Página no encontrada</p>
            <h1>Este inmueble no está disponible</h1>
            <p>El inmueble solicitado no existe o no está disponible actualmente.</p>
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

    <section class="encabezado-pagina encabezado-detalle">
        <div class="contenedor">

            <nav class="migas-pan" aria-label="Ruta de navegación">
                <a href="index.php">Inicio</a>
                <span>/</span>
                <a href="catalogo.php">Catálogo</a>
                <span>/</span>
                <span><?php echo $tituloH; ?></span>
            </nav>

            <p class="etiqueta">Propiedad disponible</p>

            <h1><?php echo $tituloH; ?></h1>

            <?php if ($ubicacionCorta !== ''): ?>
                <p><?php echo $ubicacionCorta; ?></p>
            <?php endif; ?>

        </div>
    </section>

    <section class="seccion detalle-seccion">
        <div class="contenedor">

            <!-- GALERÍA -->
            <?php
            $fotoFallback    = 'recursos/img/casa1.jpg';
            $fotoPrincipal   = !empty($fotos) ? $fotos[0]['url_foto'] : $fotoFallback;
            $altPrincipal    = !empty($fotos) && !empty($fotos[0]['descripcion'])
                ? htmlspecialchars($fotos[0]['descripcion'], ENT_QUOTES, 'UTF-8')
                : $tituloH;
            ?>
            <div class="galeria-inmueble">

                <div class="galeria-principal">
                    <img
                        id="foto-principal"
                        src="<?php echo htmlspecialchars($fotoPrincipal, ENT_QUOTES, 'UTF-8'); ?>"
                        alt="<?php echo $altPrincipal; ?>"
                    >
                </div>

                <div class="galeria-miniaturas">
                    <?php if (!empty($fotos)): ?>
                        <?php foreach ($fotos as $i => $foto): ?>
                            <button
                                type="button"
                                class="miniatura<?php echo $i === 0 ? ' activa' : ''; ?>"
                                data-src="<?php echo htmlspecialchars($foto['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <img
                                    src="<?php echo htmlspecialchars($foto['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo !empty($foto['descripcion'])
                                        ? htmlspecialchars($foto['descripcion'], ENT_QUOTES, 'UTF-8')
                                        : 'Fotografía ' . ($i + 1); ?>"
                                >
                            </button>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <button
                            type="button"
                            class="miniatura activa"
                            data-src="<?php echo htmlspecialchars($fotoFallback, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <img
                                src="<?php echo htmlspecialchars($fotoFallback, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo $tituloH; ?>"
                            >
                        </button>
                    <?php endif; ?>
                </div>

            </div>

            <!-- INFORMACIÓN PRINCIPAL -->
            <div class="detalle-layout">

                <div class="detalle-contenido">

                    <!-- Cabecera + características -->
                    <article class="detalle-bloque">

                        <div class="detalle-cabecera">

                            <div>
                                <span class="badge"><?php echo $categoria; ?></span>
                                <h2><?php echo $tituloH; ?></h2>
                                <?php if ($ubicacionCorta !== ''): ?>
                                    <p class="detalle-ubicacion">
                                        <?php echo $ubicacionCorta; ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="detalle-precio">
                                <span>Precio</span>
                                <strong><?php echo $precioF; ?></strong>
                            </div>

                        </div>

                        <div class="detalle-caracteristicas">

                            <?php if ($inmueble['recamaras'] !== null): ?>
                                <div class="caracteristica-detalle">
                                    <strong><?php echo (int) $inmueble['recamaras']; ?></strong>
                                    <span>Recámaras</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($inmueble['banos'] !== null): ?>
                                <div class="caracteristica-detalle">
                                    <strong><?php echo (float) $inmueble['banos']; ?></strong>
                                    <span>Baños</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($inmueble['estacionamientos'] !== null): ?>
                                <div class="caracteristica-detalle">
                                    <strong><?php echo (int) $inmueble['estacionamientos']; ?></strong>
                                    <span>Estacionamientos</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($inmueble['metros_construccion'] !== null): ?>
                                <div class="caracteristica-detalle">
                                    <strong><?php echo (float) $inmueble['metros_construccion']; ?> m²</strong>
                                    <span>Construcción</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($inmueble['metros_terreno'] !== null): ?>
                                <div class="caracteristica-detalle">
                                    <strong><?php echo (float) $inmueble['metros_terreno']; ?> m²</strong>
                                    <span>Terreno</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($inmueble['antiguedad'] !== null): ?>
                                <div class="caracteristica-detalle">
                                    <strong>
                                        <?php
                                        $ant = (int) $inmueble['antiguedad'];
                                        echo $ant . ($ant === 1 ? ' año' : ' años');
                                        ?>
                                    </strong>
                                    <span>Antigüedad</span>
                                </div>
                            <?php endif; ?>

                        </div>

                    </article>

                    <!-- Descripción -->
                    <?php if ($descripcion !== ''): ?>
                        <article class="detalle-bloque">
                            <h2>Descripción</h2>
                            <p><?php echo nl2br($descripcion); ?></p>
                        </article>
                    <?php endif; ?>

                    <!-- Ubicación -->
                    <article class="detalle-bloque">
                        <h2>Ubicación</h2>

                        <div class="datos-ubicacion">

                            <?php if (!empty($inmueble['estado_geo'])): ?>
                                <div>
                                    <span>Estado</span>
                                    <strong>
                                        <?php echo htmlspecialchars($inmueble['estado_geo'], ENT_QUOTES, 'UTF-8'); ?>
                                    </strong>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($inmueble['ciudad'])): ?>
                                <div>
                                    <span>Ciudad</span>
                                    <strong>
                                        <?php echo htmlspecialchars($inmueble['ciudad'], ENT_QUOTES, 'UTF-8'); ?>
                                    </strong>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($inmueble['colonia'])): ?>
                                <div>
                                    <span>Colonia</span>
                                    <strong>
                                        <?php echo htmlspecialchars($inmueble['colonia'], ENT_QUOTES, 'UTF-8'); ?>
                                    </strong>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($inmueble['codigo_postal'])): ?>
                                <div>
                                    <span>Código postal</span>
                                    <strong>
                                        <?php echo htmlspecialchars($inmueble['codigo_postal'], ENT_QUOTES, 'UTF-8'); ?>
                                    </strong>
                                </div>
                            <?php endif; ?>

                        </div>

                        <p class="nota-ubicacion">
                            La dirección completa podrá mostrarse al confirmar una cita.
                        </p>
                    </article>

                    <!-- Servicios y amenidades -->
                    <?php if (!empty($servicios) || !empty($amenidades)): ?>
                        <article class="detalle-bloque">
                            <div class="detalle-columnas">

                                <?php if (!empty($servicios)): ?>
                                    <div>
                                        <h2>Servicios</h2>
                                        <ul class="lista-detalle">
                                            <?php foreach ($servicios as $srv): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($srv['nombre_servicio'], ENT_QUOTES, 'UTF-8'); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($amenidades)): ?>
                                    <div>
                                        <h2>Amenidades</h2>
                                        <ul class="lista-detalle">
                                            <?php foreach ($amenidades as $am): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($am['nombre_amenidad'], ENT_QUOTES, 'UTF-8'); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </article>
                    <?php endif; ?>

                    <!-- Vendedores -->
                    <article class="detalle-bloque">

                        <div class="titulo-con-accion">
                            <div>
                                <p class="etiqueta etiqueta-oscura">Atención disponible</p>
                                <h2>Vendedores asignados</h2>
                            </div>
                        </div>

                        <?php if (!empty($vendedores)): ?>

                            <p class="texto-intro">
                                Selecciona al vendedor con quien deseas recibir atención
                                para este inmueble.
                            </p>

                            <div class="vendedores-grid">

                                <?php foreach ($vendedores as $vnd): ?>
                                    <?php
                                    $nombreVnd = htmlspecialchars(
                                        $vnd['nombre'] . ' ' . $vnd['apellido'],
                                        ENT_QUOTES, 'UTF-8'
                                    );
                                    $zonaVnd   = htmlspecialchars($vnd['zona_trabajo'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $fotoVnd   = !empty($vnd['foto_perfil'])
                                        ? htmlspecialchars($vnd['foto_perfil'], ENT_QUOTES, 'UTF-8')
                                        : 'recursos/img/vendedor1.jpg';
                                    $expAnios  = (int) $vnd['experiencia'];
                                    $expTexto  = $expAnios === 1 ? '1 año de experiencia' : $expAnios . ' años de experiencia';
                                    $idVnd     = (int) $vnd['id_vendedor'];
                                    ?>

                                    <article class="vendedor-card">

                                        <img
                                            src="<?php echo $fotoVnd; ?>"
                                            alt="Fotografía de <?php echo $nombreVnd; ?>"
                                        >

                                        <div class="vendedor-card-contenido">

                                            <h3><?php echo $nombreVnd; ?></h3>

                                            <?php if ($zonaVnd !== ''): ?>
                                                <p class="vendedor-zona">
                                                    Zona de trabajo: <?php echo $zonaVnd; ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="vendedor-datos">
                                                <span><?php echo $expTexto; ?></span>
                                                <?php if ($vnd['calificacion'] !== null): ?>
                                                    <span>&#9733; <?php echo $vnd['calificacion']; ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="vendedor-acciones">
                                                <a
                                                    href="perfil-vendedor.php?id=<?php echo $idVnd; ?>"
                                                    class="btn btn-claro"
                                                >
                                                    Ver perfil
                                                </a>

                                                <?php if (!$sesionIniciada): ?>
                                                    <a
                                                        href="login.php"
                                                        class="btn btn-principal"
                                                    >
                                                        Iniciar sesión
                                                    </a>
                                                <?php elseif ($idRolSesion === 1): ?>
                                                    <a
                                                        href="../comprador/agendar.php?id=<?php echo $idMostrar; ?>"
                                                        class="btn btn-principal"
                                                    >
                                                        Solicitar visita
                                                    </a>
                                                <?php elseif ($idRolSesion === 2): ?>
                                                    <a
                                                        href="../vendedor/index.php"
                                                        class="btn btn-principal"
                                                    >
                                                        Ir a mi panel
                                                    </a>
                                                <?php elseif ($idRolSesion === 3): ?>
                                                    <a
                                                        href="../admin/dashboard.php"
                                                        class="btn btn-principal"
                                                    >
                                                        Ir al panel administrativo
                                                    </a>
                                                <?php endif; ?>
                                            </div>

                                        </div>

                                    </article>

                                <?php endforeach; ?>

                            </div>

                        <?php else: ?>

                            <p class="texto-intro">
                                Este inmueble no tiene vendedores asignados actualmente.
                            </p>

                        <?php endif; ?>

                        <?php if (!$sesionIniciada): ?>
                            <p class="aviso-visitante">
                                Debes iniciar sesión como comprador para seleccionar
                                un vendedor y agendar una cita.
                            </p>
                        <?php elseif ($idRolSesion === 1): ?>
                            <p class="aviso-visitante">
                                Selecciona un vendedor y agenda una cita para visitar
                                este inmueble.
                            </p>
                        <?php elseif ($idRolSesion === 2): ?>
                            <p class="aviso-visitante">
                                Estás consultando esta propiedad con una cuenta de vendedor.
                            </p>
                        <?php elseif ($idRolSesion === 3): ?>
                            <p class="aviso-visitante">
                                Estás consultando esta propiedad con una cuenta administrativa.
                            </p>
                        <?php endif; ?>

                    </article>

                    <!-- COMENTARIOS -->
                    <article class="detalle-bloque">

                        <div class="titulo-con-accion">
                            <div>
                                <p class="etiqueta etiqueta-oscura">Opiniones</p>
                                <h2>Comentarios del inmueble</h2>
                            </div>
                        </div>

                        <div class="comentarios-lista">
                            <?php if (empty($comentarios)): ?>
                                <p class="detalle-sin-datos">
                                    Todavía no hay comentarios para este inmueble.
                                </p>
                            <?php else: ?>
                                <?php foreach ($comentarios as $com): ?>
                                    <div class="comentario-item" style="border-bottom:1px solid #eee;padding:16px 0;">
                                        <p style="font-weight:600;margin:0 0 4px;">
                                            <?php echo htmlspecialchars(
                                                $com['nombre'] . ' ' . $com['apellido'],
                                                ENT_QUOTES, 'UTF-8'
                                            ); ?>
                                        </p>
                                        <p style="color:#888;font-size:13px;margin:0 0 8px;">
                                            <?php echo htmlspecialchars(
                                                formatearFechaEs($com['fecha_comentario']),
                                                ENT_QUOTES, 'UTF-8'
                                            ); ?>
                                        </p>
                                        <p style="margin:0;">
                                            <?php echo htmlspecialchars(
                                                $com['contenido'],
                                                ENT_QUOTES, 'UTF-8'
                                            ); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                    </article>

                </div>

                <!-- RESUMEN LATERAL -->
                <aside class="detalle-resumen">

                    <div class="detalle-resumen-card">

                        <span class="estado-inmueble estado-estatico">Disponible</span>

                        <h2>¿Te interesa este inmueble?</h2>

                        <?php if (!$sesionIniciada): ?>
                            <p>
                                Inicia sesión como comprador para elegir un vendedor
                                y solicitar una cita.
                            </p>

                            <a href="login.php" class="btn btn-principal btn-completo">
                                Iniciar sesión
                            </a>

                            <a href="registro-comprador.php" class="btn btn-secundario btn-completo">
                                Crear cuenta de comprador
                            </a>
                        <?php elseif ($idRolSesion === 1): ?>
                            <p>
                                Ya puedes elegir un vendedor y solicitar una cita
                                para conocer este inmueble.
                            </p>

                            <a
                                href="../comprador/agendar.php?id=<?php echo $idMostrar; ?>"
                                class="btn btn-principal btn-completo"
                            >
                                Solicitar visita
                            </a>

                            <a href="../comprador/index.php" class="btn btn-secundario btn-completo">
                                Ir a mi panel
                            </a>
                        <?php elseif ($idRolSesion === 2): ?>
                            <p>
                                Estás viendo esta propiedad con una cuenta de vendedor.
                            </p>

                            <a href="../vendedor/index.php" class="btn btn-principal btn-completo">
                                Ir a mi panel de vendedor
                            </a>
                        <?php elseif ($idRolSesion === 3): ?>
                            <p>
                                Estás viendo esta propiedad con una cuenta administrativa.
                            </p>

                            <a href="../admin/dashboard.php" class="btn btn-principal btn-completo">
                                Ir al panel administrativo
                            </a>
                        <?php endif; ?>

                        <div class="resumen-separador"></div>

                        <div class="resumen-dato">
                            <span>ID del inmueble</span>
                            <strong>#<?php echo $idMostrar; ?></strong>
                        </div>

                        <div class="resumen-dato">
                            <span>Condición</span>
                            <strong><?php echo $condicion; ?></strong>
                        </div>

                        <div class="resumen-dato">
                            <span>Publicado</span>
                            <strong><?php echo $fechaPublicada; ?></strong>
                        </div>

                    </div>

                </aside>

            </div>

        </div>
    </section>

<?php endif; ?>

</main>

<script>
(function () {
    var principal  = document.getElementById('foto-principal');
    var miniaturas = document.querySelectorAll('.miniatura');

    if (!principal || miniaturas.length < 2) return;

    miniaturas.forEach(function (btn) {
        btn.addEventListener('click', function () {
            principal.src = this.dataset.src;
            miniaturas.forEach(function (b) { b.classList.remove('activa'); });
            this.classList.add('activa');
        });
    });
}());
</script>

<?php
include('includes/footer.php');
?>
