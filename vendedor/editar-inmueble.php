<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idUsuario = (int) $_SESSION['id_usuario'];

// ─── Validar parámetro GET ────────────────────────────────────────────────────

$idInmueble = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($idInmueble === null || $idInmueble === false) {
    $_SESSION['error_general'] = 'Inmueble no especificado.';
    header('Location: mis-inmuebles.php');
    exit;
}

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito']   ?? null;
$errorGeneral = $_SESSION['error_general']   ?? null;
$errores      = $_SESSION['errores_inmueble'] ?? [];
$datosPrev    = $_SESSION['datos_inmueble']  ?? null;
unset(
    $_SESSION['mensaje_exito'],
    $_SESSION['error_general'],
    $_SESSION['errores_inmueble'],
    $_SESSION['datos_inmueble']
);

// ─── Verificar propiedad y propietario ────────────────────────────────────────

$inmueble = null;

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            i.id_inmueble, i.titulo, i.id_categoria, i.id_condicion,
            i.id_estado_publicacion, ep.nombre_estado AS estado_publicacion,
            i.descripcion, i.precio, i.moneda,
            i.estado, i.ciudad, i.colonia, i.direccion, i.codigo_postal,
            i.recamaras, i.banos, i.estacionamientos,
            i.metros_terreno, i.metros_construccion, i.antiguedad
        FROM Inmueble i
        INNER JOIN EstadoPublicacion ep ON ep.id_estado_publicacion = i.id_estado_publicacion
        WHERE i.id_inmueble = ? AND i.id_usuario_publicador = ?
        LIMIT 1
    ');
    mysqli_stmt_bind_param($stmt, 'ii', $idInmueble, $idUsuario);
    mysqli_stmt_execute($stmt);
    $inmueble = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_general'] = 'No fue posible cargar el inmueble.';
    header('Location: mis-inmuebles.php');
    exit;
}

if ($inmueble === null) {
    $_SESSION['error_general'] = 'El inmueble no existe o no tienes permiso para editarlo.';
    header('Location: mis-inmuebles.php');
    exit;
}

// Si hubo envío con error, restaurar datos del formulario
$datos = $datosPrev ?? $inmueble;

// ─── Catálogos ────────────────────────────────────────────────────────────────

$categorias  = [];
$condiciones = [];

try {
    $s = mysqli_prepare($conexion,
        'SELECT id_categoria, nombre_categoria FROM CategoriaInmueble WHERE activo = TRUE ORDER BY nombre_categoria'
    );
    mysqli_stmt_execute($s);
    $categorias = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
    mysqli_stmt_close($s);

    $s = mysqli_prepare($conexion,
        'SELECT id_condicion, nombre_condicion FROM CondicionInmueble ORDER BY nombre_condicion'
    );
    mysqli_stmt_execute($s);
    $condiciones = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
    mysqli_stmt_close($s);
} catch (mysqli_sql_exception $e) {
    // Continúa con catálogos vacíos
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'mis-inmuebles.php';
$tituloPagina = 'Editar inmueble | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';

$v = fn(string $campo): string =>
    htmlspecialchars((string) ($datos[$campo] ?? ''), ENT_QUOTES, 'UTF-8');

$sel = fn(string $campo, mixed $valor): string =>
    (string) ($datos[$campo] ?? '') === (string) $valor ? 'selected' : '';

$e = fn(string $campo): string => isset($errores[$campo])
    ? '<p class="form-error">' . htmlspecialchars($errores[$campo], ENT_QUOTES, 'UTF-8') . '</p>'
    : '';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Actualización</p>
            <h2>Editar Inmueble</h2>
            <p>
                Estado actual:
                <strong><?php echo htmlspecialchars(ucfirst($inmueble['estado_publicacion']), ENT_QUOTES, 'UTF-8'); ?></strong>
            </p>
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

        <div class="card">
            <div class="card-contenido">
                <form action="../procesos/procesar-inmueble.php" method="POST" class="formulario">

                    <!-- id_inmueble como campo oculto →identifica que es edición -->
                    <input type="hidden" name="id_inmueble" value="<?php echo (int) $idInmueble; ?>">

                    <!-- Sección: Información general -->
                    <h3 style="border-bottom:1px solid var(--color-borde);padding-bottom:8px;margin-bottom:20px;">
                        Información general
                    </h3>

                    <div style="margin-bottom:15px;">
                        <label for="titulo">Título de la publicación <span style="color:#e94b27;">*</span></label>
                        <input type="text" id="titulo" name="titulo"
                               value="<?php echo $v('titulo'); ?>"
                               maxlength="150" required>
                        <?php echo $e('titulo'); ?>
                    </div>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="id_categoria">Categoría <span style="color:#e94b27;">*</span></label>
                            <select id="id_categoria" name="id_categoria" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo (int) $cat['id_categoria']; ?>"
                                            <?php echo $sel('id_categoria', $cat['id_categoria']); ?>>
                                        <?php echo htmlspecialchars($cat['nombre_categoria'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php echo $e('id_categoria'); ?>
                        </div>
                        <div>
                            <label for="id_condicion">Condición</label>
                            <select id="id_condicion" name="id_condicion">
                                <option value="">-- Selecciona (opcional) --</option>
                                <?php foreach ($condiciones as $cond): ?>
                                    <option value="<?php echo (int) $cond['id_condicion']; ?>"
                                            <?php echo $sel('id_condicion', $cond['id_condicion']); ?>>
                                        <?php echo htmlspecialchars($cond['nombre_condicion'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="precio">Precio</label>
                            <input type="number" id="precio" name="precio"
                                   value="<?php echo $v('precio'); ?>"
                                   min="0" step="0.01">
                        </div>
                        <div>
                            <label for="moneda">Moneda</label>
                            <select id="moneda" name="moneda">
                                <option value="MXN" <?php echo $sel('moneda', 'MXN'); ?>>MXN</option>
                                <option value="USD" <?php echo $sel('moneda', 'USD'); ?>>USD</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom:15px;">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion"><?php echo $v('descripcion'); ?></textarea>
                    </div>

                    <!-- Sección: Ubicación -->
                    <h3 style="border-bottom:1px solid var(--color-borde);padding-bottom:8px;margin:25px 0 20px;">
                        Ubicación
                    </h3>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="estado">Estado (entidad geográfica)</label>
                            <input type="text" id="estado" name="estado"
                                   value="<?php echo $v('estado'); ?>"
                                   maxlength="100">
                        </div>
                        <div>
                            <label for="ciudad">Ciudad / Municipio</label>
                            <input type="text" id="ciudad" name="ciudad"
                                   value="<?php echo $v('ciudad'); ?>"
                                   maxlength="100">
                        </div>
                    </div>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="colonia">Colonia</label>
                            <input type="text" id="colonia" name="colonia"
                                   value="<?php echo $v('colonia'); ?>"
                                   maxlength="100">
                        </div>
                        <div>
                            <label for="codigo_postal">Código postal</label>
                            <input type="text" id="codigo_postal" name="codigo_postal"
                                   value="<?php echo $v('codigo_postal'); ?>"
                                   maxlength="10">
                        </div>
                    </div>

                    <div style="margin-bottom:15px;">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion"
                               value="<?php echo $v('direccion'); ?>"
                               maxlength="255">
                    </div>

                    <!-- Sección: Características -->
                    <h3 style="border-bottom:1px solid var(--color-borde);padding-bottom:8px;margin:25px 0 20px;">
                        Características
                    </h3>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="recamaras">Recámaras</label>
                            <input type="number" id="recamaras" name="recamaras"
                                   value="<?php echo $v('recamaras'); ?>" min="0">
                        </div>
                        <div>
                            <label for="banos">Baños</label>
                            <input type="number" id="banos" name="banos"
                                   value="<?php echo $v('banos'); ?>" min="0" step="0.5">
                        </div>
                    </div>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="estacionamientos">Estacionamientos</label>
                            <input type="number" id="estacionamientos" name="estacionamientos"
                                   value="<?php echo $v('estacionamientos'); ?>" min="0">
                        </div>
                        <div>
                            <label for="antiguedad">Antigüedad (años)</label>
                            <input type="number" id="antiguedad" name="antiguedad"
                                   value="<?php echo $v('antiguedad'); ?>" min="0">
                        </div>
                    </div>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="metros_terreno">Metros de terreno (m²)</label>
                            <input type="number" id="metros_terreno" name="metros_terreno"
                                   value="<?php echo $v('metros_terreno'); ?>" min="0" step="0.01">
                        </div>
                        <div>
                            <label for="metros_construccion">Metros de construcción (m²)</label>
                            <input type="number" id="metros_construccion" name="metros_construccion"
                                   value="<?php echo $v('metros_construccion'); ?>" min="0" step="0.01">
                        </div>
                    </div>

                    <div style="margin-top:30px;display:flex;gap:15px;justify-content:flex-end;border-top:1px solid var(--color-borde);padding-top:20px;">
                        <a href="mis-inmuebles.php" class="btn btn-claro">Cancelar</a>
                        <a href="subir-fotos.php?id=<?php echo (int) $idInmueble; ?>"
                           class="btn btn-secundario">
                            Gestionar fotos
                        </a>
                        <button type="submit" class="btn btn-principal">Actualizar inmueble</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
