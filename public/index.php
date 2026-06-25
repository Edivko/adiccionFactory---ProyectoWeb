<?php

require_once __DIR__ . '/../config/conexion.php';

// ─── Inmuebles destacados (máximo 3, solo publicados) ─────────────────────────

$inmuebles = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            i.id_inmueble,
            i.titulo,
            i.precio,
            i.moneda,
            i.ciudad,
            i.estado         AS estado_geo,
            i.recamaras,
            i.banos,
            i.estacionamientos,
            c.nombre_categoria,
            (SELECT url_foto
             FROM FotoInmueble
             WHERE id_inmueble = i.id_inmueble AND principal = TRUE
             LIMIT 1)        AS url_foto
        FROM Inmueble i
        INNER JOIN CategoriaInmueble c ON c.id_categoria = i.id_categoria
        WHERE i.id_estado_publicacion = 3
        ORDER BY COALESCE(i.fecha_publicacion, i.fecha_registro) DESC
        LIMIT 3
    ');
    mysqli_stmt_execute($stmt);
    $inmuebles = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $inmuebles = [];
}

$tituloPagina = "Inicio | Adicción Factory Inmobiliaria";
include("includes/header.php");
?>

<main>

    <section class="hero">
        <div class="contenedor hero-contenido">

            <div>
                <p class="etiqueta">Venta de inmuebles</p>
                <h1>Encuentra el inmueble ideal con el vendedor adecuado</h1>
                <p>
                    Explora propiedades disponibles, revisa sus características,
                    conoce vendedores asignados y agenda una cita cuando encuentres
                    el inmueble que deseas visitar.
                </p>

                <div class="hero-botones">
                    <a href="catalogo.php" class="btn btn-principal">Ver inmuebles</a>
                    <a href="registro-comprador.php" class="btn btn-claro">
                        Registrarme como comprador
                    </a>
                </div>
            </div>

            <div class="card buscador-card">
                <h2>Buscar inmueble</h2>

                <form action="catalogo.php" method="GET" class="formulario">
                    <div>
                        <label for="ciudad">Ciudad</label>
                        <input
                            type="text"
                            id="ciudad"
                            name="ciudad"
                            placeholder="Ej. CDMX"
                        >
                    </div>

                    <div>
                        <label for="tipo">Tipo de inmueble</label>
                        <select id="tipo" name="tipo">
                            <option value="">Seleccionar</option>
                            <option value="casa">Casa</option>
                            <option value="departamento">Departamento</option>
                            <option value="terreno">Terreno</option>
                        </select>
                    </div>

                    <div>
                        <label for="precio_max">Precio máximo</label>
                        <input
                            type="number"
                            id="precio_max"
                            name="precio_max"
                            placeholder="Ej. 2500000"
                        >
                    </div>

                    <button type="submit" class="btn btn-principal btn-completo">
                        Buscar
                    </button>
                </form>
            </div>

        </div>
    </section>

    <section class="seccion">
        <div class="contenedor">

            <div class="titulo-seccion">
                <p class="etiqueta">Propiedades</p>
                <h2>Inmuebles destacados</h2>
                <p>Consulta algunas propiedades disponibles en la plataforma.</p>
            </div>

            <?php if (empty($inmuebles)): ?>

                <p class="catalogo-sin-resultados">
                    Aún no hay propiedades publicadas.
                    <a href="catalogo.php">Visita el catálogo</a> cuando haya disponibilidad.
                </p>

            <?php else: ?>

                <div class="grid-3">

                    <?php foreach ($inmuebles as $inm): ?>
                        <?php
                        $titulo  = htmlspecialchars($inm['titulo'],           ENT_QUOTES, 'UTF-8');
                        $cat     = htmlspecialchars($inm['nombre_categoria'], ENT_QUOTES, 'UTF-8');
                        $partes  = array_filter([
                            $inm['ciudad']     ?? '',
                            $inm['estado_geo'] ?? '',
                        ]);
                        $ubicacion = htmlspecialchars(implode(', ', $partes), ENT_QUOTES, 'UTF-8');
                        $precio  = $inm['precio'] !== null
                            ? '$' . number_format((float) $inm['precio'], 2)
                              . ' ' . htmlspecialchars($inm['moneda'] ?? 'MXN', ENT_QUOTES, 'UTF-8')
                            : 'Precio a consultar';
                        $idInm   = (int) $inm['id_inmueble'];
                        ?>

                        <article class="card card-inmueble">

                            <?php if ($inm['url_foto'] !== null): ?>
                                <img
                                    src="<?php echo htmlspecialchars($inm['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo $titulo; ?>"
                                >
                            <?php else: ?>
                                <div class="sin-foto" aria-label="Sin fotografía disponible"></div>
                            <?php endif; ?>

                            <div class="card-contenido">
                                <span class="badge"><?php echo $cat; ?></span>

                                <h3><?php echo $titulo; ?></h3>

                                <p class="precio"><?php echo $precio; ?></p>

                                <?php if ($ubicacion !== ''): ?>
                                    <p class="ubicacion"><?php echo $ubicacion; ?></p>
                                <?php endif; ?>

                                <div class="caracteristicas">
                                    <?php if ($inm['recamaras'] !== null): ?>
                                        <span><?php echo (int) $inm['recamaras']; ?> recámaras</span>
                                    <?php endif; ?>

                                    <?php if ($inm['banos'] !== null): ?>
                                        <span><?php echo (float) $inm['banos']; ?> baños</span>
                                    <?php endif; ?>

                                    <?php if ($inm['estacionamientos'] !== null): ?>
                                        <span><?php echo (int) $inm['estacionamientos']; ?> estacionamientos</span>
                                    <?php endif; ?>
                                </div>

                                <a
                                    href="detalle-inmueble.php?id=<?php echo $idInm; ?>"
                                    class="btn btn-secundario btn-completo"
                                >
                                    Ver detalle
                                </a>
                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>
    </section>

    <section class="seccion seccion-clara">
        <div class="contenedor grid-2">

            <div>
                <p class="etiqueta">Funcionamiento</p>
                <h2>Agenda una cita con el vendedor disponible</h2>
                <p>
                    En Adicción Factory Inmobiliaria, el comprador primero elige
                    el inmueble que le interesa y después selecciona al vendedor
                    disponible para agendar una cita.
                </p>

                <div class="pasos">
                    <div class="paso">
                        <span>1</span>
                        <p>Busca un inmueble en el catálogo.</p>
                    </div>

                    <div class="paso">
                        <span>2</span>
                        <p>Revisa fotos, precio, ubicación y características.</p>
                    </div>

                    <div class="paso">
                        <span>3</span>
                        <p>Selecciona un vendedor asignado al inmueble.</p>
                    </div>

                    <div class="paso">
                        <span>4</span>
                        <p>Agenda tu cita para visitar la propiedad.</p>
                    </div>
                </div>
            </div>

            <article class="card">
                <img
                    src="recursos/img/vendedor1.jpg"
                    alt="Vendedor inmobiliario"
                >

                <div class="card-contenido">
                    <h3>Vendedores verificados</h3>
                    <p>
                        Consulta el perfil público de cada vendedor, su experiencia,
                        zona de trabajo, comentarios y calificación promedio.
                    </p>

                    <a href="catalogo.php" class="btn btn-principal">
                        Explorar inmuebles
                    </a>
                </div>
            </article>

        </div>
    </section>

    <section class="seccion">
        <div class="contenedor grid-2">

            <article class="card">
                <div class="card-contenido">
                    <h3>¿Buscas comprar?</h3>
                    <p>
                        Regístrate como comprador para agendar citas, guardar tu información
                        y calificar inmuebles o vendedores después de una visita.
                    </p>

                    <a href="registro-comprador.php" class="btn btn-principal">
                        Registro comprador
                    </a>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <h3>¿Eres vendedor?</h3>
                    <p>
                        Crea tu perfil público, consulta tus citas solicitadas y administra
                        los inmuebles que tienes asignados.
                    </p>

                    <a href="registro-vendedor.php" class="btn btn-secundario">
                        Registro vendedor
                    </a>
                </div>
            </article>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
