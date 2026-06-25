<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Cargar catálogos ─────────────────────────────────────────────────────────

$categorias = [];
$servicios  = [];
$amenidades = [];

try {
    $sc = mysqli_prepare($conexion,
        'SELECT id_categoria, nombre_categoria, descripcion, activo
         FROM CategoriaInmueble ORDER BY nombre_categoria ASC'
    );
    mysqli_stmt_execute($sc);
    $categorias = mysqli_fetch_all(mysqli_stmt_get_result($sc), MYSQLI_ASSOC);
    mysqli_stmt_close($sc);

    $ss = mysqli_prepare($conexion,
        'SELECT id_servicio, nombre_servicio, activo FROM Servicio ORDER BY nombre_servicio ASC'
    );
    mysqli_stmt_execute($ss);
    $servicios = mysqli_fetch_all(mysqli_stmt_get_result($ss), MYSQLI_ASSOC);
    mysqli_stmt_close($ss);

    $sa = mysqli_prepare($conexion,
        'SELECT id_amenidad, nombre_amenidad, activo FROM Amenidad ORDER BY nombre_amenidad ASC'
    );
    mysqli_stmt_execute($sa);
    $amenidades = mysqli_fetch_all(mysqli_stmt_get_result($sa), MYSQLI_ASSOC);
    mysqli_stmt_close($sa);

} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar los catálogos.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'categorias.php';
$tituloPagina = 'Catálogos | Adicción Factory Inmobiliaria';

include '../public/includes/header.php';

// Renderiza una tabla de catálogo reutilizable
function renderCatalogo(
    string $titulo,
    string $tablaClave,   // 'categoria' | 'servicio' | 'amenidad'
    array  $filas,
    string $campoId,
    string $campoNombre
): void {
    $tieneDesc = $tablaClave === 'categoria';
    ?>
    <section class="card" style="padding:24px;margin-bottom:32px;">

        <h3 style="margin-top:0;"><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h3>

        <!-- Formulario de creación -->
        <form action="../procesos/procesar-categoria.php" method="POST"
              class="formulario" style="margin-bottom:24px;">
            <input type="hidden" name="tabla"  value="<?php echo htmlspecialchars($tablaClave, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="accion" value="crear">

            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:180px;">
                    <label for="nombre_<?php echo $tablaClave; ?>">Nombre</label>
                    <input type="text"
                           id="nombre_<?php echo $tablaClave; ?>"
                           name="nombre"
                           maxlength="100"
                           required
                           placeholder="Nuevo nombre…">
                </div>
                <?php if ($tieneDesc): ?>
                    <div style="flex:2;min-width:200px;">
                        <label for="desc_<?php echo $tablaClave; ?>">Descripción (opcional)</label>
                        <input type="text"
                               id="desc_<?php echo $tablaClave; ?>"
                               name="descripcion"
                               maxlength="255"
                               placeholder="Descripción breve…">
                    </div>
                <?php endif; ?>
                <div>
                    <button type="submit" class="btn btn-principal" style="margin-top:4px;">
                        Agregar
                    </button>
                </div>
            </div>
        </form>

        <!-- Lista -->
        <?php if (empty($filas)): ?>
            <p style="color:#888;text-align:center;padding:16px 0;">Sin registros.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--color-borde);">
                        <th style="padding:10px 8px;text-align:left;">#</th>
                        <th style="padding:10px 8px;text-align:left;">Nombre</th>
                        <?php if ($tieneDesc): ?>
                            <th style="padding:10px 8px;text-align:left;">Descripción</th>
                        <?php endif; ?>
                        <th style="padding:10px 8px;text-align:left;">Estado</th>
                        <th style="padding:10px 8px;text-align:left;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $fila):
                    $id      = (int)  $fila[$campoId];
                    $nombre  = htmlspecialchars($fila[$campoNombre], ENT_QUOTES, 'UTF-8');
                    $activo  = (bool) $fila['activo'];
                    $desc    = $tieneDesc ? htmlspecialchars($fila['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') : '';
                ?>
                    <tr style="border-bottom:1px solid var(--color-borde);">
                        <td style="padding:10px 8px;color:#aaa;"><?php echo $id; ?></td>
                        <td style="padding:10px 8px;font-weight:600;"><?php echo $nombre; ?></td>
                        <?php if ($tieneDesc): ?>
                            <td style="padding:10px 8px;color:#666;"><?php echo $desc; ?></td>
                        <?php endif; ?>
                        <td style="padding:10px 8px;">
                            <?php if ($activo): ?>
                                <span style="color:#27ae60;font-weight:700;">Activo</span>
                            <?php else: ?>
                                <span style="color:#e94b27;font-weight:700;">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px 8px;">
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">

                                <!-- Toggle activo -->
                                <form method="POST" action="../procesos/procesar-categoria.php">
                                    <input type="hidden" name="tabla"  value="<?php echo htmlspecialchars($tablaClave, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="accion" value="toggle">
                                    <input type="hidden" name="id"     value="<?php echo $id; ?>">
                                    <button type="submit" class="btn btn-claro" style="font-size:12px;">
                                        <?php echo $activo ? 'Desactivar' : 'Activar'; ?>
                                    </button>
                                </form>

                                <!-- Eliminar físico (solo si no hay relacionados) -->
                                <form method="POST" action="../procesos/procesar-categoria.php"
                                      onsubmit="return confirm('¿Eliminar definitivamente este registro?');">
                                    <input type="hidden" name="tabla"  value="<?php echo htmlspecialchars($tablaClave, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id"     value="<?php echo $id; ?>">
                                    <button type="submit" class="btn btn-claro"
                                            style="font-size:12px;color:#e94b27;border-color:#e94b27;">
                                        Eliminar
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
    <?php
}
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Administración</p>
            <h2>Catálogos</h2>
            <p>Gestiona categorías de inmuebles, servicios y amenidades.
               Desactivar oculta el elemento del formulario de publicación sin eliminar datos históricos.</p>
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

        <?php renderCatalogo('Categorías de inmueble', 'categoria', $categorias, 'id_categoria', 'nombre_categoria'); ?>
        <?php renderCatalogo('Servicios',              'servicio',  $servicios,  'id_servicio',  'nombre_servicio');  ?>
        <?php renderCatalogo('Amenidades',             'amenidad',  $amenidades, 'id_amenidad',  'nombre_amenidad');  ?>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>
