<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

$idComprador = (int) $_SESSION['id_perfil'];
$idUsuario   = (int) $_SESSION['id_usuario'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Calificaciones de inmuebles ──────────────────────────────────────────────

$califInmueble = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            ci.puntuacion,
            ci.comentario,
            ci.fecha_calificacion,
            i.titulo
        FROM CalificacionInmueble ci
        INNER JOIN Inmueble i ON i.id_inmueble = ci.id_inmueble
        WHERE ci.id_comprador = ?
        ORDER BY ci.fecha_calificacion DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idComprador);
    mysqli_stmt_execute($stmt);
    $califInmueble = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar las calificaciones de inmuebles.';
}

// ─── Calificaciones de vendedores ─────────────────────────────────────────────

$califVendedor = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            cv.puntuacion,
            cv.comentario,
            cv.fecha_calificacion,
            u.nombre    AS nombre_vendedor,
            u.apellido  AS apellido_vendedor
        FROM CalificacionVendedor cv
        INNER JOIN Vendedor v ON v.id_vendedor = cv.id_vendedor
        INNER JOIN Usuario  u ON u.id_usuario  = v.id_usuario
        WHERE cv.id_comprador = ?
        ORDER BY cv.fecha_calificacion DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idComprador);
    mysqli_stmt_execute($stmt);
    $califVendedor = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    // $califVendedor queda vacío
}

// ─── Comentarios de texto ─────────────────────────────────────────────────────

$comentarios = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            co.contenido,
            co.fecha_comentario,
            ec.nombre_estado      AS estado_moderacion,
            i.titulo              AS titulo_inmueble,
            uv.nombre             AS nombre_vendedor,
            uv.apellido           AS apellido_vendedor
        FROM Comentario co
        INNER JOIN EstadoComentario ec ON ec.id_estado_comentario = co.id_estado_comentario
        LEFT JOIN Inmueble i   ON i.id_inmueble  = co.id_inmueble
        LEFT JOIN Vendedor vnd ON vnd.id_vendedor = co.id_vendedor
        LEFT JOIN Usuario uv   ON uv.id_usuario  = vnd.id_usuario
        WHERE co.id_usuario = ?
        ORDER BY co.fecha_comentario DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $comentarios = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    // $comentarios queda vacío
}

// ─── Citas realizadas para el formulario de nuevo comentario ──────────────────

$citasParaComento = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            c.id_cita,
            c.fecha_inicio,
            i.titulo AS titulo_inmueble,
            u.nombre AS nombre_vendedor,
            u.apellido AS apellido_vendedor
        FROM Cita c
        INNER JOIN Inmueble  i ON i.id_inmueble  = c.id_inmueble
        INNER JOIN Vendedor  v ON v.id_vendedor   = c.id_vendedor
        INNER JOIN Usuario   u ON u.id_usuario    = v.id_usuario
        WHERE c.id_comprador = ? AND c.id_estado_cita = 5
        ORDER BY c.fecha_inicio DESC
        LIMIT 20
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idComprador);
    mysqli_stmt_execute($stmt);
    $citasParaComento = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    // Sin citas realizadas
}

$hayContenido = !empty($califInmueble) || !empty($califVendedor) || !empty($comentarios);

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'comentarios.php';
$tituloPagina = 'Mis comentarios y calificaciones | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';

function estrellas(int $n): string {
    return str_repeat('⭐', $n) . str_repeat('☆', 5 - $n);
}
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <h2>Mis Comentarios y Calificaciones</h2>
            <p>Historial de las reseñas que has dejado a los vendedores y las propiedades.</p>
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

        <?php if (!$hayContenido): ?>

            <div class="card" style="padding:40px;text-align:center;">
                <h3 style="color:#666;font-weight:normal;">No hay comentarios registrados.</h3>
                <p style="margin-top:15px;">Después de asistir a una cita podrás calificar al vendedor y el inmueble.</p>
            </div>

        <?php else: ?>

            <!-- Calificaciones de inmuebles -->
            <?php if (!empty($califInmueble)): ?>
                <div class="titulo-seccion" style="margin-bottom:16px;">
                    <p class="etiqueta">Reseñas</p>
                    <h2>Calificaciones de inmuebles</h2>
                </div>
                <?php foreach ($califInmueble as $cal): ?>
                    <div class="card detalle-bloque" style="padding:20px;margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                            <div>
                                <strong><?php echo htmlspecialchars($cal['titulo'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <p style="margin-top:4px;font-size:20px;">
                                    <?php echo estrellas((int) $cal['puntuacion']); ?>
                                </p>
                                <?php if (!empty($cal['comentario'])): ?>
                                    <p style="margin-top:8px;color:#4a4a4a;">
                                        <?php echo htmlspecialchars($cal['comentario'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <span style="color:#999;font-size:13px;white-space:nowrap;">
                                <?php
                                if (!empty($cal['fecha_calificacion'])) {
                                    echo date('d/m/Y', strtotime($cal['fecha_calificacion']));
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Calificaciones de vendedores -->
            <?php if (!empty($califVendedor)): ?>
                <div class="titulo-seccion" style="margin-top:32px;margin-bottom:16px;">
                    <p class="etiqueta">Reseñas</p>
                    <h2>Calificaciones de vendedores</h2>
                </div>
                <?php foreach ($califVendedor as $cal): ?>
                    <div class="card detalle-bloque" style="padding:20px;margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                            <div>
                                <strong>
                                    <?php echo htmlspecialchars(
                                        $cal['nombre_vendedor'] . ' ' . $cal['apellido_vendedor'],
                                        ENT_QUOTES, 'UTF-8'
                                    ); ?>
                                </strong>
                                <p style="margin-top:4px;font-size:20px;">
                                    <?php echo estrellas((int) $cal['puntuacion']); ?>
                                </p>
                                <?php if (!empty($cal['comentario'])): ?>
                                    <p style="margin-top:8px;color:#4a4a4a;">
                                        <?php echo htmlspecialchars($cal['comentario'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <span style="color:#999;font-size:13px;white-space:nowrap;">
                                <?php
                                if (!empty($cal['fecha_calificacion'])) {
                                    echo date('d/m/Y', strtotime($cal['fecha_calificacion']));
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Comentarios de texto -->
            <?php if (!empty($comentarios)): ?>
                <div class="titulo-seccion" style="margin-top:32px;margin-bottom:16px;">
                    <p class="etiqueta">Comentarios</p>
                    <h2>Tus comentarios</h2>
                </div>
                <?php foreach ($comentarios as $co): ?>
                    <?php
                    $sobre = !empty($co['titulo_inmueble'])
                        ? 'Inmueble: ' . $co['titulo_inmueble']
                        : 'Vendedor: ' . $co['nombre_vendedor'] . ' ' . $co['apellido_vendedor'];
                    $estadoMod = $co['estado_moderacion'] ?? '';
                    $colorMod = match ($estadoMod) {
                        'visible'   => 'color:#4caf50;',
                        'pendiente' => 'color:#ff9800;',
                        'oculto'    => 'color:#9e9e9e;',
                        'eliminado' => 'color:#e94b27;',
                        default     => '',
                    };
                    ?>
                    <div class="card detalle-bloque" style="padding:20px;margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                            <div style="flex:1;">
                                <strong style="font-size:14px;color:#666;">
                                    <?php echo htmlspecialchars($sobre, ENT_QUOTES, 'UTF-8'); ?>
                                </strong>
                                <p style="margin-top:8px;color:#4a4a4a;">
                                    <?php echo htmlspecialchars($co['contenido'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <p style="margin-top:6px;font-size:13px;<?php echo $colorMod; ?>">
                                    Estado: <?php echo htmlspecialchars(ucfirst($estadoMod), ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <span style="color:#999;font-size:13px;white-space:nowrap;">
                                <?php
                                if (!empty($co['fecha_comentario'])) {
                                    echo date('d/m/Y', strtotime($co['fecha_comentario']));
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>

        <!-- Formulario para nuevo comentario (solo si hay citas realizadas) -->
        <?php if (!empty($citasParaComento)): ?>
            <div style="margin-top:40px;">
                <div class="titulo-seccion" style="margin-bottom:16px;">
                    <p class="etiqueta">Nuevo</p>
                    <h2>Dejar un comentario</h2>
                </div>
                <div class="card" style="max-width:700px;margin:0 auto;padding:30px;">
                    <form class="formulario" action="../procesos/procesar-comentario.php" method="POST">

                        <div>
                            <label for="id_cita">Cita relacionada <span style="color:#e94b27;">*</span></label>
                            <select id="id_cita" name="id_cita" required>
                                <option value="">-- Selecciona una cita --</option>
                                <?php foreach ($citasParaComento as $cit): ?>
                                    <option value="<?php echo (int) $cit['id_cita']; ?>">
                                        <?php
                                        $fechaCit = date('d/m/Y', strtotime($cit['fecha_inicio']));
                                        echo htmlspecialchars(
                                            $fechaCit . ' — ' . $cit['titulo_inmueble']
                                            . ' (vendedor: ' . $cit['nombre_vendedor'] . ' ' . $cit['apellido_vendedor'] . ')',
                                            ENT_QUOTES, 'UTF-8'
                                        );
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-top:15px;">
                            <label for="tipo">Comentario sobre <span style="color:#e94b27;">*</span></label>
                            <select id="tipo" name="tipo" required>
                                <option value="">-- Selecciona --</option>
                                <option value="inmueble">La propiedad</option>
                                <option value="vendedor">El vendedor</option>
                            </select>
                        </div>

                        <div style="margin-top:15px;">
                            <label for="contenido">Comentario <span style="color:#e94b27;">*</span></label>
                            <textarea id="contenido" name="contenido" required
                                      placeholder="Comparte tu experiencia con detalle..."></textarea>
                        </div>

                        <div style="margin-top:25px;">
                            <button type="submit" class="btn btn-principal">Enviar comentario</button>
                        </div>

                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
