<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idVendedor = (int) $_SESSION['id_perfil'];

// ─── Resumen de calificaciones (AVG calculado, nunca almacenado) ──────────────

$promedio   = null;
$totalCal   = 0;

try {
    $s = mysqli_prepare($conexion,
        'SELECT ROUND(AVG(puntuacion), 1), COUNT(*) FROM CalificacionVendedor WHERE id_vendedor = ?'
    );
    mysqli_stmt_bind_param($s, 'i', $idVendedor);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $promedio, $totalCal);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);
} catch (mysqli_sql_exception $e) {
    // Sin calificaciones
}

// ─── Lista de calificaciones recibidas ────────────────────────────────────────

$calificaciones = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            cv.puntuacion,
            cv.comentario,
            cv.fecha_calificacion,
            u.nombre    AS nombre_comprador,
            u.apellido  AS apellido_comprador,
            i.titulo    AS titulo_inmueble
        FROM CalificacionVendedor cv
        INNER JOIN Comprador co ON co.id_comprador = cv.id_comprador
        INNER JOIN Usuario   u  ON u.id_usuario   = co.id_usuario
        INNER JOIN Cita      c  ON c.id_cita       = cv.id_cita
        INNER JOIN Inmueble  i  ON i.id_inmueble   = c.id_inmueble
        WHERE cv.id_vendedor = ?
        ORDER BY cv.fecha_calificacion DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idVendedor);
    mysqli_stmt_execute($stmt);
    $calificaciones = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $calificaciones = [];
}

// ─── Comentarios de texto recibidos (de Comentario, visibles) ─────────────────

$comentariosTexto = [];

try {
    $stmtCo = mysqli_prepare($conexion, '
        SELECT
            co.contenido,
            co.fecha_comentario,
            u.nombre    AS nombre_comprador,
            u.apellido  AS apellido_comprador
        FROM Comentario co
        INNER JOIN Usuario u ON u.id_usuario = co.id_usuario
        WHERE co.id_vendedor = ? AND co.id_estado_comentario = 2
        ORDER BY co.fecha_comentario DESC
    ');
    mysqli_stmt_bind_param($stmtCo, 'i', $idVendedor);
    mysqli_stmt_execute($stmtCo);
    $comentariosTexto = mysqli_fetch_all(mysqli_stmt_get_result($stmtCo), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtCo);
} catch (mysqli_sql_exception $e) {
    $comentariosTexto = [];
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'reputacion.php';
$tituloPagina = 'Mi reputación | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';

function estrellas(int $n): string {
    return str_repeat('⭐', $n) . str_repeat('☆', 5 - $n);
}
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Feedback</p>
            <h2>Calificaciones y Comentarios Recibidos</h2>
        </div>

        <!-- Tarjeta resumen -->
        <div class="card detalle-bloque" style="padding:30px;margin-bottom:40px;text-align:center;">
            <?php if ($totalCal > 0 && $promedio !== null): ?>
                <p style="font-size:48px;margin:0;">
                    <?php echo str_repeat('⭐', (int) round((float) $promedio)); ?>
                </p>
                <p style="font-size:32px;font-weight:700;color:var(--color-oscuro);margin:8px 0 4px;">
                    <?php echo htmlspecialchars((string) $promedio, ENT_QUOTES, 'UTF-8'); ?> / 5
                </p>
                <p style="color:#666;">
                    Basado en <?php echo (int) $totalCal; ?> calificacion<?php echo $totalCal !== 1 ? 'es' : ''; ?> de compradores
                </p>
            <?php else: ?>
                <p style="font-size:32px;margin:0;">—</p>
                <p style="color:#666;margin-top:8px;">Sin calificaciones</p>
            <?php endif; ?>
        </div>

        <!-- Lista de calificaciones -->
        <?php if (!empty($calificaciones)): ?>
            <div class="titulo-seccion" style="margin-bottom:16px;">
                <p class="etiqueta">Reseñas</p>
                <h2>Calificaciones</h2>
            </div>

            <div class="grid-2" style="margin-bottom:40px;">
                <?php foreach ($calificaciones as $cal): ?>
                    <article class="card">
                        <div class="card-contenido">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:10px;">
                                <div>
                                    <h3 style="margin:0;font-size:16px;">
                                        <?php echo htmlspecialchars(
                                            $cal['nombre_comprador'] . ' ' . $cal['apellido_comprador'],
                                            ENT_QUOTES, 'UTF-8'
                                        ); ?>
                                    </h3>
                                    <p style="font-size:13px;color:#888;margin-top:4px;">
                                        <?php echo htmlspecialchars($cal['titulo_inmueble'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                                <span style="font-size:18px;white-space:nowrap;">
                                    <?php echo estrellas((int) $cal['puntuacion']); ?>
                                </span>
                            </div>

                            <?php if (!empty($cal['fecha_calificacion'])): ?>
                                <p style="font-size:13px;color:#aaa;margin-bottom:8px;">
                                    <?php echo date('d/m/Y', strtotime($cal['fecha_calificacion'])); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($cal['comentario'])): ?>
                                <p style="color:#4a4a4a;">
                                    "<?php echo htmlspecialchars($cal['comentario'], ENT_QUOTES, 'UTF-8'); ?>"
                                </p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Comentarios de texto (visibles) -->
        <?php if (!empty($comentariosTexto)): ?>
            <div class="titulo-seccion" style="margin-bottom:16px;">
                <p class="etiqueta">Opiniones</p>
                <h2>Comentarios</h2>
            </div>

            <div class="grid-2">
                <?php foreach ($comentariosTexto as $co): ?>
                    <article class="card">
                        <div class="card-contenido">
                            <h3 style="margin:0 0 6px;font-size:16px;">
                                <?php echo htmlspecialchars(
                                    $co['nombre_comprador'] . ' ' . $co['apellido_comprador'],
                                    ENT_QUOTES, 'UTF-8'
                                ); ?>
                            </h3>
                            <?php if (!empty($co['fecha_comentario'])): ?>
                                <p style="font-size:13px;color:#aaa;margin-bottom:10px;">
                                    <?php echo date('d/m/Y', strtotime($co['fecha_comentario'])); ?>
                                </p>
                            <?php endif; ?>
                            <p style="color:#4a4a4a;">
                                "<?php echo htmlspecialchars($co['contenido'], ENT_QUOTES, 'UTF-8'); ?>"
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($calificaciones) && empty($comentariosTexto)): ?>
            <div class="card" style="padding:40px;text-align:center;">
                <h3 style="color:#666;font-weight:normal;">Sin calificaciones</h3>
                <p style="margin-top:15px;">
                    Cuando un comprador te evalúe después de una visita, sus comentarios aparecerán aquí.
                </p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
