<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idUsuario   = (int) $_SESSION['id_usuario'];
$idVendedor  = (int) $_SESSION['id_perfil'];
$nombreUser  = htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8');

// ─── Métricas ─────────────────────────────────────────────────────────────────

$totalInmuebles     = 0;
$citasPendientes    = 0;
$totalInmueblesPublicados = 0;
$calificacionProm   = null;

try {
    $s = mysqli_prepare($conexion,
        'SELECT COUNT(*) FROM Inmueble WHERE id_usuario_publicador = ?'
    );
    mysqli_stmt_bind_param($s, 'i', $idUsuario);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalInmuebles);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    $s = mysqli_prepare($conexion,
        'SELECT COUNT(*) FROM Inmueble
         WHERE id_usuario_publicador = ? AND id_estado_publicacion = 3'
    );
    mysqli_stmt_bind_param($s, 'i', $idUsuario);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalInmueblesPublicados);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    $s = mysqli_prepare($conexion,
        'SELECT COUNT(*) FROM Cita
         WHERE id_vendedor = ? AND id_estado_cita IN (1,2) AND fecha_inicio >= NOW()'
    );
    mysqli_stmt_bind_param($s, 'i', $idVendedor);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $citasPendientes);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    $s = mysqli_prepare($conexion,
        'SELECT ROUND(AVG(puntuacion), 1) FROM CalificacionVendedor WHERE id_vendedor = ?'
    );
    mysqli_stmt_bind_param($s, 'i', $idVendedor);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $calificacionProm);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

} catch (mysqli_sql_exception $e) {
    // Métricas en 0 si falla la consulta
}

// ─── Próximas citas ───────────────────────────────────────────────────────────

$proximasCitas = [];

try {
    $s = mysqli_prepare($conexion, '
        SELECT
            c.id_cita,
            c.fecha_inicio,
            ec.nombre_estado AS estado,
            i.titulo         AS titulo_inmueble,
            u.nombre         AS nombre_comprador,
            u.apellido       AS apellido_comprador
        FROM Cita c
        INNER JOIN Inmueble  i  ON i.id_inmueble  = c.id_inmueble
        INNER JOIN Comprador co ON co.id_comprador = c.id_comprador
        INNER JOIN Usuario   u  ON u.id_usuario   = co.id_usuario
        INNER JOIN EstadoCita ec ON ec.id_estado_cita = c.id_estado_cita
        WHERE c.id_vendedor = ?
          AND c.fecha_inicio >= NOW()
          AND c.id_estado_cita IN (1, 2)
        ORDER BY c.fecha_inicio ASC
        LIMIT 5
    ');
    mysqli_stmt_bind_param($s, 'i', $idVendedor);
    mysqli_stmt_execute($s);
    $proximasCitas = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
    mysqli_stmt_close($s);
} catch (mysqli_sql_exception $e) {
    $proximasCitas = [];
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'index.php';
$tituloPagina = 'Panel del vendedor | Adicción Factory Inmobiliaria';

$meses = [
    '', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
];

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Panel de Control</p>
            <h2>Bienvenido, <?php echo $nombreUser; ?></h2>
            <p>Gestiona tus inmuebles, atiende solicitudes de clientes y revisa tu reputación.</p>
        </div>

        <!-- Métricas -->
        <div class="detalle-caracteristicas" style="margin-bottom:40px;">
            <div class="caracteristica-detalle">
                <strong><?php echo (int) $totalInmuebles; ?></strong>
                <span>Inmuebles registrados</span>
            </div>
            <div class="caracteristica-detalle">
                <strong><?php echo (int) $totalInmueblesPublicados; ?></strong>
                <span>Publicados</span>
            </div>
            <div class="caracteristica-detalle">
                <strong><?php echo (int) $citasPendientes; ?></strong>
                <span>Citas próximas</span>
            </div>
            <div class="caracteristica-detalle">
                <strong><?php echo $calificacionProm !== null ? $calificacionProm . ' / 5' : '—'; ?></strong>
                <span>Calificación promedio</span>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="grid-3" style="margin-bottom:40px;">
            <article class="card">
                <div class="card-contenido">
                    <h3>Mis Inmuebles</h3>
                    <p>Administra y publica tus propiedades.</p>
                    <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;">
                        <a href="mis-inmuebles.php" class="btn btn-principal btn-completo">Ver mis inmuebles</a>
                        <a href="agregar-inmueble.php" class="btn btn-claro btn-completo">Agregar inmueble</a>
                    </div>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <h3>Citas Solicitadas</h3>
                    <p>Revisa y gestiona las solicitudes de visita de compradores.</p>
                    <div style="margin-top:20px;">
                        <a href="citas.php" class="btn btn-secundario btn-completo">Gestionar citas</a>
                    </div>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <h3>Mi Perfil Público</h3>
                    <p>Mantén actualizada tu información para generar confianza.</p>
                    <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;">
                        <a href="perfil.php" class="btn btn-claro btn-completo">Ver mi perfil</a>
                        <a href="editar-perfil.php" class="btn btn-claro btn-completo">Editar perfil</a>
                    </div>
                </div>
            </article>
        </div>

        <!-- Próximas citas -->
        <?php if (!empty($proximasCitas)): ?>
            <div class="titulo-seccion" style="margin-bottom:16px;">
                <p class="etiqueta">Agenda</p>
                <h2>Próximas citas</h2>
            </div>

            <div style="display:grid;gap:14px;margin-bottom:40px;">
                <?php foreach ($proximasCitas as $cita): ?>
                    <?php
                    $ts = strtotime($cita['fecha_inicio']);
                    $fechaStr = date('j', $ts) . ' de ' . $meses[(int) date('n', $ts)]
                              . ' de ' . date('Y', $ts) . ', ' . date('H:i', $ts);
                    ?>
                    <div class="detalle-bloque" style="padding:20px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                            <div>
                                <strong><?php echo htmlspecialchars($cita['titulo_inmueble'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <p style="color:#666;font-size:13px;margin-top:4px;">
                                    Comprador: <?php echo htmlspecialchars(
                                        $cita['nombre_comprador'] . ' ' . $cita['apellido_comprador'],
                                        ENT_QUOTES, 'UTF-8'
                                    ); ?>
                                </p>
                            </div>
                            <div style="text-align:right;">
                                <strong><?php echo $fechaStr; ?></strong>
                                <p style="color:#666;font-size:13px;margin-top:4px;">
                                    <?php echo htmlspecialchars(ucfirst($cita['estado']), ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
