<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idVendedor = (int) $_SESSION['id_perfil'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Cargar citas del vendedor ────────────────────────────────────────────────

$citas = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            c.id_cita,
            c.fecha_inicio,
            c.comentario_solicitud,
            c.id_estado_cita,
            ec.nombre_estado      AS estado,
            i.titulo              AS titulo_inmueble,
            u.nombre              AS nombre_comprador,
            u.apellido            AS apellido_comprador,
            co.id_comprador,
            (SELECT COUNT(*) FROM CalificacionComprador cc
             WHERE cc.id_cita = c.id_cita)  AS ya_calificado
        FROM Cita c
        INNER JOIN Inmueble   i  ON i.id_inmueble   = c.id_inmueble
        INNER JOIN Comprador  co ON co.id_comprador = c.id_comprador
        INNER JOIN Usuario    u  ON u.id_usuario    = co.id_usuario
        INNER JOIN EstadoCita ec ON ec.id_estado_cita = c.id_estado_cita
        WHERE c.id_vendedor = ?
        ORDER BY
            CASE c.id_estado_cita
                WHEN 1 THEN 1
                WHEN 2 THEN 2
                WHEN 5 THEN 3
                ELSE 4
            END,
            c.fecha_inicio DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idVendedor);
    mysqli_stmt_execute($stmt);
    $citas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar las citas en este momento.';
}

// ─── Colores de estado ────────────────────────────────────────────────────────

function colorEstadoCitaVend(int $id): string {
    return match ($id) {
        1       => 'background-color:#ffde59;color:#211d1d;',
        2       => 'background-color:#4caf50;color:#fff;',
        3       => 'background-color:#e94b27;color:#fff;',
        4       => 'background-color:#9e9e9e;color:#fff;',
        5       => 'background-color:#2196f3;color:#fff;',
        default => '',
    };
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'citas.php';
$tituloPagina = 'Citas solicitadas | Adicción Factory Inmobiliaria';

$meses = ['','enero','febrero','marzo','abril','mayo','junio',
          'julio','agosto','septiembre','octubre','noviembre','diciembre'];

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Agenda</p>
            <h2>Citas Solicitadas</h2>
            <p>Gestiona las solicitudes de los compradores para visitar tus propiedades.</p>
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
                <h3 style="color:#666;font-weight:normal;">Aún no tienes citas solicitadas.</h3>
                <p style="margin-top:15px;">
                    Cuando un comprador agende una visita a uno de tus inmuebles, aparecerá aquí.
                </p>
            </div>

        <?php else: ?>

            <div class="grid-2">
                <?php foreach ($citas as $cita): ?>
                    <?php
                    $idEst        = (int) $cita['id_estado_cita'];
                    $ts           = strtotime($cita['fecha_inicio']);
                    $fechaStr     = date('j', $ts) . ' de ' . $meses[(int) date('n', $ts)]
                                  . ' de ' . date('Y', $ts);
                    $hora         = date('H:i', $ts);
                    $comprador    = htmlspecialchars(
                        $cita['nombre_comprador'] . ' ' . $cita['apellido_comprador'],
                        ENT_QUOTES, 'UTF-8'
                    );
                    $inmueble     = htmlspecialchars($cita['titulo_inmueble'], ENT_QUOTES, 'UTF-8');
                    $estado       = htmlspecialchars(ucfirst($cita['estado']), ENT_QUOTES, 'UTF-8');
                    $colorBadge   = colorEstadoCitaVend($idEst);
                    $idCita       = (int) $cita['id_cita'];
                    $yaCalificado = (int) $cita['ya_calificado'] > 0;
                    ?>

                    <article class="card">
                        <div class="card-contenido">

                            <span class="badge" style="<?php echo $colorBadge; ?>">
                                <?php echo $estado; ?>
                            </span>

                            <h3 style="margin-top:10px;"><?php echo $inmueble; ?></h3>

                            <div style="margin-top:10px;color:#4a4a4a;">
                                <p><strong>Comprador:</strong> <?php echo $comprador; ?></p>
                                <p><strong>Fecha:</strong> <?php echo $fechaStr; ?></p>
                                <p><strong>Hora:</strong> <?php echo $hora; ?> hrs</p>
                                <?php if (!empty($cita['comentario_solicitud'])): ?>
                                    <p style="margin-top:6px;font-style:italic;color:#666;">
                                        "<?php echo htmlspecialchars($cita['comentario_solicitud'], ENT_QUOTES, 'UTF-8'); ?>"
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Acciones según estado -->
                            <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;">

                                <?php if ($idEst === 1): /* pendiente → aceptar / rechazar */ ?>

                                    <form method="POST" action="../procesos/procesar-cita-vendedor.php"
                                          style="display:flex;gap:10px;">
                                        <input type="hidden" name="id_cita" value="<?php echo $idCita; ?>">
                                        <button type="submit" name="accion" value="aceptar"
                                                class="btn btn-principal" style="flex:1;">
                                            Aceptar
                                        </button>
                                        <button type="submit" name="accion" value="rechazar"
                                                class="btn btn-claro"
                                                style="flex:1;color:#e94b27;border-color:#e94b27;"
                                                onclick="return confirm('¿Rechazar esta cita?');">
                                            Rechazar
                                        </button>
                                    </form>

                                <?php elseif ($idEst === 2): /* aceptada → marcar realizada */ ?>

                                    <form method="POST" action="../procesos/procesar-cita-vendedor.php">
                                        <input type="hidden" name="id_cita" value="<?php echo $idCita; ?>">
                                        <button type="submit" name="accion" value="realizada"
                                                class="btn btn-secundario btn-completo"
                                                onclick="return confirm('¿Marcar esta cita como realizada?');">
                                            Marcar como realizada
                                        </button>
                                    </form>

                                <?php elseif ($idEst === 5): /* realizada → calificar */ ?>

                                    <?php if ($yaCalificado): ?>
                                        <p style="color:#4caf50;font-size:14px;font-weight:600;">
                                            Comprador ya calificado
                                        </p>
                                    <?php else: ?>
                                        <a href="calificar-comprador.php?id_cita=<?php echo $idCita; ?>"
                                           class="btn btn-claro btn-completo">
                                            Calificar comprador
                                        </a>
                                    <?php endif; ?>

                                <?php endif; ?>

                            </div>

                        </div>
                    </article>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
