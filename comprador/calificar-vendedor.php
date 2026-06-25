<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

$idComprador = (int) $_SESSION['id_perfil'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Citas realizadas sin calificación de vendedor ───────────────────────────

$pendientes = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            c.id_cita,
            c.fecha_inicio,
            v.id_vendedor,
            u.nombre    AS nombre_vendedor,
            u.apellido  AS apellido_vendedor,
            i.titulo    AS titulo_inmueble
        FROM Cita c
        INNER JOIN Vendedor v ON v.id_vendedor  = c.id_vendedor
        INNER JOIN Usuario  u ON u.id_usuario   = v.id_usuario
        INNER JOIN Inmueble i ON i.id_inmueble  = c.id_inmueble
        LEFT  JOIN CalificacionVendedor cv ON cv.id_cita = c.id_cita
        WHERE c.id_comprador = ?
          AND c.id_estado_cita = 5
          AND cv.id_calificacion_vendedor IS NULL
        ORDER BY c.fecha_inicio DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idComprador);
    mysqli_stmt_execute($stmt);
    $pendientes = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar los vendedores por calificar.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'calificar-vendedor.php';
$tituloPagina = 'Calificar vendedor | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <h2>Calificar Vendedor</h2>
            <p>Tu opinión es importante. Evalúa el servicio y la atención brindada por el asesor.</p>
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
                <h3 style="color:#666;font-weight:normal;">No tienes vendedores pendientes de calificar.</h3>
                <p style="margin-top:15px;">
                    Podrás calificar a un vendedor una vez que tu cita haya sido marcada como realizada.
                </p>
                <a href="citas.php" class="btn btn-secundario" style="margin-top:20px;">
                    Ver mis citas
                </a>
            </div>

        <?php else: ?>

            <?php foreach ($pendientes as $item): ?>
                <?php
                $nombreVend = htmlspecialchars(
                    $item['nombre_vendedor'] . ' ' . $item['apellido_vendedor'],
                    ENT_QUOTES, 'UTF-8'
                );
                $tituloInm  = htmlspecialchars($item['titulo_inmueble'], ENT_QUOTES, 'UTF-8');
                $fechaCita  = date('d/m/Y', strtotime($item['fecha_inicio']));
                ?>

                <div class="card" style="max-width:600px;margin:0 auto 30px;padding:30px;">

                    <div style="display:flex;align-items:center;gap:15px;margin-bottom:25px;border-bottom:1px solid var(--color-borde);padding-bottom:15px;">
                        <div style="width:60px;height:60px;background:var(--color-borde);border-radius:50%;display:grid;place-items:center;font-size:24px;flex-shrink:0;">
                            👤
                        </div>
                        <div>
                            <h3 style="margin:0;font-size:18px;color:var(--color-oscuro);">
                                <?php echo $nombreVend; ?>
                            </h3>
                            <p style="margin:4px 0 0;font-size:14px;color:var(--color-texto);">
                                Inmueble: <?php echo $tituloInm; ?>
                            </p>
                            <p style="margin:4px 0 0;font-size:13px;color:#999;">
                                Cita realizada el <?php echo $fechaCita; ?>
                            </p>
                        </div>
                    </div>

                    <form class="formulario" action="../procesos/procesar-calificacion-vendedor.php" method="POST">

                        <input type="hidden" name="id_cita"
                               value="<?php echo (int) $item['id_cita']; ?>">

                        <div>
                            <label for="puntuacion_<?php echo (int) $item['id_cita']; ?>">
                                ¿Cómo calificarías la atención recibida?
                                <span style="color:#e94b27;">*</span>
                            </label>
                            <select id="puntuacion_<?php echo (int) $item['id_cita']; ?>"
                                    name="puntuacion" required>
                                <option value="">-- Selecciona una opción --</option>
                                <option value="5">⭐⭐⭐⭐⭐ (Excelente atención)</option>
                                <option value="4">⭐⭐⭐⭐ (Buena)</option>
                                <option value="3">⭐⭐⭐ (Regular)</option>
                                <option value="2">⭐⭐ (Mala)</option>
                                <option value="1">⭐ (Pésimo servicio)</option>
                            </select>
                        </div>

                        <div style="margin-top:15px;">
                            <label for="comentario_<?php echo (int) $item['id_cita']; ?>">
                                Escribe tu reseña (opcional)
                            </label>
                            <textarea id="comentario_<?php echo (int) $item['id_cita']; ?>"
                                      name="comentario"
                                      placeholder="Cuéntanos los detalles de tu experiencia con este asesor..."></textarea>
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
