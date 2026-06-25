<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['error_general']);

// ─── Filtro por estado de cita ────────────────────────────────────────────────
// EstadoCita: 1=pendiente, 2=aceptada, 3=rechazada, 4=cancelada, 5=realizada

$filtroEstado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);

// ─── Contadores por estado (para pills) ──────────────────────────────────────

$contadores = [];

try {
    $sc = mysqli_prepare($conexion,
        'SELECT id_estado_cita, COUNT(*) AS total FROM Cita GROUP BY id_estado_cita'
    );
    mysqli_stmt_execute($sc);
    $resC = mysqli_stmt_get_result($sc);
    while ($row = mysqli_fetch_assoc($resC)) {
        $contadores[(int) $row['id_estado_cita']] = (int) $row['total'];
    }
    mysqli_stmt_close($sc);
} catch (mysqli_sql_exception $e) {
    // Contadores opcionales
}

// ─── Cargar citas (solo lectura) ──────────────────────────────────────────────

$citas = [];

try {
    if ($filtroEstado !== null) {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                c.id_cita,
                c.fecha_inicio,
                c.fecha_fin,
                c.comentario_solicitud,
                c.id_estado_cita,
                ec.nombre_estado   AS estado,
                uc.nombre          AS nombre_comprador,
                uc.apellido        AS apellido_comprador,
                uv.nombre          AS nombre_vendedor,
                uv.apellido        AS apellido_vendedor,
                i.titulo           AS titulo_inmueble
            FROM Cita c
            INNER JOIN Comprador   co ON co.id_comprador = c.id_comprador
            INNER JOIN Usuario     uc ON uc.id_usuario   = co.id_usuario
            INNER JOIN Vendedor    ve ON ve.id_vendedor  = c.id_vendedor
            INNER JOIN Usuario     uv ON uv.id_usuario   = ve.id_usuario
            INNER JOIN Inmueble    i  ON i.id_inmueble   = c.id_inmueble
            INNER JOIN EstadoCita  ec ON ec.id_estado_cita = c.id_estado_cita
            WHERE c.id_estado_cita = ?
            ORDER BY c.fecha_inicio DESC
        ');
        mysqli_stmt_bind_param($stmt, 'i', $filtroEstado);
    } else {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                c.id_cita,
                c.fecha_inicio,
                c.fecha_fin,
                c.comentario_solicitud,
                c.id_estado_cita,
                ec.nombre_estado   AS estado,
                uc.nombre          AS nombre_comprador,
                uc.apellido        AS apellido_comprador,
                uv.nombre          AS nombre_vendedor,
                uv.apellido        AS apellido_vendedor,
                i.titulo           AS titulo_inmueble
            FROM Cita c
            INNER JOIN Comprador   co ON co.id_comprador = c.id_comprador
            INNER JOIN Usuario     uc ON uc.id_usuario   = co.id_usuario
            INNER JOIN Vendedor    ve ON ve.id_vendedor  = c.id_vendedor
            INNER JOIN Usuario     uv ON uv.id_usuario   = ve.id_usuario
            INNER JOIN Inmueble    i  ON i.id_inmueble   = c.id_inmueble
            INNER JOIN EstadoCita  ec ON ec.id_estado_cita = c.id_estado_cita
            ORDER BY c.id_estado_cita ASC, c.fecha_inicio DESC
        ');
    }
    mysqli_stmt_execute($stmt);
    $citas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar las citas.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'citas.php';
$tituloPagina = 'Supervisión de citas | Adicción Factory Inmobiliaria';

include '../public/includes/header.php';

$etiquetasEstado = [
    1 => 'Pendiente', 2 => 'Aceptada', 3 => 'Rechazada', 4 => 'Cancelada', 5 => 'Realizada',
];

function colorEstadoCita(int $id): string
{
    return match ($id) {
        1       => 'background:#ffde59;color:#211d1d;',
        2       => 'background:#4caf50;color:#fff;',
        3       => 'background:#e94b27;color:#fff;',
        4       => 'background:#9e9e9e;color:#fff;',
        5       => 'background:#2196f3;color:#fff;',
        default => '',
    };
}
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Administración · Solo lectura</p>
            <h2>Supervisión de Citas</h2>
            <p>Consulta el historial de visitas entre compradores y vendedores.
               Las acciones sobre citas son exclusivas de comprador y vendedor.</p>
        </div>

        <?php if ($errorGeneral !== null): ?>
            <div class="mensaje-error" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Pills de filtro -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:28px;">
            <a href="citas.php"
               class="btn <?php echo $filtroEstado === null ? 'btn-principal' : 'btn-claro'; ?>"
               style="font-size:13px;">
                Todas (<?php echo array_sum($contadores); ?>)
            </a>
            <?php foreach ($etiquetasEstado as $idE => $label): ?>
                <a href="citas.php?estado=<?php echo $idE; ?>"
                   class="btn <?php echo $filtroEstado === $idE ? 'btn-principal' : 'btn-claro'; ?>"
                   style="font-size:13px;">
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    (<?php echo $contadores[$idE] ?? 0; ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Tabla de citas -->
        <article class="card" style="padding:20px;overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;text-align:left;font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--color-borde);">
                        <th style="padding:12px 10px;">#</th>
                        <th style="padding:12px 10px;">Inmueble</th>
                        <th style="padding:12px 10px;">Comprador</th>
                        <th style="padding:12px 10px;">Vendedor</th>
                        <th style="padding:12px 10px;">Fecha inicio</th>
                        <th style="padding:12px 10px;">Estado</th>
                        <th style="padding:12px 10px;">Notas</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($citas)): ?>
                    <tr>
                        <td colspan="7" style="padding:24px;text-align:center;color:#888;">
                            No hay citas<?php echo $filtroEstado !== null ? ' en este estado' : ''; ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($citas as $cita):
                        $idCita   = (int) $cita['id_cita'];
                        $idEst    = (int) $cita['id_estado_cita'];
                        $comprador = htmlspecialchars($cita['nombre_comprador'] . ' ' . $cita['apellido_comprador'], ENT_QUOTES, 'UTF-8');
                        $vendedor  = htmlspecialchars($cita['nombre_vendedor']  . ' ' . $cita['apellido_vendedor'],  ENT_QUOTES, 'UTF-8');
                        $inmueble  = htmlspecialchars($cita['titulo_inmueble'], ENT_QUOTES, 'UTF-8');
                        $estLabel  = htmlspecialchars(ucfirst($cita['estado']), ENT_QUOTES, 'UTF-8');
                        $fechaIni  = date('d/m/Y H:i', strtotime($cita['fecha_inicio']));
                    ?>
                    <tr style="border-bottom:1px solid var(--color-borde);">
                        <td style="padding:12px 10px;color:#aaa;"><?php echo $idCita; ?></td>
                        <td style="padding:12px 10px;font-weight:600;"><?php echo $inmueble; ?></td>
                        <td style="padding:12px 10px;"><?php echo $comprador; ?></td>
                        <td style="padding:12px 10px;"><?php echo $vendedor; ?></td>
                        <td style="padding:12px 10px;white-space:nowrap;"><?php echo $fechaIni; ?></td>
                        <td style="padding:12px 10px;">
                            <span class="badge" style="<?php echo colorEstadoCita($idEst); ?>">
                                <?php echo $estLabel; ?>
                            </span>
                        </td>
                        <td style="padding:12px 10px;color:#888;font-size:13px;max-width:200px;">
                            <?php if (!empty($cita['comentario_solicitud'])): ?>
                                <?php echo htmlspecialchars(mb_substr($cita['comentario_solicitud'], 0, 80), ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (mb_strlen($cita['comentario_solicitud']) > 80) echo '…'; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </article>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>
