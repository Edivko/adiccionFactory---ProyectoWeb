<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Estadísticas — todo calculado en tiempo real ────────────────────────────

$totalUsuarios         = 0;
$totalCompradores      = 0;
$totalVendedores       = 0;
$vendedoresPendientes  = 0;
$inmueblesPub          = 0;
$inmueblesPend         = 0;
$comentariosPendientes = 0;
$totalCalificaciones   = 0;
$citasPorEstado        = [];  // [['nombre_estado' => ..., 'total' => ...], ...]

try {
    // ── Usuarios ──────────────────────────────────────────────────────────────
    $s = mysqli_prepare($conexion, 'SELECT COUNT(*) FROM Usuario');
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalUsuarios);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    $s = mysqli_prepare($conexion, 'SELECT COUNT(*) FROM Usuario WHERE id_rol = 1');
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalCompradores);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    $s = mysqli_prepare($conexion, 'SELECT COUNT(*) FROM Usuario WHERE id_rol = 2');
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalVendedores);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    // Vendedores pendientes de aprobación (rol=2, estado=1 pendiente)
    $s = mysqli_prepare($conexion,
        'SELECT COUNT(*) FROM Usuario WHERE id_rol = 2 AND id_estado_cuenta = 1'
    );
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $vendedoresPendientes);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    // ── Inmuebles ─────────────────────────────────────────────────────────────
    $s = mysqli_prepare($conexion,
        'SELECT COUNT(*) FROM Inmueble WHERE id_estado_publicacion = 3'
    );
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $inmueblesPub);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    $s = mysqli_prepare($conexion,
        'SELECT COUNT(*) FROM Inmueble WHERE id_estado_publicacion = 2'
    );
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $inmueblesPend);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    // ── Citas por estado ──────────────────────────────────────────────────────
    $s = mysqli_prepare($conexion,
        'SELECT c.id_estado_cita, ec.nombre_estado, COUNT(*) AS total
         FROM Cita c
         INNER JOIN EstadoCita ec ON ec.id_estado_cita = c.id_estado_cita
         GROUP BY c.id_estado_cita, ec.nombre_estado
         ORDER BY c.id_estado_cita ASC'
    );
    mysqli_stmt_execute($s);
    $citasPorEstado = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
    mysqli_stmt_close($s);

    // ── Comentarios pendientes ────────────────────────────────────────────────
    $s = mysqli_prepare($conexion,
        'SELECT COUNT(*) FROM Comentario WHERE id_estado_comentario = 1'
    );
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $comentariosPendientes);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

    // ── Total calificaciones (suma de las 3 tablas) ───────────────────────────
    $s = mysqli_prepare($conexion,
        'SELECT
             (SELECT COUNT(*) FROM CalificacionVendedor)  +
             (SELECT COUNT(*) FROM CalificacionComprador) +
             (SELECT COUNT(*) FROM CalificacionInmueble)  AS total'
    );
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $totalCalificaciones);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);

} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar algunas estadísticas.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'index.php';
$tituloPagina = 'Panel de administración | Adicción Factory Inmobiliaria';
$nombreAdmin  = htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');

include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Administración</p>
            <h2>Bienvenido, <?php echo $nombreAdmin; ?></h2>
            <p>Estadísticas calculadas en tiempo real.</p>
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

        <!-- Sección: Usuarios -->
        <p class="etiqueta" style="margin-bottom:12px;">Usuarios</p>
        <div class="grid-3" style="margin-bottom:32px;">

            <article class="card">
                <div class="card-contenido">
                    <p class="etiqueta">Total usuarios</p>
                    <p style="font-size:36px;font-weight:700;color:var(--color-principal);margin:8px 0;">
                        <?php echo (int) $totalUsuarios; ?>
                    </p>
                    <p style="color:#666;">registrados en la plataforma</p>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <p class="etiqueta">Compradores</p>
                    <p style="font-size:36px;font-weight:700;color:var(--color-principal);margin:8px 0;">
                        <?php echo (int) $totalCompradores; ?>
                    </p>
                    <a href="usuarios.php?rol=1" class="btn btn-claro btn-completo" style="margin-top:12px;">
                        Ver compradores
                    </a>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <p class="etiqueta">Vendedores</p>
                    <p style="font-size:36px;font-weight:700;margin:8px 0;
                               color:<?php echo $vendedoresPendientes > 0 ? '#e94b27' : 'var(--color-principal)'; ?>">
                        <?php echo (int) $totalVendedores; ?>
                        <?php if ($vendedoresPendientes > 0): ?>
                            <span style="font-size:16px;vertical-align:middle;">
                                (<?php echo (int) $vendedoresPendientes; ?> pendientes)
                            </span>
                        <?php endif; ?>
                    </p>
                    <a href="vendedores.php" class="btn btn-claro btn-completo" style="margin-top:12px;">
                        Gestionar vendedores
                    </a>
                </div>
            </article>

        </div>

        <!-- Sección: Inmuebles -->
        <p class="etiqueta" style="margin-bottom:12px;">Inmuebles</p>
        <div class="grid-3" style="margin-bottom:32px;">

            <article class="card">
                <div class="card-contenido">
                    <p class="etiqueta">Publicados</p>
                    <p style="font-size:36px;font-weight:700;color:#27ae60;margin:8px 0;">
                        <?php echo (int) $inmueblesPub; ?>
                    </p>
                    <a href="inmuebles.php?estado=3" class="btn btn-claro btn-completo" style="margin-top:12px;">
                        Ver publicados
                    </a>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <p class="etiqueta">Pendientes de revisión</p>
                    <p style="font-size:36px;font-weight:700;margin:8px 0;
                               color:<?php echo $inmueblesPend > 0 ? '#e94b27' : 'var(--color-principal)'; ?>">
                        <?php echo (int) $inmueblesPend; ?>
                    </p>
                    <a href="inmuebles.php?estado=2" class="btn btn-claro btn-completo" style="margin-top:12px;">
                        Revisar pendientes
                    </a>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <p class="etiqueta">Calificaciones registradas</p>
                    <p style="font-size:36px;font-weight:700;color:var(--color-principal);margin:8px 0;">
                        <?php echo (int) $totalCalificaciones; ?>
                    </p>
                    <p style="color:#666;font-size:13px;">
                        (vendedores + compradores + inmuebles)
                    </p>
                </div>
            </article>

        </div>

        <!-- Sección: Citas por estado -->
        <p class="etiqueta" style="margin-bottom:12px;">Citas</p>
        <div class="card" style="padding:20px;margin-bottom:32px;">
            <?php if (empty($citasPorEstado)): ?>
                <p style="color:#888;text-align:center;padding:12px 0;">Sin citas registradas.</p>
            <?php else: ?>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <?php
                    $coloresCita = [
                        'pendiente'  => ['#ffde59', '#211d1d'],
                        'aceptada'   => ['#4caf50', '#fff'],
                        'rechazada'  => ['#e94b27', '#fff'],
                        'cancelada'  => ['#9e9e9e', '#fff'],
                        'realizada'  => ['#2196f3', '#fff'],
                    ];
                    foreach ($citasPorEstado as $fila):
                        $estado  = strtolower($fila['nombre_estado']);
                        $idEstCita = (int) $fila['id_estado_cita'];
                        [$bg, $fg] = $coloresCita[$estado] ?? ['#e0e0e0', '#333'];
                    ?>
                        <a href="citas.php?estado=<?php echo $idEstCita; ?>"
                           style="flex:1;min-width:140px;padding:16px;border-radius:8px;
                                  background:<?php echo $bg; ?>;color:<?php echo $fg; ?>;
                                  text-align:center;text-decoration:none;">
                            <p style="font-size:28px;font-weight:700;margin:0;">
                                <?php echo (int) $fila['total']; ?>
                            </p>
                            <p style="font-size:13px;margin:4px 0 0;opacity:0.85;">
                                <?php echo htmlspecialchars(ucfirst($fila['nombre_estado']), ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección: Comentarios -->
        <p class="etiqueta" style="margin-bottom:12px;">Moderación</p>
        <div class="grid-3" style="margin-bottom:32px;">

            <article class="card">
                <div class="card-contenido">
                    <p class="etiqueta">Comentarios pendientes</p>
                    <p style="font-size:36px;font-weight:700;margin:8px 0;
                               color:<?php echo $comentariosPendientes > 0 ? '#e94b27' : 'var(--color-principal)'; ?>">
                        <?php echo (int) $comentariosPendientes; ?>
                    </p>
                    <a href="comentarios.php?estado=1" class="btn btn-claro btn-completo" style="margin-top:12px;">
                        Moderar comentarios
                    </a>
                </div>
            </article>

        </div>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>
