<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Filtro por estado de publicación ────────────────────────────────────────

$filtroEstado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

// EstadoPublicacion: 1=borrador,2=pendiente,3=publicado,4=rechazado,5=pausado,6=vendido,7=archivado
$estadosValidos = [1, 2, 3, 4, 5, 6, 7];
if ($filtroEstado !== null && !in_array($filtroEstado, $estadosValidos, true)) {
    $filtroEstado = null;
}

// ─── Contadores por estado (para pills) ──────────────────────────────────────

$contadores = [];

try {
    $sc = mysqli_prepare($conexion,
        'SELECT id_estado_publicacion, COUNT(*) AS total
         FROM Inmueble
         GROUP BY id_estado_publicacion'
    );
    mysqli_stmt_execute($sc);
    $resC = mysqli_stmt_get_result($sc);
    while ($row = mysqli_fetch_assoc($resC)) {
        $contadores[(int) $row['id_estado_publicacion']] = (int) $row['total'];
    }
    mysqli_stmt_close($sc);
} catch (mysqli_sql_exception $e) {
    // Contadores opcionales, no bloquean la página
}

// ─── Lista de inmuebles ───────────────────────────────────────────────────────

$inmuebles = [];

try {
    if ($filtroEstado !== null) {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                i.id_inmueble,
                i.titulo,
                i.precio,
                i.moneda,
                i.ciudad,
                i.id_estado_publicacion,
                ep.nombre_estado   AS estado_pub,
                ci.nombre_categoria AS categoria,
                u.nombre           AS nombre_pub,
                u.apellido         AS apellido_pub,
                i.fecha_registro
            FROM Inmueble i
            INNER JOIN EstadoPublicacion ep ON ep.id_estado_publicacion = i.id_estado_publicacion
            INNER JOIN CategoriaInmueble ci ON ci.id_categoria          = i.id_categoria
            INNER JOIN Usuario           u  ON u.id_usuario             = i.id_usuario_publicador
            WHERE i.id_estado_publicacion = ?
            ORDER BY i.fecha_registro DESC
        ');
        mysqli_stmt_bind_param($stmt, 'i', $filtroEstado);
    } else {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                i.id_inmueble,
                i.titulo,
                i.precio,
                i.moneda,
                i.ciudad,
                i.id_estado_publicacion,
                ep.nombre_estado   AS estado_pub,
                ci.nombre_categoria AS categoria,
                u.nombre           AS nombre_pub,
                u.apellido         AS apellido_pub,
                i.fecha_registro
            FROM Inmueble i
            INNER JOIN EstadoPublicacion ep ON ep.id_estado_publicacion = i.id_estado_publicacion
            INNER JOIN CategoriaInmueble ci ON ci.id_categoria          = i.id_categoria
            INNER JOIN Usuario           u  ON u.id_usuario             = i.id_usuario_publicador
            ORDER BY i.id_estado_publicacion ASC, i.fecha_registro DESC
        ');
    }
    mysqli_stmt_execute($stmt);
    $inmuebles = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar la lista de inmuebles.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'inmuebles.php';
$tituloPagina = 'Inmuebles | Adicción Factory Inmobiliaria';

include '../public/includes/header.php';

$etiquetasEstado = [
    1 => 'Borrador', 2 => 'Pendiente', 3 => 'Publicado',
    4 => 'Rechazado', 5 => 'Pausado',  6 => 'Vendido', 7 => 'Archivado',
];

function colorEstadoPub(int $id): string
{
    return match ($id) {
        2       => 'background:#ffde59;color:#211d1d;',  // pendiente
        3       => 'background:#4caf50;color:#fff;',     // publicado
        4       => 'background:#e94b27;color:#fff;',     // rechazado
        5       => 'background:#2196f3;color:#fff;',     // pausado
        6       => 'background:#9c27b0;color:#fff;',     // vendido
        7       => 'background:#9e9e9e;color:#fff;',     // archivado
        default => 'background:#e0e0e0;color:#333;',
    };
}
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Administración</p>
            <h2>Inmuebles</h2>
            <p>Revisa y audita las publicaciones de los vendedores.</p>
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

        <!-- Pills de filtro -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:28px;">
            <a href="inmuebles.php"
               class="btn <?php echo $filtroEstado === null ? 'btn-principal' : 'btn-claro'; ?>"
               style="font-size:13px;">
                Todos
                <?php
                $totalGeneral = array_sum($contadores);
                if ($totalGeneral > 0) echo " ({$totalGeneral})";
                ?>
            </a>
            <?php foreach ($etiquetasEstado as $idE => $label): ?>
                <?php $cnt = $contadores[$idE] ?? 0; if ($cnt === 0 && $filtroEstado !== $idE) continue; ?>
                <a href="inmuebles.php?estado=<?php echo $idE; ?>"
                   class="btn <?php echo $filtroEstado === $idE ? 'btn-principal' : 'btn-claro'; ?>"
                   style="font-size:13px;">
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    <?php if ($cnt > 0) echo " ({$cnt})"; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Tabla de inmuebles -->
        <article class="card" style="padding:20px;overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;text-align:left;">
                <thead>
                    <tr style="border-bottom:2px solid var(--color-borde);">
                        <th style="padding:12px 10px;">#</th>
                        <th style="padding:12px 10px;">Título</th>
                        <th style="padding:12px 10px;">Publicador</th>
                        <th style="padding:12px 10px;">Categoría</th>
                        <th style="padding:12px 10px;">Precio</th>
                        <th style="padding:12px 10px;">Estado</th>
                        <th style="padding:12px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($inmuebles)): ?>
                    <tr>
                        <td colspan="7" style="padding:24px;text-align:center;color:#888;">
                            No hay inmuebles<?php echo $filtroEstado !== null ? ' en este estado' : ''; ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($inmuebles as $inm):
                        $idInm    = (int) $inm['id_inmueble'];
                        $idEst    = (int) $inm['id_estado_publicacion'];
                        $titulo   = htmlspecialchars($inm['titulo'], ENT_QUOTES, 'UTF-8');
                        $pub      = htmlspecialchars($inm['nombre_pub'] . ' ' . $inm['apellido_pub'], ENT_QUOTES, 'UTF-8');
                        $cat      = htmlspecialchars($inm['categoria'], ENT_QUOTES, 'UTF-8');
                        $estLabel = htmlspecialchars(ucfirst($inm['estado_pub']), ENT_QUOTES, 'UTF-8');
                        $precio   = $inm['precio'] !== null
                            ? '$' . number_format((float) $inm['precio'], 0, '.', ',') . ' ' . htmlspecialchars($inm['moneda'] ?? '', ENT_QUOTES, 'UTF-8')
                            : '—';
                        $fecha    = date('d/m/Y', strtotime($inm['fecha_registro']));
                    ?>
                    <tr style="border-bottom:1px solid var(--color-borde);">
                        <td style="padding:12px 10px;color:#999;"><?php echo $idInm; ?></td>
                        <td style="padding:12px 10px;">
                            <strong><?php echo $titulo; ?></strong>
                            <?php if (!empty($inm['ciudad'])): ?>
                                <br><span style="font-size:12px;color:#999;">
                                    <?php echo htmlspecialchars($inm['ciudad'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 10px;"><?php echo $pub; ?></td>
                        <td style="padding:12px 10px;"><?php echo $cat; ?></td>
                        <td style="padding:12px 10px;white-space:nowrap;"><?php echo $precio; ?></td>
                        <td style="padding:12px 10px;">
                            <span class="badge" style="<?php echo colorEstadoPub($idEst); ?>">
                                <?php echo $estLabel; ?>
                            </span>
                        </td>
                        <td style="padding:12px 10px;">
                            <a href="revisar-inmueble.php?id=<?php echo $idInm; ?>"
                               class="btn btn-claro"
                               style="font-size:13px;">
                                <?php echo $idEst === 2 ? 'Revisar' : 'Ver detalle'; ?>
                            </a>
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
