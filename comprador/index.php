<?php

require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

// ─── Variables de sesión seguras ─────────────────────────────────────────────

$idComprador = (int) $_SESSION['id_perfil'];   // id_comprador en Comprador
$idUsuario   = (int) $_SESSION['id_usuario'];
$nombreUser  = htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8');

// ─── Datos del perfil del comprador ──────────────────────────────────────────

$zonaInteres = '';

try {
    $s = mysqli_prepare($conexion, '
        SELECT zona_interes
        FROM Comprador
        WHERE id_comprador = ?
        LIMIT 1
    ');
    mysqli_stmt_bind_param($s, 'i', $idComprador);
    mysqli_stmt_execute($s);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
    mysqli_stmt_close($s);

    $zonaInteres = htmlspecialchars($row['zona_interes'] ?? '', ENT_QUOTES, 'UTF-8');

} catch (mysqli_sql_exception $e) {
    // Sin datos de perfil, continúa normalmente
}

// ─── Métricas del comprador ───────────────────────────────────────────────────

$totalCitas          = 0;
$totalComentarios    = 0;
$totalCalifInmueble  = 0;
$totalCalifVendedor  = 0;

try {
    // Total de citas
    $s = mysqli_prepare($conexion, 'SELECT COUNT(*) FROM Cita WHERE id_comprador = ?');
    mysqli_stmt_bind_param($s, 'i', $idComprador);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalCitas);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    // Total de comentarios realizados
    $s = mysqli_prepare($conexion, 'SELECT COUNT(*) FROM Comentario WHERE id_usuario = ?');
    mysqli_stmt_bind_param($s, 'i', $idUsuario);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalComentarios);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    // Total de calificaciones de inmuebles
    $s = mysqli_prepare($conexion, 'SELECT COUNT(*) FROM CalificacionInmueble WHERE id_comprador = ?');
    mysqli_stmt_bind_param($s, 'i', $idComprador);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalCalifInmueble);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    // Total de calificaciones de vendedores
    $s = mysqli_prepare($conexion, 'SELECT COUNT(*) FROM CalificacionVendedor WHERE id_comprador = ?');
    mysqli_stmt_bind_param($s, 'i', $idComprador);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalCalifVendedor);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

} catch (mysqli_sql_exception $e) {
    // Las métricas quedan en 0 si la consulta falla
}

// ─── Próximas citas ───────────────────────────────────────────────────────────

$proximasCitas = [];

try {
    $s = mysqli_prepare($conexion, '
        SELECT
            c.id_cita,
            c.fecha_inicio,
            i.titulo       AS titulo_inmueble,
            u.nombre       AS nombre_vendedor,
            u.apellido     AS apellido_vendedor,
            ec.nombre_estado AS estado_cita
        FROM Cita c
        INNER JOIN Inmueble  i  ON i.id_inmueble  = c.id_inmueble
        INNER JOIN Vendedor  v  ON v.id_vendedor  = c.id_vendedor
        INNER JOIN Usuario   u  ON u.id_usuario   = v.id_usuario
        INNER JOIN EstadoCita ec ON ec.id_estado_cita = c.id_estado_cita
        WHERE c.id_comprador = ?
          AND c.fecha_inicio  >= NOW()
          AND ec.nombre_estado IN (\'pendiente\', \'aceptada\')
        ORDER BY c.fecha_inicio ASC
        LIMIT 3
    ');
    mysqli_stmt_bind_param($s, 'i', $idComprador);
    mysqli_stmt_execute($s);
    $proximasCitas = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
    mysqli_stmt_close($s);

} catch (mysqli_sql_exception $e) {
    $proximasCitas = [];
}

// ─── Inmuebles publicados disponibles ────────────────────────────────────────

$inmuebles = [];

try {
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
            i.estacionamientos,
            c.nombre_categoria,
            (
                SELECT fi.url_foto
                FROM FotoInmueble AS fi
                WHERE fi.id_inmueble = i.id_inmueble
                ORDER BY fi.principal DESC, fi.id_foto ASC
                LIMIT 1
            ) AS url_foto
        FROM Inmueble i
        INNER JOIN CategoriaInmueble c ON c.id_categoria = i.id_categoria
        WHERE i.id_estado_publicacion = 3
        ORDER BY COALESCE(i.fecha_publicacion, i.fecha_registro) DESC
        LIMIT 6
    ');
    mysqli_stmt_execute($s);
    $inmuebles = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
    mysqli_stmt_close($s);

} catch (mysqli_sql_exception $e) {
    $inmuebles = [];
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'index.php';
$tituloPagina = 'Panel del comprador | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <!-- Bienvenida -->
        <div class="titulo-seccion">
            <h2>Bienvenido, <?php echo $nombreUser; ?></h2>
            <p>
                <?php if ($zonaInteres !== ''): ?>
                    Tu zona de interés: <strong><?php echo $zonaInteres; ?></strong>.
                <?php else: ?>
                    Explora nuestro catálogo y encuentra tu próximo hogar.
                <?php endif; ?>
            </p>
        </div>

        <!-- Métricas rápidas -->
        <div class="detalle-caracteristicas" style="margin-bottom:40px;">
            <div class="caracteristica-detalle">
                <strong><?php echo (int) $totalCitas; ?></strong>
                <span>Citas totales</span>
            </div>
            <div class="caracteristica-detalle">
                <strong><?php echo count($proximasCitas); ?></strong>
                <span>Próximas citas</span>
            </div>
            <div class="caracteristica-detalle">
                <strong><?php echo (int) $totalComentarios; ?></strong>
                <span>Comentarios</span>
            </div>
            <div class="caracteristica-detalle">
                <strong><?php echo (int) ($totalCalifInmueble + $totalCalifVendedor); ?></strong>
                <span>Calificaciones</span>
            </div>
        </div>

        <!-- Próximas citas -->
        <?php if (!empty($proximasCitas)): ?>
            <div class="titulo-seccion" style="margin-bottom:16px;">
                <p class="etiqueta etiqueta-oscura">Agenda</p>
                <h2>Próximas citas</h2>
            </div>

            <div style="display:grid;gap:14px;margin-bottom:40px;">
                <?php foreach ($proximasCitas as $cita): ?>
                    <div class="detalle-bloque" style="padding:20px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
                            <div>
                                <strong>
                                    <?php echo htmlspecialchars($cita['titulo_inmueble'], ENT_QUOTES, 'UTF-8'); ?>
                                </strong>
                                <p style="color:#777;font-size:13px;margin-top:4px;">
                                    Vendedor:
                                    <?php echo htmlspecialchars(
                                        $cita['nombre_vendedor'] . ' ' . $cita['apellido_vendedor'],
                                        ENT_QUOTES, 'UTF-8'
                                    ); ?>
                                </p>
                            </div>
                            <div style="text-align:right;">
                                <strong style="color:var(--color-oscuro);">
                                    <?php
                                    $ts = strtotime($cita['fecha_inicio']);
                                    $meses = ['','enero','febrero','marzo','abril','mayo','junio',
                                              'julio','agosto','septiembre','octubre','noviembre','diciembre'];
                                    echo date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)]
                                       . ' de ' . date('Y', $ts) . ', ' . date('H:i', $ts);
                                    ?>
                                </strong>
                                <p style="color:#777;font-size:13px;margin-top:4px;">
                                    <?php echo htmlspecialchars(ucfirst($cita['estado_cita']), ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Inmuebles disponibles -->
        <div class="titulo-seccion">
            <p class="etiqueta">Propiedades</p>
            <h2>Inmuebles disponibles</h2>
        </div>

        <?php if (empty($inmuebles)): ?>
            <p class="catalogo-sin-resultados">
                No hay propiedades publicadas actualmente.
                <a href="../public/catalogo.php">Volver al catálogo</a>.
            </p>
        <?php else: ?>

            <div class="grid-3">

                <?php foreach ($inmuebles as $inm): ?>
                    <?php
                    $titulo    = htmlspecialchars($inm['titulo'],           ENT_QUOTES, 'UTF-8');
                    $cat       = htmlspecialchars($inm['nombre_categoria'], ENT_QUOTES, 'UTF-8');
                    $partes    = array_filter([
                        $inm['ciudad']     ?? '',
                        $inm['estado_geo'] ?? '',
                    ]);
                    $ubicacion = htmlspecialchars(implode(', ', $partes), ENT_QUOTES, 'UTF-8');
                    $precio    = $inm['precio'] !== null
                        ? '$' . number_format((float) $inm['precio'], 2)
                          . ' ' . htmlspecialchars($inm['moneda'] ?? 'MXN', ENT_QUOTES, 'UTF-8')
                        : 'Precio a consultar';
                    $idInm     = (int) $inm['id_inmueble'];
                    ?>

                    <article class="card card-inmueble">

                        <?php if (!empty($inm['url_foto'])): ?>
                            <?php
                            $rutaFoto = ltrim((string) $inm['url_foto'], '/');

                            if (str_starts_with($rutaFoto, 'public/')) {
                                $rutaFoto = substr($rutaFoto, strlen('public/'));
                            }

                            $rutaFoto = '../public/' . $rutaFoto;
                            ?>
                            <img
                                src="<?php echo htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo $titulo; ?>"
                            >
                        <?php else: ?>
                            <div class="sin-foto" aria-label="Sin fotografía disponible"></div>
                        <?php endif; ?>

                        <div class="card-contenido">
                            <span class="badge"><?php echo $cat; ?></span>

                            <h3><?php echo $titulo; ?></h3>

                            <p class="precio"><?php echo $precio; ?></p>

                            <?php if ($ubicacion !== ''): ?>
                                <p class="ubicacion"><?php echo $ubicacion; ?></p>
                            <?php endif; ?>

                            <div class="caracteristicas">
                                <?php if ($inm['recamaras'] !== null): ?>
                                    <span><?php echo (int) $inm['recamaras']; ?> recámaras</span>
                                <?php endif; ?>
                                <?php if ($inm['banos'] !== null): ?>
                                    <span><?php echo (float) $inm['banos']; ?> baños</span>
                                <?php endif; ?>
                                <?php if ($inm['estacionamientos'] !== null): ?>
                                    <span><?php echo (int) $inm['estacionamientos']; ?> estacionamientos</span>
                                <?php endif; ?>
                            </div>

                            <a
                                href="../public/detalle-inmueble.php?id=<?php echo $idInm; ?>"
                                class="btn btn-secundario btn-completo"
                            >
                                Ver detalle
                            </a>
                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
