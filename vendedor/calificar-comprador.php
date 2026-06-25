<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idVendedor = (int) $_SESSION['id_perfil'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Detectar si se recibe una cita específica vía GET ───────────────────────

$idCitaFiltro = filter_input(INPUT_GET, 'id_cita', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

// ─── Cargar citas realizadas sin calificación de comprador ────────────────────

$pendientes = [];

try {
    $sql = '
        SELECT
            c.id_cita,
            c.fecha_inicio,
            c.id_comprador,
            u.nombre    AS nombre_comprador,
            u.apellido  AS apellido_comprador,
            i.titulo    AS titulo_inmueble
        FROM Cita c
        INNER JOIN Comprador co ON co.id_comprador = c.id_comprador
        INNER JOIN Usuario   u  ON u.id_usuario   = co.id_usuario
        INNER JOIN Inmueble  i  ON i.id_inmueble  = c.id_inmueble
        LEFT  JOIN CalificacionComprador cc ON cc.id_cita = c.id_cita
        WHERE c.id_vendedor = ?
          AND c.id_estado_cita = 5
          AND cc.id_calificacion_comprador IS NULL
    ';

    $params = [$idVendedor];
    $types  = 'i';

    if ($idCitaFiltro !== null && $idCitaFiltro !== false) {
        $sql    .= ' AND c.id_cita = ?';
        $params[] = $idCitaFiltro;
        $types   .= 'i';
    }

    $sql .= ' ORDER BY c.fecha_inicio DESC';

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $pendientes = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar las citas por calificar.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'calificar-comprador.php';
$tituloPagina = 'Calificar comprador | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <h2>Evaluar Comprador</h2>
            <p>Califica la experiencia de la visita con cada comprador.</p>
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
                <h3 style="color:#666;font-weight:normal;">No hay compradores pendientes de calificar.</h3>
                <p style="margin-top:15px;">
                    Podrás calificar a un comprador después de que la cita sea marcada como realizada.
                </p>
                <a href="citas.php" class="btn btn-secundario" style="margin-top:20px;">
                    Ver mis citas
                </a>
            </div>

        <?php else: ?>

            <?php foreach ($pendientes as $item): ?>
                <?php
                $comprador  = htmlspecialchars(
                    $item['nombre_comprador'] . ' ' . $item['apellido_comprador'],
                    ENT_QUOTES, 'UTF-8'
                );
                $inmueble   = htmlspecialchars($item['titulo_inmueble'], ENT_QUOTES, 'UTF-8');
                $fechaCita  = date('d/m/Y', strtotime($item['fecha_inicio']));
                $idCita     = (int) $item['id_cita'];
                ?>

                <div class="card" style="max-width:600px;margin:0 auto 30px;padding:30px;">

                    <div style="display:flex;align-items:center;gap:15px;margin-bottom:25px;border-bottom:1px solid var(--color-borde);padding-bottom:15px;">
                        <div style="width:60px;height:60px;background:var(--color-borde);border-radius:50%;display:grid;place-items:center;font-size:24px;flex-shrink:0;">
                            👤
                        </div>
                        <div>
                            <h3 style="margin:0;font-size:18px;color:var(--color-oscuro);">
                                <?php echo $comprador; ?>
                            </h3>
                            <p style="margin:4px 0 0;font-size:14px;color:var(--color-texto);">
                                Visita a: <?php echo $inmueble; ?>
                            </p>
                            <p style="margin:4px 0 0;font-size:13px;color:#999;">
                                Cita realizada el <?php echo $fechaCita; ?>
                            </p>
                        </div>
                    </div>

                    <form class="formulario" action="../procesos/procesar-calificar-comprador.php" method="POST">

                        <input type="hidden" name="id_cita" value="<?php echo $idCita; ?>">

                        <div>
                            <label for="puntuacion_<?php echo $idCita; ?>">
                                ¿Cómo calificarías al comprador?
                                <span style="color:#e94b27;">*</span>
                            </label>
                            <select id="puntuacion_<?php echo $idCita; ?>" name="puntuacion" required>
                                <option value="">-- Selecciona una opción --</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                <option value="4">⭐⭐⭐⭐ Bueno</option>
                                <option value="3">⭐⭐⭐ Regular</option>
                                <option value="2">⭐⭐ Malo</option>
                                <option value="1">⭐ Pésimo</option>
                            </select>
                        </div>

                        <div style="margin-top:15px;">
                            <label for="comentario_<?php echo $idCita; ?>">
                                Comentario sobre el comprador (opcional)
                            </label>
                            <textarea id="comentario_<?php echo $idCita; ?>"
                                      name="comentario"
                                      placeholder="¿Llegó a tiempo? ¿Mostró interés genuino?..."></textarea>
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
