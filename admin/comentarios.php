<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Filtro por estado de comentario ─────────────────────────────────────────
// EstadoComentario: 1=pendiente, 2=visible, 3=oculto, 4=eliminado

$filtroEstado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4]]);

// ─── Contadores por estado (para pills) ──────────────────────────────────────

$contadores = [];

try {
    $sc = mysqli_prepare($conexion,
        'SELECT id_estado_comentario, COUNT(*) AS total
         FROM Comentario
         GROUP BY id_estado_comentario'
    );
    mysqli_stmt_execute($sc);
    $resC = mysqli_stmt_get_result($sc);
    while ($row = mysqli_fetch_assoc($resC)) {
        $contadores[(int) $row['id_estado_comentario']] = (int) $row['total'];
    }
    mysqli_stmt_close($sc);
} catch (mysqli_sql_exception $e) {
    // Contadores opcionales
}

// ─── Cargar comentarios ───────────────────────────────────────────────────────

$comentarios = [];

try {
    if ($filtroEstado !== null) {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                co.id_comentario,
                co.contenido,
                co.fecha_comentario,
                co.id_estado_comentario,
                co.id_vendedor,
                co.id_inmueble,
                ec.nombre_estado         AS estado,
                u.nombre                 AS nombre_autor,
                u.apellido               AS apellido_autor,
                i.titulo                 AS titulo_inmueble,
                CONCAT(uv.nombre, " ", uv.apellido) AS nombre_vendedor
            FROM Comentario co
            INNER JOIN EstadoComentario ec ON ec.id_estado_comentario = co.id_estado_comentario
            INNER JOIN Usuario          u  ON u.id_usuario            = co.id_usuario
            LEFT  JOIN Inmueble         i  ON i.id_inmueble           = co.id_inmueble
            LEFT  JOIN Vendedor         v  ON v.id_vendedor           = co.id_vendedor
            LEFT  JOIN Usuario          uv ON uv.id_usuario           = v.id_usuario
            WHERE co.id_estado_comentario = ?
            ORDER BY co.fecha_comentario DESC
        ');
        mysqli_stmt_bind_param($stmt, 'i', $filtroEstado);
    } else {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                co.id_comentario,
                co.contenido,
                co.fecha_comentario,
                co.id_estado_comentario,
                co.id_vendedor,
                co.id_inmueble,
                ec.nombre_estado         AS estado,
                u.nombre                 AS nombre_autor,
                u.apellido               AS apellido_autor,
                i.titulo                 AS titulo_inmueble,
                CONCAT(uv.nombre, " ", uv.apellido) AS nombre_vendedor
            FROM Comentario co
            INNER JOIN EstadoComentario ec ON ec.id_estado_comentario = co.id_estado_comentario
            INNER JOIN Usuario          u  ON u.id_usuario            = co.id_usuario
            LEFT  JOIN Inmueble         i  ON i.id_inmueble           = co.id_inmueble
            LEFT  JOIN Vendedor         v  ON v.id_vendedor           = co.id_vendedor
            LEFT  JOIN Usuario          uv ON uv.id_usuario           = v.id_usuario
            ORDER BY co.id_estado_comentario ASC, co.fecha_comentario DESC
        ');
    }
    mysqli_stmt_execute($stmt);
    $comentarios = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar los comentarios.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'comentarios.php';
$tituloPagina = 'Moderación de comentarios | Adicción Factory Inmobiliaria';

include '../public/includes/header.php';

$etiquetasEstado = [
    1 => 'Pendiente', 2 => 'Visible', 3 => 'Oculto', 4 => 'Eliminado',
];

function colorEstadoCom(int $id): string
{
    return match ($id) {
        1       => 'background:#ffde59;color:#211d1d;',
        2       => 'background:#4caf50;color:#fff;',
        3       => 'background:#9e9e9e;color:#fff;',
        4       => 'background:#e94b27;color:#fff;',
        default => '',
    };
}
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Administración</p>
            <h2>Moderación de Comentarios</h2>
            <p>Revisa y cambia el estado de visibilidad de los comentarios publicados.</p>
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
            <a href="comentarios.php"
               class="btn <?php echo $filtroEstado === null ? 'btn-principal' : 'btn-claro'; ?>"
               style="font-size:13px;">
                Todos (<?php echo array_sum($contadores); ?>)
            </a>
            <?php foreach ($etiquetasEstado as $idE => $label): ?>
                <a href="comentarios.php?estado=<?php echo $idE; ?>"
                   class="btn <?php echo $filtroEstado === $idE ? 'btn-principal' : 'btn-claro'; ?>"
                   style="font-size:13px;">
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    (<?php echo $contadores[$idE] ?? 0; ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Lista de comentarios -->
        <?php if (empty($comentarios)): ?>
            <div class="card" style="padding:40px;text-align:center;">
                <p style="color:#888;">
                    No hay comentarios<?php echo $filtroEstado !== null ? ' en este estado' : ''; ?>.
                </p>
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:16px;">
            <?php foreach ($comentarios as $co):
                $idCo    = (int) $co['id_comentario'];
                $idEst   = (int) $co['id_estado_comentario'];
                $autor   = htmlspecialchars($co['nombre_autor'] . ' ' . $co['apellido_autor'], ENT_QUOTES, 'UTF-8');
                $estLabel = htmlspecialchars(ucfirst($co['estado']), ENT_QUOTES, 'UTF-8');
                $fecha   = date('d/m/Y H:i', strtotime($co['fecha_comentario']));

                // Destino del comentario: inmueble o vendedor
                if (!empty($co['titulo_inmueble'])) {
                    $destino = 'Inmueble: ' . htmlspecialchars($co['titulo_inmueble'], ENT_QUOTES, 'UTF-8');
                } elseif (!empty($co['nombre_vendedor'])) {
                    $destino = 'Vendedor: ' . htmlspecialchars($co['nombre_vendedor'], ENT_QUOTES, 'UTF-8');
                } else {
                    $destino = '—';
                }
            ?>
                <article class="card" style="padding:20px;">

                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                        <div>
                            <span class="badge" style="<?php echo colorEstadoCom($idEst); ?>">
                                <?php echo $estLabel; ?>
                            </span>
                            <span style="margin-left:10px;font-size:13px;color:#888;"><?php echo $fecha; ?></span>
                        </div>
                        <p style="font-size:13px;color:#666;">
                            <strong>Autor:</strong> <?php echo $autor; ?> &nbsp;|&nbsp;
                            <strong><?php echo $destino; ?></strong>
                        </p>
                    </div>

                    <p style="font-style:italic;color:#4a4a4a;margin-bottom:16px;line-height:1.6;">
                        "<?php echo nl2br(htmlspecialchars($co['contenido'], ENT_QUOTES, 'UTF-8')); ?>"
                    </p>

                    <!-- Acciones de moderación -->
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">

                        <?php if ($idEst !== 2): /* visible */ ?>
                            <form method="POST" action="../procesos/procesar-moderar-comentario.php">
                                <input type="hidden" name="id_comentario" value="<?php echo $idCo; ?>">
                                <input type="hidden" name="accion"        value="visible">
                                <input type="hidden" name="volver_estado" value="<?php echo $filtroEstado ?? ''; ?>">
                                <button type="submit" class="btn btn-claro"
                                        style="font-size:13px;color:#27ae60;border-color:#27ae60;">
                                    Hacer visible
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($idEst !== 3): /* oculto */ ?>
                            <form method="POST" action="../procesos/procesar-moderar-comentario.php">
                                <input type="hidden" name="id_comentario" value="<?php echo $idCo; ?>">
                                <input type="hidden" name="accion"        value="oculto">
                                <input type="hidden" name="volver_estado" value="<?php echo $filtroEstado ?? ''; ?>">
                                <button type="submit" class="btn btn-claro" style="font-size:13px;">
                                    Ocultar
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($idEst !== 4): /* eliminado */ ?>
                            <form method="POST" action="../procesos/procesar-moderar-comentario.php"
                                  onsubmit="return confirm('¿Marcar como eliminado? El comentario dejará de aparecer en el sitio.');">
                                <input type="hidden" name="id_comentario" value="<?php echo $idCo; ?>">
                                <input type="hidden" name="accion"        value="eliminado">
                                <input type="hidden" name="volver_estado" value="<?php echo $filtroEstado ?? ''; ?>">
                                <button type="submit" class="btn btn-claro"
                                        style="font-size:13px;color:#e94b27;border-color:#e94b27;">
                                    Marcar eliminado
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>

                </article>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>
