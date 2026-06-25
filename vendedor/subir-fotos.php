<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

// id_perfil = id_vendedor
$idVendedor = (int) $_SESSION['id_perfil'];

// ─── Inmueble desde GET ───────────────────────────────────────────────────────

$idInmueble = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$idInmueble) {
    header('Location: mis-inmuebles.php');
    exit;
}

// ─── Verificar propiedad: Inmueble.id_usuario_publicador → Vendedor.id_vendedor ─

$inmueble = null;

try {
    $stmt = mysqli_prepare($conexion,
        'SELECT i.id_inmueble, i.titulo
         FROM Inmueble i
         INNER JOIN Vendedor v ON v.id_usuario = i.id_usuario_publicador
         WHERE i.id_inmueble = ? AND v.id_vendedor = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'ii', $idInmueble, $idVendedor);
    mysqli_stmt_execute($stmt);
    $inmueble = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    // error de BD → inmueble queda null
}

if (!$inmueble) {
    header('Location: mis-inmuebles.php');
    exit;
}

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Cargar fotos actuales ────────────────────────────────────────────────────

$fotos = [];

try {
    $stmtF = mysqli_prepare($conexion,
        'SELECT id_foto, url_foto, descripcion, principal
         FROM FotoInmueble
         WHERE id_inmueble = ?
         ORDER BY principal DESC, id_foto ASC'
    );
    mysqli_stmt_bind_param($stmtF, 'i', $idInmueble);
    mysqli_stmt_execute($stmtF);
    $fotos = mysqli_fetch_all(mysqli_stmt_get_result($stmtF), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtF);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar las fotos. Inténtalo nuevamente.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'mis-inmuebles.php';
$tituloPagina = 'Fotos del inmueble | Adicción Factory Inmobiliaria';
$tituloEsc    = htmlspecialchars($inmueble['titulo'], ENT_QUOTES, 'UTF-8');

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Multimedia</p>
            <h2>Fotos de "<?php echo $tituloEsc; ?>"</h2>
            <p>Sube, organiza y elimina las fotografías de tu inmueble.</p>
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

        <!-- Formulario de subida -->
        <div class="card" style="padding:30px;margin-bottom:40px;">
            <h3 style="margin-top:0;">Agregar fotografías</h3>

            <form action="../procesos/procesar-foto.php"
                  method="POST"
                  enctype="multipart/form-data"
                  class="formulario">

                <input type="hidden" name="accion"      value="subir">
                <input type="hidden" name="id_inmueble" value="<?php echo $idInmueble; ?>">

                <div>
                    <label for="fotos">
                        Imágenes (JPG, PNG o WEBP · máx. 5 MB por archivo)
                        <span style="color:#e94b27;">*</span>
                    </label>
                    <input type="file"
                           id="fotos"
                           name="fotos[]"
                           multiple
                           accept="image/jpeg,image/png,image/webp"
                           required>
                </div>

                <div style="margin-top:16px;">
                    <label for="descripcion_foto">
                        Descripción (opcional — se aplica a todas las fotos del envío)
                    </label>
                    <input type="text"
                           id="descripcion_foto"
                           name="descripcion"
                           maxlength="150"
                           placeholder="Ej. Sala principal, Fachada, Recámara…">
                </div>

                <div style="margin-top:20px;">
                    <button type="submit" class="btn btn-principal">Subir fotos</button>
                </div>
            </form>
        </div>

        <!-- Galería actual -->
        <?php if (!empty($fotos)): ?>

            <h3 style="margin-bottom:20px;">
                Fotos actuales (<?php echo count($fotos); ?>)
            </h3>

            <div class="grid-3" style="margin-bottom:40px;">
                <?php foreach ($fotos as $foto): ?>
                    <?php
                    $idFoto  = (int)  $foto['id_foto'];
                    $esPrinc = (bool) $foto['principal'];
                    $descEsc = htmlspecialchars($foto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
                    // url_foto = "recursos/uploads/inmuebles/nombre.jpg"
                    // desde vendedor/ necesita ../public/ como prefijo
                    $srcImg  = '../public/' . htmlspecialchars($foto['url_foto'], ENT_QUOTES, 'UTF-8');
                    ?>

                    <article class="card" style="overflow:hidden;">

                        <div style="position:relative;">
                            <img src="<?php echo $srcImg; ?>"
                                 alt="<?php echo $descEsc ?: $tituloEsc; ?>"
                                 style="width:100%;height:200px;object-fit:cover;display:block;"
                                 onerror="this.src='../public/recursos/img/no-imagen.png'">

                            <?php if ($esPrinc): ?>
                                <span style="position:absolute;top:8px;left:8px;
                                             background:#e94b27;color:#fff;
                                             font-size:12px;font-weight:700;
                                             padding:3px 8px;border-radius:4px;">
                                    Principal
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="card-contenido" style="padding:12px;">

                            <?php if ($descEsc !== ''): ?>
                                <p style="font-size:13px;color:#666;margin-bottom:10px;">
                                    <?php echo $descEsc; ?>
                                </p>
                            <?php endif; ?>

                            <div style="display:flex;flex-direction:column;gap:8px;">

                                <?php if (!$esPrinc): ?>
                                    <form method="POST" action="../procesos/procesar-foto.php">
                                        <input type="hidden" name="accion"      value="principal">
                                        <input type="hidden" name="id_inmueble" value="<?php echo $idInmueble; ?>">
                                        <input type="hidden" name="id_foto"     value="<?php echo $idFoto; ?>">
                                        <button type="submit" class="btn btn-claro btn-completo"
                                                style="font-size:13px;">
                                            Marcar como principal
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <p style="font-size:13px;color:#4caf50;font-weight:600;text-align:center;">
                                        Foto principal
                                    </p>
                                <?php endif; ?>

                                <form method="POST" action="../procesos/procesar-foto.php">
                                    <input type="hidden" name="accion"      value="eliminar">
                                    <input type="hidden" name="id_inmueble" value="<?php echo $idInmueble; ?>">
                                    <input type="hidden" name="id_foto"     value="<?php echo $idFoto; ?>">
                                    <button type="submit"
                                            class="btn btn-claro btn-completo"
                                            style="font-size:13px;color:#e94b27;border-color:#e94b27;"
                                            onclick="return confirm('¿Eliminar esta foto? No se puede deshacer.');">
                                        Eliminar
                                    </button>
                                </form>

                            </div>
                        </div>
                    </article>

                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <div class="card" style="padding:40px;text-align:center;margin-bottom:40px;">
                <p style="color:#888;">Aún no hay fotos para este inmueble. ¡Sube la primera!</p>
            </div>

        <?php endif; ?>

        <div style="margin-top:10px;">
            <a href="mis-inmuebles.php" class="btn btn-claro">← Volver a mis inmuebles</a>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
