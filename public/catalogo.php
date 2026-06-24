<?php
$tituloPagina = "Catálogo de inmuebles | Adicción Factory Inmobiliaria";
include("includes/header.php");

/*
 * Estos valores permiten conservar los filtros escritos
 * después de enviar el formulario.
 */
$busqueda = htmlspecialchars($_GET["busqueda"] ?? "");
$ciudad = htmlspecialchars($_GET["ciudad"] ?? "");
$categoria = htmlspecialchars($_GET["categoria"] ?? "");
$precioMinimo = htmlspecialchars($_GET["precio_minimo"] ?? "");
$precioMaximo = htmlspecialchars($_GET["precio_maximo"] ?? "");
$recamaras = htmlspecialchars($_GET["recamaras"] ?? "");
$banos = htmlspecialchars($_GET["banos"] ?? "");
$orden = htmlspecialchars($_GET["orden"] ?? "");
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

                            <option
                                value="casa"
                                <?php echo $categoria === "casa" ? "selected" : ""; ?>
                            >
                                Casa
                            </option>

                            <option
                                value="departamento"
                                <?php echo $categoria === "departamento" ? "selected" : ""; ?>
                            >
                                Departamento
                            </option>

                            <option
                                value="terreno"
                                <?php echo $categoria === "terreno" ? "selected" : ""; ?>
                            >
                                Terreno
                            </option>

                            <option
                                value="residencia"
                                <?php echo $categoria === "residencia" ? "selected" : ""; ?>
                            >
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
                                <option value="1" <?php echo $recamaras === "1" ? "selected" : ""; ?>>
                                    1 o más
                                </option>
                                <option value="2" <?php echo $recamaras === "2" ? "selected" : ""; ?>>
                                    2 o más
                                </option>
                                <option value="3" <?php echo $recamaras === "3" ? "selected" : ""; ?>>
                                    3 o más
                                </option>
                                <option value="4" <?php echo $recamaras === "4" ? "selected" : ""; ?>>
                                    4 o más
                                </option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="banos">Baños</label>

                            <select id="banos" name="banos">
                                <option value="">Cualquiera</option>
                                <option value="1" <?php echo $banos === "1" ? "selected" : ""; ?>>
                                    1 o más
                                </option>
                                <option value="2" <?php echo $banos === "2" ? "selected" : ""; ?>>
                                    2 o más
                                </option>
                                <option value="3" <?php echo $banos === "3" ? "selected" : ""; ?>>
                                    3 o más
                                </option>
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
                        <p>Mostrando 6 propiedades disponibles.</p>
                    </div>

                    <form
                        action="catalogo.php"
                        method="GET"
                        class="orden-formulario"
                    >
                        <!-- Conserva los filtros al ordenar -->
                        <input type="hidden" name="busqueda" value="<?php echo $busqueda; ?>">
                        <input type="hidden" name="ciudad" value="<?php echo $ciudad; ?>">
                        <input type="hidden" name="categoria" value="<?php echo $categoria; ?>">
                        <input type="hidden" name="precio_minimo" value="<?php echo $precioMinimo; ?>">
                        <input type="hidden" name="precio_maximo" value="<?php echo $precioMaximo; ?>">
                        <input type="hidden" name="recamaras" value="<?php echo $recamaras; ?>">
                        <input type="hidden" name="banos" value="<?php echo $banos; ?>">

                        <label for="orden">Ordenar por</label>

                        <select
                            id="orden"
                            name="orden"
                            onchange="this.form.submit()"
                        >
                            <option value="">Más recientes</option>

                            <option
                                value="precio-menor"
                                <?php echo $orden === "precio-menor" ? "selected" : ""; ?>
                            >
                                Menor precio
                            </option>

                            <option
                                value="precio-mayor"
                                <?php echo $orden === "precio-mayor" ? "selected" : ""; ?>
                            >
                                Mayor precio
                            </option>
                        </select>
                    </form>

                </div>

                <div class="catalogo-grid">

                    <!-- INMUEBLE 1 -->
                    <article class="card card-inmueble">

                        <div class="card-imagen">
                            <img
                                src="recursos/img/casa1.jpg"
                                alt="Casa moderna en zona residencial"
                            >

                            <span class="estado-inmueble">Disponible</span>
                        </div>

                        <div class="card-contenido">
                            <span class="badge">Casa</span>

                            <h3>Casa moderna en zona residencial</h3>

                            <p class="precio">$2,500,000 MXN</p>

                            <p class="ubicacion">
                                Metepec, Estado de México
                            </p>

                            <div class="caracteristicas">
                                <span>3 recámaras</span>
                                <span>2 baños</span>
                                <span>2 estacionamientos</span>
                            </div>

                            <a
                                href="detalle-inmueble.php?id=1"
                                class="btn btn-secundario btn-completo"
                            >
                                Ver detalle
                            </a>
                        </div>

                    </article>

                    <!-- INMUEBLE 2 -->
                    <article class="card card-inmueble">

                        <div class="card-imagen">
                            <img
                                src="recursos/img/casa2.jpg"
                                alt="Departamento céntrico con amenidades"
                            >

                            <span class="estado-inmueble">Disponible</span>
                        </div>

                        <div class="card-contenido">
                            <span class="badge">Departamento</span>

                            <h3>Departamento céntrico con amenidades</h3>

                            <p class="precio">$1,850,000 MXN</p>

                            <p class="ubicacion">
                                Toluca, Estado de México
                            </p>

                            <div class="caracteristicas">
                                <span>2 recámaras</span>
                                <span>1.5 baños</span>
                                <span>1 estacionamiento</span>
                            </div>

                            <a
                                href="detalle-inmueble.php?id=2"
                                class="btn btn-secundario btn-completo"
                            >
                                Ver detalle
                            </a>
                        </div>

                    </article>

                    <!-- INMUEBLE 3 -->
                    <article class="card card-inmueble">

                        <div class="card-imagen">
                            <img
                                src="recursos/img/casa3.jpg"
                                alt="Residencia amplia con jardín"
                            >

                            <span class="estado-inmueble">Disponible</span>
                        </div>

                        <div class="card-contenido">
                            <span class="badge">Residencia</span>

                            <h3>Residencia amplia con jardín</h3>

                            <p class="precio">$4,200,000 MXN</p>

                            <p class="ubicacion">
                                Santa Fe, Ciudad de México
                            </p>

                            <div class="caracteristicas">
                                <span>4 recámaras</span>
                                <span>3 baños</span>
                                <span>3 estacionamientos</span>
                            </div>

                            <a
                                href="detalle-inmueble.php?id=3"
                                class="btn btn-secundario btn-completo"
                            >
                                Ver detalle
                            </a>
                        </div>

                    </article>

                    <!-- INMUEBLE 4 -->
                    <article class="card card-inmueble">

                        <div class="card-imagen">
                            <img
                                src="recursos/img/casa1.jpg"
                                alt="Casa familiar cerca del centro"
                            >

                            <span class="estado-inmueble">Disponible</span>
                        </div>

                        <div class="card-contenido">
                            <span class="badge">Casa</span>

                            <h3>Casa familiar cerca del centro</h3>

                            <p class="precio">$2,150,000 MXN</p>

                            <p class="ubicacion">
                                Lerma, Estado de México
                            </p>

                            <div class="caracteristicas">
                                <span>3 recámaras</span>
                                <span>2.5 baños</span>
                                <span>2 estacionamientos</span>
                            </div>

                            <a
                                href="detalle-inmueble.php?id=4"
                                class="btn btn-secundario btn-completo"
                            >
                                Ver detalle
                            </a>
                        </div>

                    </article>

                    <!-- INMUEBLE 5 -->
                    <article class="card card-inmueble">

                        <div class="card-imagen">
                            <img
                                src="recursos/img/casa2.jpg"
                                alt="Departamento moderno en zona comercial"
                            >

                            <span class="estado-inmueble">Disponible</span>
                        </div>

                        <div class="card-contenido">
                            <span class="badge">Departamento</span>

                            <h3>Departamento moderno en zona comercial</h3>

                            <p class="precio">$1,620,000 MXN</p>

                            <p class="ubicacion">
                                Naucalpan, Estado de México
                            </p>

                            <div class="caracteristicas">
                                <span>2 recámaras</span>
                                <span>2 baños</span>
                                <span>1 estacionamiento</span>
                            </div>

                            <a
                                href="detalle-inmueble.php?id=5"
                                class="btn btn-secundario btn-completo"
                            >
                                Ver detalle
                            </a>
                        </div>

                    </article>

                    <!-- INMUEBLE 6 -->
                    <article class="card card-inmueble">

                        <div class="card-imagen">
                            <img
                                src="recursos/img/casa3.jpg"
                                alt="Residencia con terraza y jardín"
                            >

                            <span class="estado-inmueble">Disponible</span>
                        </div>

                        <div class="card-contenido">
                            <span class="badge">Residencia</span>

                            <h3>Residencia con terraza y jardín</h3>

                            <p class="precio">$5,100,000 MXN</p>

                            <p class="ubicacion">
                                Interlomas, Estado de México
                            </p>

                            <div class="caracteristicas">
                                <span>4 recámaras</span>
                                <span>3.5 baños</span>
                                <span>3 estacionamientos</span>
                            </div>

                            <a
                                href="detalle-inmueble.php?id=6"
                                class="btn btn-secundario btn-completo"
                            >
                                Ver detalle
                            </a>
                        </div>

                    </article>

                </div>

                <!-- PAGINACIÓN -->
                <nav class="paginacion" aria-label="Paginación del catálogo">
                    <a href="#" class="pagina desactivada">Anterior</a>
                    <a href="#" class="pagina activa">1</a>
                    <a href="#" class="pagina">2</a>
                    <a href="#" class="pagina">3</a>
                    <a href="#" class="pagina">Siguiente</a>
                </nav>

            </div>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
