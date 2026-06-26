<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idUsuario = (int) $_SESSION['id_usuario'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Cargar inmuebles del vendedor ────────────────────────────────────────────

$inmuebles = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            i.id_inmueble,
            i.titulo,
            i.precio,
            i.moneda,
            i.ciudad,
            i.estado          AS estado_geo,
            i.id_estado_publicacion,
            ep.nombre_estado  AS estado_publicacion,
            (SELECT url_foto
             FROM FotoInmueble
             WHERE id_inmueble = i.id_inmueble AND principal = TRUE
             LIMIT 1)         AS url_foto,
            (SELECT COUNT(*)
             FROM FotoInmueble
             WHERE id_inmueble = i.id_inmueble)   AS total_fotos,
            (SELECT COUNT(*)
             FROM Cita
             WHERE id_inmueble = i.id_inmueble)   AS total_citas
        FROM Inmueble i
        INNER JOIN EstadoPublicacion ep ON ep.id_estado_publicacion = i.id_estado_publicacion
        WHERE i.id_usuario_publicador = ?
        ORDER BY i.fecha_registro DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $inmuebles = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar tus inmuebles.';
}

// ─── Colores por estado de publicación ───────────────────────────────────────

function colorEstadoPub(int $id): string {
    return match ($id) {
        1       => 'background-color:#eee;color:#333;',           // borrador
        2       => 'background-color:#ff9800;color:#fff;',        // pendiente
        3       => 'background-color:#4caf50;color:#fff;',        // publicado
        4       => 'background-color:#e94b27;color:#fff;',        // rechazado
        5       => 'background-color:#2196f3;color:#fff;',        // pausado
        6       => 'background-color:#9c27b0;color:#fff;',        // vendido
        7       => 'background-color:#9e9e9e;color:#fff;',        // archivado
        default => '',
    };
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'mis-inmuebles.php';
$tituloPagina = 'Mis inmuebles | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
            <div>
                <p class="etiqueta">Gestión</p>
                <h2 style="font-size:34px;color:var(--color-oscuro);">Mis Inmuebles</h2>
            </div>
            <a href="agregar-inmueble.php" class="btn btn-principal">Agregar inmueble</a>
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

        <?php if (empty($inmuebles)): ?>

            <div class="card" style="padding:40px;text-align:center;">
                <h3 style="color:#666;font-weight:normal;">No has registrado ningún inmueble aún.</h3>
                <a href="agregar-inmueble.php" class="btn btn-principal" style="margin-top:20px;">
                    Agregar primer inmueble
                </a>
            </div>

        <?php else: ?>

            <div class="grid-3">
                <?php foreach ($inmuebles as $inm): ?>
                    <?php
                    $titulo    = htmlspecialchars($inm['titulo'], ENT_QUOTES, 'UTF-8');
                    $idEst     = (int) $inm['id_estado_publicacion'];
                    $colorBadge = colorEstadoPub($idEst);
                    $idInm     = (int) $inm['id_inmueble'];

                    // Acciones de estado disponibles para el vendedor
                    $accionesEstado = [];
                    if ($idEst === 1) {
                        $accionesEstado[] = ['label' => 'Enviar a revisión', 'nuevo_estado' => 2];
                    } elseif ($idEst === 3) {
                        $accionesEstado[] = ['label' => 'Pausar publicación', 'nuevo_estado' => 5];
                    } elseif ($idEst === 5) {
                        $accionesEstado[] = ['label' => 'Reactivar publicación', 'nuevo_estado' => 2];
                    }
                    ?>
                    <article class="card card-inmueble">

                        <?php if (!empty($inm['url_foto'])): ?>
                            <img src="<?php echo htmlspecialchars('../public/' . $inm['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo $titulo; ?>">
                        <?php else: ?>
                            <div class="sin-foto" aria-label="Sin fotografía" style="height:180px;background:#eee;display:grid;place-items:center;font-size:32px;">🏠</div>
                        <?php endif; ?>

                        <div class="card-contenido">
                            <span class="badge" style="<?php echo $colorBadge; ?>">
                                <?php echo htmlspecialchars(ucfirst($inm['estado_publicacion']), ENT_QUOTES, 'UTF-8'); ?>
                            </span>

                            <h3 style="margin-top:8px;"><?php echo $titulo; ?></h3>

                            <?php if (!empty($inm['precio'])): ?>
                                <p class="precio">
                                    $<?php echo number_format((float) $inm['precio'], 0, '.', ','); ?>
                                    <?php echo htmlspecialchars($inm['moneda'] ?? 'MXN', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php endif; ?>

                            <?php
                            $partes = array_filter([$inm['ciudad'] ?? '', $inm['estado_geo'] ?? '']);
                            if (!empty($partes)):
                            ?>
                                <p class="ubicacion"><?php echo htmlspecialchars(implode(', ', $partes), ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>

                            <p style="font-size:13px;color:#999;margin-top:6px;">
                                <?php echo (int) $inm['total_fotos']; ?> foto<?php echo $inm['total_fotos'] !== '1' ? 's' : ''; ?> &middot;
                                <?php echo (int) $inm['total_citas']; ?> cita<?php echo $inm['total_citas'] !== '1' ? 's' : ''; ?>
                            </p>

                            <div style="display:flex;flex-direction:column;gap:10px;margin-top:20px;">
                                <a href="editar-inmueble.php?id=<?php echo $idInm; ?>"
                                   class="btn btn-claro btn-completo">
                                    Editar datos
                                </a>
                                <a href="subir-fotos.php?id=<?php echo $idInm; ?>"
                                   class="btn btn-claro btn-completo">
                                    Gestionar fotos
                                </a>

                                <?php foreach ($accionesEstado as $accion): ?>
                                    <form method="POST" action="../procesos/procesar-inmueble.php">
                                        <input type="hidden" name="accion" value="estado">
                                        <input type="hidden" name="id_inmueble" value="<?php echo $idInm; ?>">
                                        <input type="hidden" name="nuevo_estado" value="<?php echo (int) $accion['nuevo_estado']; ?>">
                                        <button type="submit" class="btn btn-secundario btn-completo">
                                            <?php echo htmlspecialchars($accion['label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
