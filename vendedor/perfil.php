<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idUsuario  = (int) $_SESSION['id_usuario'];
$idVendedor = (int) $_SESSION['id_perfil'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
unset($_SESSION['mensaje_exito']);

// ─── Datos del vendedor ───────────────────────────────────────────────────────

$datos = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            u.nombre, u.apellido, u.correo, u.telefono,
            v.descripcion, v.experiencia, v.foto_perfil, v.zona_trabajo
        FROM Vendedor v
        INNER JOIN Usuario u ON u.id_usuario = v.id_usuario
        WHERE v.id_usuario = ?
        LIMIT 1
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $datos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?? [];
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $datos = [];
}

// ─── Calificación promedio ────────────────────────────────────────────────────

$calificacion = null;
$totalCal     = 0;

try {
    $s = mysqli_prepare($conexion,
        'SELECT ROUND(AVG(puntuacion),1), COUNT(*) FROM CalificacionVendedor WHERE id_vendedor = ?'
    );
    mysqli_stmt_bind_param($s, 'i', $idVendedor);
    mysqli_stmt_execute($s);
    mysqli_stmt_bind_result($s, $calificacion, $totalCal);
    mysqli_stmt_fetch($s);
    mysqli_stmt_close($s);
} catch (mysqli_sql_exception $e) {
    // Sin calificaciones
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'perfil.php';
$tituloPagina = 'Mi perfil | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';

$esc = fn(mixed $v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Perfil de usuario</p>
            <h2>Mi Perfil Público</h2>
            <p>Así te ven los compradores cuando consultan tus propiedades o agendan una cita.</p>
        </div>

        <?php if ($mensajeExito !== null): ?>
            <div class="mensaje-exito" style="margin-bottom:24px;">
                <?php echo $esc($mensajeExito); ?>
            </div>
        <?php endif; ?>

        <div class="grid-2">

            <!-- Avatar y calificación -->
            <article class="card" style="text-align:center;padding:40px 20px;">
                <?php
                $fotoSrc = !empty($datos['foto_perfil'])
                    ? htmlspecialchars($datos['foto_perfil'], ENT_QUOTES, 'UTF-8')
                    : '../public/recursos/img/avatar-default.png';
                ?>
                <img src="<?php echo $fotoSrc; ?>"
                     alt="Foto de perfil"
                     style="width:160px;height:160px;border-radius:50%;object-fit:cover;margin:0 auto 20px;border:4px solid var(--color-claro);box-shadow:var(--sombra);"
                     onerror="this.style.display='none'">

                <h3><?php echo $esc($datos['nombre'] ?? ''); ?> <?php echo $esc($datos['apellido'] ?? ''); ?></h3>
                <p class="etiqueta" style="margin-bottom:8px;">Vendedor Inmobiliario</p>

                <?php if ($calificacion !== null): ?>
                    <p style="color:var(--color-oscuro);font-weight:bold;margin-bottom:20px;">
                        <?php echo str_repeat('⭐', (int) round((float) $calificacion)); ?>
                        <span style="font-weight:normal;color:var(--color-texto);">
                            (<?php echo $esc($calificacion); ?> / 5 —
                            <?php echo (int) $totalCal; ?> calificacion<?php echo $totalCal !== 1 ? 'es' : ''; ?>)
                        </span>
                    </p>
                <?php else: ?>
                    <p style="color:#999;margin-bottom:20px;">Sin calificaciones aún</p>
                <?php endif; ?>

                <a href="editar-perfil.php" class="btn btn-secundario btn-completo">Editar información</a>
            </article>

            <!-- Datos de contacto y perfil -->
            <article class="card">
                <div class="card-contenido">
                    <h3 style="border-bottom:1px solid var(--color-borde);padding-bottom:10px;margin-bottom:15px;">
                        Información de contacto
                    </h3>

                    <?php if (!empty($datos['correo'])): ?>
                        <p style="margin-bottom:10px;">
                            <strong>Correo:</strong> <?php echo $esc($datos['correo']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($datos['telefono'])): ?>
                        <p style="margin-bottom:25px;">
                            <strong>Teléfono:</strong> <?php echo $esc($datos['telefono']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($datos['descripcion'])): ?>
                        <h3 style="border-bottom:1px solid var(--color-borde);padding-bottom:10px;margin-bottom:15px;">
                            Acerca de mi trabajo
                        </h3>
                        <p style="margin-bottom:20px;"><?php echo $esc($datos['descripcion']); ?></p>
                    <?php endif; ?>

                    <div style="background:var(--color-claro);padding:15px;border-radius:8px;">
                        <?php if (!empty($datos['zona_trabajo'])): ?>
                            <p style="margin-bottom:5px;">
                                <strong>Zona de trabajo:</strong> <?php echo $esc($datos['zona_trabajo']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($datos['experiencia'])): ?>
                            <p>
                                <strong>Experiencia:</strong>
                                <?php echo (int) $datos['experiencia']; ?>
                                año<?php echo (int) $datos['experiencia'] !== 1 ? 's' : ''; ?> en el sector inmobiliario.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

        </div>

        <div style="text-align:center;margin-top:40px;">
            <a href="index.php" class="btn btn-claro">Volver al panel</a>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
