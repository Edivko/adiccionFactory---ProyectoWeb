<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

$idComprador = (int) $_SESSION['id_perfil'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Citas realizadas sin calificación de inmueble ───────────────────────────

$pendientes = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            c.id_cita,
            c.fecha_inicio,
            i.id_inmueble,
            i.titulo,
            i.ciudad,
            i.estado        AS estado_geo,
            (SELECT url_foto
             FROM FotoInmueble
             WHERE id_inmueble = i.id_inmueble AND principal = TRUE
             LIMIT 1)       AS url_foto
        FROM Cita c
        INNER JOIN Inmueble i ON i.id_inmueble = c.id_inmueble
        LEFT  JOIN CalificacionInmueble ci ON ci.id_cita = c.id_cita
        WHERE c.id_comprador = ?
          AND c.id_estado_cita = 5
          AND ci.id_calificacion_inmueble IS NULL
        ORDER BY c.fecha_inicio DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idComprador);
    mysqli_stmt_execute($stmt);
    $pendientes = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar los inmuebles por calificar.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'calificar-inmueble.php';
$tituloPagina = 'Calificar propiedad | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <h2>Calificar Propiedad</h2>
            <p>Comparte tu reseña sobre las condiciones físicas y la ubicación de cada inmueble visitado.</p>
        </div>

        <?php if ($mensajeExito !== null): ?>
            <div class="mensaje-exito" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorGeneral !== null): ?>
            <div class="mensaje-error" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($pendientes)): ?>

            <div class="card" style="padding:40px;text-align:center;">
                <h3 style="color:#666;font-weight:normal;">No tienes inmuebles pendientes de calificar.</h3>
                <p style="margin-top:15px;">
                    Podrás calificar una propiedad una vez que tu cita haya sido marcada como realizada.
                </p>
                <a href="citas.php" class="btn btn-secundario" style="margin-top:20px;">
                    Ver mis citas
                </a>
            </div>

        <?php else: ?>

            <?php foreach ($pendientes as $item): ?>
                <?php
                $titulo    = htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8');
                $partes    = array_filter([$item['ciudad'] ?? '', $item['estado_geo'] ?? '']);
                $ubicacion = htmlspecialchars(implode(', ', $partes), ENT_QUOTES, 'UTF-8');
                $fechaCita = date('d/m/Y', strtotime($item['fecha_inicio']));
                ?>

                <div class="card" style="max-width:600px;margin:0 auto 30px;padding:30px;">

                    <div style="display:flex;align-items:center;gap:15px;margin-bottom:25px;border-bottom:1px solid var(--color-borde);padding-bottom:15px;">
                        <?php if (!empty($item['url_foto'])): ?>
                            <img src="<?php echo htmlspecialchars($item['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo $titulo; ?>"
                                 style="width:80px;height:60px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                        <?php else: ?>
                            <div style="width:80px;height:60px;background:#eee;border-radius:8px;display:grid;place-items:center;font-size:24px;flex-shrink:0;">
                                🏠
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3 style="margin:0;font-size:18px;color:var(--color-oscuro);">
                                <?php echo $titulo; ?>
                            </h3>
                            <?php if ($ubicacion !== ''): ?>
                                <p style="margin:4px 0 0;font-size:14px;color:var(--color-texto);">
                                    <?php echo $ubicacion; ?>
                                </p>
                            <?php endif; ?>
                            <p style="margin:4px 0 0;font-size:13px;color:#999;">
                                Cita realizada el <?php echo $fechaCita; ?>
                            </p>
                        </div>
                    </div>

                    <form class="formulario" action="../procesos/procesar-calificacion-inmueble.php" method="POST">

                        <input type="hidden" name="id_cita"
                               value="<?php echo (int) $item['id_cita']; ?>">

                        <div>
                            <label for="puntuacion_<?php echo (int) $item['id_cita']; ?>">
                                ¿Qué calificación le das a la propiedad?
                                <span style="color:#e94b27;">*</span>
                            </label>
                            <select id="puntuacion_<?php echo (int) $item['id_cita']; ?>"
                                    name="puntuacion" required>
                                <option value="">-- Selecciona una opción --</option>
                                <option value="5">⭐⭐⭐⭐⭐ (Excelente estado y ubicación)</option>
                                <option value="4">⭐⭐⭐⭐ (Buen estado general)</option>
                                <option value="3">⭐⭐⭐ (Aceptable / regular)</option>
                                <option value="2">⭐⭐ (Requiere mantenimiento)</option>
                                <option value="1">⭐ (Malas condiciones)</option>
                            </select>
                        </div>

                        <div style="margin-top:15px;">
                            <label for="comentario_<?php echo (int) $item['id_cita']; ?>">
                                Escribe tus comentarios sobre el inmueble (opcional)
                            </label>
                            <textarea id="comentario_<?php echo (int) $item['id_cita']; ?>"
                                      name="comentario"
                                      placeholder="Ej. La zona es muy tranquila, pero hace falta impermeabilizar la azotea..."></textarea>
                        </div>

                        <div style="margin-top:25px;">
                            <button type="submit" class="btn btn-principal btn-completo">
                                Enviar calificación
                            </button>
                        </div>

                    </form>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
