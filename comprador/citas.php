<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

$idComprador = (int) $_SESSION['id_perfil'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Cargar citas del comprador ───────────────────────────────────────────────

$citas = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            c.id_cita,
            c.fecha_inicio,
            c.comentario_solicitud,
            ec.nombre_estado     AS estado,
            c.id_estado_cita,
            i.id_inmueble,
            i.titulo             AS titulo_inmueble,
            i.ciudad,
            i.estado             AS estado_geo,
            (SELECT url_foto
             FROM FotoInmueble
             WHERE id_inmueble = i.id_inmueble AND principal = TRUE
             LIMIT 1)            AS url_foto,
            u.nombre             AS nombre_vendedor,
            u.apellido           AS apellido_vendedor
        FROM Cita c
        INNER JOIN Inmueble  i  ON i.id_inmueble   = c.id_inmueble
        INNER JOIN Vendedor  v  ON v.id_vendedor    = c.id_vendedor
        INNER JOIN Usuario   u  ON u.id_usuario     = v.id_usuario
        INNER JOIN EstadoCita ec ON ec.id_estado_cita = c.id_estado_cita
        WHERE c.id_comprador = ?
        ORDER BY c.fecha_inicio DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idComprador);
    mysqli_stmt_execute($stmt);
    $citas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar las citas en este momento.';
}

// ─── Colores por estado ───────────────────────────────────────────────────────

function colorEstadoCita(int $id): string {
    return match ($id) {
        1       => 'background-color:#ffde59;color:#211d1d;',  // pendiente
        2       => 'background-color:#4caf50;color:#fff;',     // aceptada
        3       => 'background-color:#e94b27;color:#fff;',     // rechazada
        4       => 'background-color:#9e9e9e;color:#fff;',     // cancelada
        5       => 'background-color:#2196f3;color:#fff;',     // realizada
        default => '',
    };
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'citas.php';
$tituloPagina = 'Mis citas | Adicción Factory Inmobiliaria';

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
            <h2>Mis Citas Programadas</h2>
            <p>Aquí puedes ver el estado de las visitas que tienes agendadas.</p>
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

        <?php if (empty($citas)): ?>

            <div class="card" style="padding:40px;text-align:center;">
                <h3 style="color:#666;font-weight:normal;">Aún no tienes citas agendadas.</h3>
                <p style="margin-top:15px;">
                    Explora el catálogo y agenda una cita para conocer tu próximo hogar.
                </p>
                <a href="agendar.php" class="btn btn-secundario" style="margin-top:20px;">
                    Agendar cita
                </a>
            </div>

        <?php else: ?>

            <?php foreach ($citas as $cita): ?>
                <?php
                $idEstado   = (int) $cita['id_estado_cita'];
                $titulo     = htmlspecialchars($cita['titulo_inmueble'], ENT_QUOTES, 'UTF-8');
                $vendedor   = htmlspecialchars(
                    $cita['nombre_vendedor'] . ' ' . $cita['apellido_vendedor'],
                    ENT_QUOTES, 'UTF-8'
                );
                $estado     = htmlspecialchars(ucfirst($cita['estado']), ENT_QUOTES, 'UTF-8');
                $colorStyle = colorEstadoCita($idEstado);

                $ts    = strtotime($cita['fecha_inicio']);
                $fecha = date('j', $ts) . ' de ' . $meses[(int) date('n', $ts)]
                       . ' de ' . date('Y', $ts);
                $hora  = date('H:i', $ts);

                $partes   = array_filter([$cita['ciudad'] ?? '', $cita['estado_geo'] ?? '']);
                $ubicacion = implode(', ', $partes);

                $cancelable = in_array($idEstado, [1, 2], true); // pendiente o aceptada
                ?>

                <div class="card" style="display:flex;gap:25px;align-items:flex-start;padding:20px;flex-wrap:wrap;margin-bottom:20px;">

                    <?php if (!empty($cita['url_foto'])): ?>
                        <img src="<?php echo htmlspecialchars($cita['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo $titulo; ?>"
                             style="width:200px;height:150px;object-fit:cover;border-radius:10px;flex-shrink:0;">
                    <?php else: ?>
                        <div style="width:200px;height:150px;background:#eee;border-radius:10px;display:grid;place-items:center;color:#999;font-size:36px;flex-shrink:0;">
                            🏠
                        </div>
                    <?php endif; ?>

                    <div style="flex:1;min-width:200px;">
                        <span class="badge" style="<?php echo $colorStyle; ?>">
                            <?php echo $estado; ?>
                        </span>

                        <h3 style="margin-top:10px;color:#211d1d;"><?php echo $titulo; ?></h3>

                        <?php if ($ubicacion !== ''): ?>
                            <p style="color:#666;font-size:14px;margin-top:2px;">
                                <?php echo htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        <?php endif; ?>

                        <div style="margin-top:10px;color:#4a4a4a;">
                            <p><strong>Vendedor:</strong> <?php echo $vendedor; ?></p>
                            <p><strong>Fecha de visita:</strong> <?php echo $fecha; ?></p>
                            <p><strong>Hora:</strong> <?php echo $hora; ?></p>
                            <?php if (!empty($cita['comentario_solicitud'])): ?>
                                <p style="margin-top:5px;">
                                    <strong>Mensaje enviado:</strong>
                                    <em><?php echo htmlspecialchars($cita['comentario_solicitud'], ENT_QUOTES, 'UTF-8'); ?></em>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
                            <?php if ($cancelable): ?>
                                <a href="agendar.php?id=<?php echo (int) $cita['id_inmueble']; ?>"
                                   class="btn btn-secundario"
                                   style="padding:8px 16px;font-size:14px;">
                                    Reprogramar
                                </a>
                                <form method="POST" action="../procesos/procesar-cancelar-cita.php"
                                      style="display:inline;">
                                    <input type="hidden" name="id_cita"
                                           value="<?php echo (int) $cita['id_cita']; ?>">
                                    <button type="submit" class="btn btn-claro"
                                            style="padding:8px 16px;font-size:14px;color:#e94b27;border-color:#e94b27;"
                                            onclick="return confirm('¿Cancelar esta cita?');">
                                        Cancelar cita
                                    </button>
                                </form>
                            <?php elseif ($idEstado === 5): ?>
                                <a href="calificar-inmueble.php"
                                   class="btn btn-claro"
                                   style="padding:8px 16px;font-size:14px;">
                                    Calificar inmueble
                                </a>
                                <a href="calificar-vendedor.php"
                                   class="btn btn-claro"
                                   style="padding:8px 16px;font-size:14px;">
                                    Calificar vendedor
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
