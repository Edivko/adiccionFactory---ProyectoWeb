<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$errores  = $_SESSION['errores_inmueble'] ?? [];
$datosPrev = $_SESSION['datos_inmueble']  ?? null;
unset($_SESSION['errores_inmueble'], $_SESSION['datos_inmueble']);

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
    // Sin catálogos: el formulario mostrará selects vacíos
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'agregar-inmueble.php';
$tituloPagina = 'Agregar inmueble | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';

$v = fn(string $campo): string =>
    htmlspecialchars((string) ($datosPrev[$campo] ?? ''), ENT_QUOTES, 'UTF-8');

$e = fn(string $campo): string => isset($errores[$campo])
    ? '<p class="form-error">' . htmlspecialchars($errores[$campo], ENT_QUOTES, 'UTF-8') . '</p>'
    : '';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Nuevo registro</p>
            <h2>Agregar Inmueble</h2>
            <p>Se creará en estado <strong>borrador</strong>. Podrás enviarlo a revisión cuando esté completo.</p>
        </div>

        <div class="card">
            <div class="card-contenido">
                <form action="../procesos/procesar-inmueble.php" method="POST" class="formulario">

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
                                        <?php echo (string) ($datosPrev['id_categoria'] ?? '') === (string) $cat['id_categoria'] ? 'selected' : ''; ?>>
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
                                        <?php echo (string) ($datosPrev['id_condicion'] ?? '') === (string) $cond['id_condicion'] ? 'selected' : ''; ?>>
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
                                   min="0" step="0.01" placeholder="Opcional">
                        </div>
                        <div>
                            <label for="moneda">Moneda</label>
                            <select id="moneda" name="moneda">
                                <option value="MXN" <?php echo ($datosPrev['moneda'] ?? 'MXN') === 'MXN' ? 'selected' : ''; ?>>MXN</option>
                                <option value="USD" <?php echo ($datosPrev['moneda'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom:15px;">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion"
                                  placeholder="Describe la propiedad..."><?php echo $v('descripcion'); ?></textarea>
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
                                   maxlength="100" placeholder="Ej. Estado de México">
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
                                   value="<?php echo $v('recamaras'); ?>"
                                   min="0">
                        </div>
                        <div>
                            <label for="banos">Baños</label>
                            <input type="number" id="banos" name="banos"
                                   value="<?php echo $v('banos'); ?>"
                                   min="0" step="0.5">
                        </div>
                    </div>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="estacionamientos">Estacionamientos</label>
                            <input type="number" id="estacionamientos" name="estacionamientos"
                                   value="<?php echo $v('estacionamientos'); ?>"
                                   min="0">
                        </div>
                        <div>
                            <label for="antiguedad">Antigüedad (años)</label>
                            <input type="number" id="antiguedad" name="antiguedad"
                                   value="<?php echo $v('antiguedad'); ?>"
                                   min="0">
                        </div>
                    </div>

                    <div class="grid-2" style="gap:20px;margin-bottom:15px;">
                        <div>
                            <label for="metros_terreno">Metros de terreno (m²)</label>
                            <input type="number" id="metros_terreno" name="metros_terreno"
                                   value="<?php echo $v('metros_terreno'); ?>"
                                   min="0" step="0.01">
                        </div>
                        <div>
                            <label for="metros_construccion">Metros de construcción (m²)</label>
                            <input type="number" id="metros_construccion" name="metros_construccion"
                                   value="<?php echo $v('metros_construccion'); ?>"
                                   min="0" step="0.01">
                        </div>
                    </div>

                    <div style="text-align:right;margin-top:30px;border-top:1px solid var(--color-borde);padding-top:20px;display:flex;justify-content:flex-end;gap:15px;">
                        <a href="mis-inmuebles.php" class="btn btn-claro">Cancelar</a>
                        <button type="submit" class="btn btn-principal">Guardar inmueble</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
