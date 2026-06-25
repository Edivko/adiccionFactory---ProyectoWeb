<?php

require_once __DIR__ . '/../config/conexion.php';

// ─── Leer filtros (valores crudos para la consulta) ───────────────────────

$busquedaRaw  = trim($_GET['busqueda']       ?? '');
$ciudadRaw    = trim($_GET['ciudad']         ?? '');
$categoriaRaw = trim($_GET['categoria']      ?? '');
$precioMinRaw = trim($_GET['precio_minimo']  ?? '');
$precioMaxRaw = trim($_GET['precio_maximo']  ?? '');
$recamarasRaw = trim($_GET['recamaras']      ?? '');
$banosRaw     = trim($_GET['banos']          ?? '');
$ordenRaw     = trim($_GET['orden']          ?? '');

// Valores escapados para repoblar los controles del formulario
$busqueda     = htmlspecialchars($busquedaRaw,  ENT_QUOTES, 'UTF-8');
$ciudad       = htmlspecialchars($ciudadRaw,    ENT_QUOTES, 'UTF-8');
$categoria    = htmlspecialchars($categoriaRaw, ENT_QUOTES, 'UTF-8');
$precioMinimo = htmlspecialchars($precioMinRaw, ENT_QUOTES, 'UTF-8');
$precioMaximo = htmlspecialchars($precioMaxRaw, ENT_QUOTES, 'UTF-8');
$recamaras    = htmlspecialchars($recamarasRaw, ENT_QUOTES, 'UTF-8');
$banos        = htmlspecialchars($banosRaw,     ENT_QUOTES, 'UTF-8');
$orden        = htmlspecialchars($ordenRaw,     ENT_QUOTES, 'UTF-8');

// ─── Construir consulta dinámica con condiciones opcionales ───────────────

// id_estado_publicacion = 3 corresponde a 'publicado' (02_datos_iniciales.sql)
$condiciones = ['i.id_estado_publicacion = 3'];
$tipos       = '';
$valores     = [];

if ($busquedaRaw !== '') {
    $condiciones[] = '(i.titulo LIKE ? OR i.colonia LIKE ?)';
    $like = '%' . $busquedaRaw . '%';
    $tipos .= 'ss';
    $valores[] = $like;
    $valores[] = $like;
}

if ($ciudadRaw !== '') {
    $condiciones[] = 'i.ciudad LIKE ?';
    $tipos .= 's';
    $valores[] = '%' . $ciudadRaw . '%';
}

if ($categoriaRaw !== '') {
    $condiciones[] = 'LOWER(c.nombre_categoria) = ?';
    $tipos .= 's';
    $valores[] = strtolower($categoriaRaw);
}

if ($precioMinRaw !== '' && is_numeric($precioMinRaw) && (float) $precioMinRaw >= 0) {
    $condiciones[] = 'i.precio >= ?';
    $tipos .= 'd';
    $valores[] = (float) $precioMinRaw;
}

if ($precioMaxRaw !== '' && is_numeric($precioMaxRaw) && (float) $precioMaxRaw >= 0) {
    $condiciones[] = 'i.precio <= ?';
    $tipos .= 'd';
    $valores[] = (float) $precioMaxRaw;
}

if ($recamarasRaw !== '' && ctype_digit($recamarasRaw)) {
    $condiciones[] = 'i.recamaras >= ?';
    $tipos .= 'i';
    $valores[] = (int) $recamarasRaw;
}

if ($banosRaw !== '' && ctype_digit($banosRaw)) {
    $condiciones[] = 'i.banos >= ?';
    $tipos .= 'd';
    $valores[] = (float) $banosRaw;
}

// Orden controlado por código; los valores de usuario no entran en el SQL
$sqlOrden = match ($ordenRaw) {
    'precio-menor' => 'ORDER BY i.precio ASC',
    'precio-mayor' => 'ORDER BY i.precio DESC',
    default        => 'ORDER BY i.fecha_registro DESC',
};

$sql = 'SELECT
    i.id_inmueble,
    i.titulo,
    i.precio,
    i.moneda,
    i.ciudad,
    i.estado        AS estado_geo,
    i.recamaras,
    i.banos,
    i.estacionamientos,
    c.nombre_categoria,
    (SELECT url_foto
     FROM FotoInmueble
     WHERE id_inmueble = i.id_inmueble AND principal = TRUE
     LIMIT 1)       AS url_foto
FROM Inmueble i
INNER JOIN CategoriaInmueble c ON c.id_categoria = i.id_categoria
WHERE ' . implode(' AND ', $condiciones) . ' ' . $sqlOrden;

// ─── Ejecutar consulta ────────────────────────────────────────────────────

$inmuebles = [];

try {
    $stmt = mysqli_prepare($conexion, $sql);

    if (!empty($valores)) {
        mysqli_stmt_bind_param($stmt, $tipos, ...$valores);
    }

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $inmuebles = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

} catch (mysqli_sql_exception $e) {
    $inmuebles = [];
}

$total = count($inmuebles);

// ─── Vista ────────────────────────────────────────────────────────────────

$tituloPagina = "Catálogo de inmuebles | Adicción Factory Inmobiliaria";
include("includes/header.php");
?>

<main>

    <section class="encabezado-pagina encabezado-catalogo">
        <div class="contenedor">
            <p class="etiqueta">Propiedades disponibles</p>
            <h1>Catálogo de inmuebles</h1>
            <p>
                Busca y compara casas, departamentos, terrenos y otras
                propiedades disponibles.
            </p>
        </div>
    </section>

    <section class="seccion">
        <div class="contenedor catalogo-layout">

            <!-- FILTROS -->
            <aside class="filtros-card">

                <div class="filtros-encabezado">
                    <div>
                        <h2>Filtrar inmuebles</h2>
                        <p>Selecciona tus preferencias.</p>
                    </div>
                </div>

                <form
                    action="catalogo.php"
                    method="GET"
                    class="formulario filtros-formulario"
                >

                    <div class="campo">
                        <label for="busqueda">Buscar</label>
                        <input
                            type="search"
                            id="busqueda"
                            name="busqueda"
                            placeholder="Título, colonia o palabra clave"
                            value="<?php echo $busqueda; ?>"
                        >
                    </div>

                    <div class="campo">
                        <label for="ciudad">Ciudad</label>
                        <input
                            type="text"
                            id="ciudad"
                            name="ciudad"
                            placeholder="Ej. Metepec"
                            maxlength="100"
                            value="<?php echo $ciudad; ?>"
                        >
                    </div>

                    <div class="campo">
                        <label for="categoria">Tipo de inmueble</label>

                        <select id="categoria" name="categoria">
                            <option value="">Todos</option>
                            <option value="casa"
                                <?php echo $categoriaRaw === 'casa' ? 'selected' : ''; ?>>
                                Casa
                            </option>
                            <option value="departamento"
                                <?php echo $categoriaRaw === 'departamento' ? 'selected' : ''; ?>>
                                Departamento
                            </option>
                            <option value="terreno"
                                <?php echo $categoriaRaw === 'terreno' ? 'selected' : ''; ?>>
                                Terreno
                            </option>
                            <option value="residencia"
                                <?php echo $categoriaRaw === 'residencia' ? 'selected' : ''; ?>>
                                Residencia
                            </option>
                        </select>
                    </div>

                    <div class="filtros-doble">

                        <div class="campo">
                            <label for="precio_minimo">Precio mínimo</label>
                            <input
                                type="number"
                                id="precio_minimo"
                                name="precio_minimo"
                                min="0"
                                step="1000"
                                placeholder="$0"
                                value="<?php echo $precioMinimo; ?>"
                            >
                        </div>

                        <div class="campo">
                            <label for="precio_maximo">Precio máximo</label>
                            <input
                                type="number"
                                id="precio_maximo"
                                name="precio_maximo"
                                min="0"
                                step="1000"
                                placeholder="$5,000,000"
                                value="<?php echo $precioMaximo; ?>"
                            >
                        </div>

                    </div>

                    <div class="filtros-doble">

                        <div class="campo">
                            <label for="recamaras">Recámaras</label>
                            <select id="recamaras" name="recamaras">
                                <option value="">Cualquiera</option>
                                <option value="1" <?php echo $recamarasRaw === '1' ? 'selected' : ''; ?>>1 o más</option>
                                <option value="2" <?php echo $recamarasRaw === '2' ? 'selected' : ''; ?>>2 o más</option>
                                <option value="3" <?php echo $recamarasRaw === '3' ? 'selected' : ''; ?>>3 o más</option>
                                <option value="4" <?php echo $recamarasRaw === '4' ? 'selected' : ''; ?>>4 o más</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="banos">Baños</label>
                            <select id="banos" name="banos">
                                <option value="">Cualquiera</option>
                                <option value="1" <?php echo $banosRaw === '1' ? 'selected' : ''; ?>>1 o más</option>
                                <option value="2" <?php echo $banosRaw === '2' ? 'selected' : ''; ?>>2 o más</option>
                                <option value="3" <?php echo $banosRaw === '3' ? 'selected' : ''; ?>>3 o más</option>
                            </select>
                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-principal btn-completo"
                    >
                        Aplicar filtros
                    </button>

                    <a
                        href="catalogo.php"
                        class="btn btn-claro btn-completo"
                    >
                        Limpiar filtros
                    </a>

                </form>

            </aside>

            <!-- RESULTADOS -->
            <div class="catalogo-resultados">

                <div class="catalogo-barra">

                    <div>
                        <h2>Inmuebles encontrados</h2>
                        <p>
                            <?php if ($total === 1): ?>
                                Mostrando 1 propiedad disponible.
                            <?php else: ?>
                                Mostrando <?php echo $total; ?> propiedades disponibles.
                            <?php endif; ?>
                        </p>
                    </div>

                    <form
                        action="catalogo.php"
                        method="GET"
                        class="orden-formulario"
                    >
                        <!-- Conserva los filtros al ordenar -->
                        <input type="hidden" name="busqueda"      value="<?php echo $busqueda; ?>">
                        <input type="hidden" name="ciudad"        value="<?php echo $ciudad; ?>">
                        <input type="hidden" name="categoria"     value="<?php echo $categoria; ?>">
                        <input type="hidden" name="precio_minimo" value="<?php echo $precioMinimo; ?>">
                        <input type="hidden" name="precio_maximo" value="<?php echo $precioMaximo; ?>">
                        <input type="hidden" name="recamaras"     value="<?php echo $recamaras; ?>">
                        <input type="hidden" name="banos"         value="<?php echo $banos; ?>">

                        <label for="orden">Ordenar por</label>

                        <select
                            id="orden"
                            name="orden"
                            onchange="this.form.submit()"
                        >
                            <option value="">Más recientes</option>
                            <option value="precio-menor"
                                <?php echo $ordenRaw === 'precio-menor' ? 'selected' : ''; ?>>
                                Menor precio
                            </option>
                            <option value="precio-mayor"
                                <?php echo $ordenRaw === 'precio-mayor' ? 'selected' : ''; ?>>
                                Mayor precio
                            </option>
                        </select>
                    </form>

                </div>

                <div class="catalogo-grid">

                    <?php if ($total === 0): ?>

                        <p class="catalogo-sin-resultados">
                            No se encontraron inmuebles con los filtros seleccionados.
                        </p>

                    <?php else: ?>

                        <?php foreach ($inmuebles as $inmueble): ?>

                            <?php
                            $titulo    = htmlspecialchars($inmueble['titulo'],           ENT_QUOTES, 'UTF-8');
                            $catNombre = htmlspecialchars($inmueble['nombre_categoria'], ENT_QUOTES, 'UTF-8');

                            // Ubicación
                            $partes = array_filter([
                                $inmueble['ciudad']    ?? '',
                                $inmueble['estado_geo'] ?? '',
                            ]);
                            $ubicacion = htmlspecialchars(implode(', ', $partes), ENT_QUOTES, 'UTF-8');

                            // Precio
                            if ($inmueble['precio'] !== null) {
                                $moneda  = htmlspecialchars($inmueble['moneda'] ?? 'MXN', ENT_QUOTES, 'UTF-8');
                                $precioF = '$' . number_format((float) $inmueble['precio'], 2) . ' ' . $moneda;
                            } else {
                                $precioF = 'Precio a consultar';
                            }
                            ?>

                            <article class="card card-inmueble">

                                <div class="card-imagen">

                                    <?php if ($inmueble['url_foto'] !== null): ?>
                                        <img
                                            src="<?php echo htmlspecialchars($inmueble['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                            alt="<?php echo $titulo; ?>"
                                        >
                                    <?php else: ?>
                                        <div class="sin-foto" aria-label="Sin fotografía disponible"></div>
                                    <?php endif; ?>

                                    <span class="estado-inmueble">Disponible</span>
                                </div>

                                <div class="card-contenido">
                                    <span class="badge"><?php echo $catNombre; ?></span>

                                    <h3><?php echo $titulo; ?></h3>

                                    <p class="precio"><?php echo $precioF; ?></p>

                                    <?php if ($ubicacion !== ''): ?>
                                        <p class="ubicacion"><?php echo $ubicacion; ?></p>
                                    <?php endif; ?>

                                    <div class="caracteristicas">
                                        <?php if ($inmueble['recamaras'] !== null): ?>
                                            <span><?php echo (int) $inmueble['recamaras']; ?> recámaras</span>
                                        <?php endif; ?>

                                        <?php if ($inmueble['banos'] !== null): ?>
                                            <span><?php echo (float) $inmueble['banos']; ?> baños</span>
                                        <?php endif; ?>

                                        <?php if ($inmueble['estacionamientos'] !== null): ?>
                                            <span><?php echo (int) $inmueble['estacionamientos']; ?> estacionamientos</span>
                                        <?php endif; ?>
                                    </div>

                                    <a
                                        href="detalle-inmueble.php?id=<?php echo (int) $inmueble['id_inmueble']; ?>"
                                        class="btn btn-secundario btn-completo"
                                    >
                                        Ver detalle
                                    </a>
                                </div>

                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
