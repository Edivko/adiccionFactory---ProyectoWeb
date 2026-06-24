i<?php
$tituloPagina = "Detalle del inmueble | Adicción Factory Inmobiliaria";
include("includes/header.php");

/*
 * Por ahora el ID solo se recibe para dejar preparada la ruta.
 * Después se usará para consultar el inmueble en la base de datos.
 */
$idInmueble = isset($_GET["id"]) ? (int) $_GET["id"] : 1;
?>

<main>

    <section class="encabezado-pagina encabezado-detalle">
        <div class="contenedor">

            <nav class="migas-pan" aria-label="Ruta de navegación">
                <a href="index.php">Inicio</a>
                <span>/</span>
                <a href="catalogo.php">Catálogo</a>
                <span>/</span>
                <span>Detalle del inmueble</span>
            </nav>

            <p class="etiqueta">Propiedad disponible</p>

            <h1>Casa moderna en zona residencial</h1>

            <p>
                Metepec, Estado de México
            </p>

        </div>
    </section>

    <section class="seccion detalle-seccion">
        <div class="contenedor">

            <!-- GALERÍA -->
            <div class="galeria-inmueble">

                <div class="galeria-principal">
                    <img
                        src="recursos/img/casa1.jpg"
                        alt="Vista principal de la casa"
                    >
                </div>

                <div class="galeria-miniaturas">
                    <button type="button" class="miniatura activa">
                        <img
                            src="recursos/img/casa1.jpg"
                            alt="Fachada del inmueble"
                        >
                    </button>

                    <button type="button" class="miniatura">
                        <img
                            src="recursos/img/casa2.jpg"
                            alt="Sala del inmueble"
                        >
                    </button>

                    <button type="button" class="miniatura">
                        <img
                            src="recursos/img/casa3.jpg"
                            alt="Jardín del inmueble"
                        >
                    </button>
                </div>

            </div>

            <!-- INFORMACIÓN PRINCIPAL -->
            <div class="detalle-layout">

                <div class="detalle-contenido">

                    <article class="detalle-bloque">

                        <div class="detalle-cabecera">

                            <div>
                                <span class="badge">Casa</span>
                                <h2>Casa moderna en zona residencial</h2>
                                <p class="detalle-ubicacion">
                                    Metepec, Estado de México
                                </p>
                            </div>

                            <div class="detalle-precio">
                                <span>Precio</span>
                                <strong>$2,500,000 MXN</strong>
                            </div>

                        </div>

                        <div class="detalle-caracteristicas">

                            <div class="caracteristica-detalle">
                                <strong>3</strong>
                                <span>Recámaras</span>
                            </div>

                            <div class="caracteristica-detalle">
                                <strong>2</strong>
                                <span>Baños</span>
                            </div>

                            <div class="caracteristica-detalle">
                                <strong>2</strong>
                                <span>Estacionamientos</span>
                            </div>

                            <div class="caracteristica-detalle">
                                <strong>180 m²</strong>
                                <span>Construcción</span>
                            </div>

                            <div class="caracteristica-detalle">
                                <strong>220 m²</strong>
                                <span>Terreno</span>
                            </div>

                            <div class="caracteristica-detalle">
                                <strong>5 años</strong>
                                <span>Antigüedad</span>
                            </div>

                        </div>

                    </article>

                    <article class="detalle-bloque">
                        <h2>Descripción</h2>

                        <p>
                            Casa moderna ubicada en una zona residencial tranquila,
                            con acceso cercano a escuelas, centros comerciales y vías
                            principales.
                        </p>

                        <p>
                            La propiedad cuenta con sala, comedor, cocina integral,
                            tres recámaras, dos baños completos, jardín posterior y
                            espacio para dos automóviles.
                        </p>
                    </article>

                    <article class="detalle-bloque">
                        <h2>Ubicación</h2>

                        <div class="datos-ubicacion">
                            <div>
                                <span>Estado</span>
                                <strong>Estado de México</strong>
                            </div>

                            <div>
                                <span>Ciudad</span>
                                <strong>Metepec</strong>
                            </div>

                            <div>
                                <span>Colonia</span>
                                <strong>Zona residencial</strong>
                            </div>

                            <div>
                                <span>Código postal</span>
                                <strong>52140</strong>
                            </div>
                        </div>

                        <p class="nota-ubicacion">
                            La dirección completa podrá mostrarse al confirmar una cita.
                        </p>
                    </article>

                    <article class="detalle-bloque">

                        <div class="detalle-columnas">

                            <div>
                                <h2>Servicios</h2>

                                <ul class="lista-detalle">
                                    <li>Agua potable</li>
                                    <li>Energía eléctrica</li>
                                    <li>Drenaje</li>
                                    <li>Internet disponible</li>
                                </ul>
                            </div>

                            <div>
                                <h2>Amenidades</h2>

                                <ul class="lista-detalle">
                                    <li>Jardín privado</li>
                                    <li>Cocina integral</li>
                                    <li>Seguridad privada</li>
                                    <li>Área de lavado</li>
                                </ul>
                            </div>

                        </div>

                    </article>

                    <!-- VENDEDORES -->
                    <article class="detalle-bloque">

                        <div class="titulo-con-accion">
                            <div>
                                <p class="etiqueta etiqueta-oscura">
                                    Atención disponible
                                </p>
                                <h2>Vendedores asignados</h2>
                            </div>
                        </div>

                        <p class="texto-intro">
                            Selecciona al vendedor con quien deseas recibir atención
                            para este inmueble.
                        </p>

                        <div class="vendedores-grid">

                            <article class="vendedor-card">

                                <img
                                    src="recursos/img/vendedor1.jpg"
                                    alt="Fotografía del vendedor Carlos Hernández"
                                >

                                <div class="vendedor-card-contenido">

                                    <h3>Carlos Hernández</h3>

                                    <p class="vendedor-zona">
                                        Zona de trabajo: Metepec y Toluca
                                    </p>

                                    <div class="vendedor-datos">
                                        <span>5 años de experiencia</span>
                                        <span>★ 4.8</span>
                                    </div>

                                    <div class="vendedor-acciones">
                                        <a
                                            href="perfil-vendedor.php?id=1"
                                            class="btn btn-claro"
                                        >
                                            Ver perfil
                                        </a>

                                        <a
                                            href="login.php"
                                            class="btn btn-principal"
                                        >
                                            Agendar cita
                                        </a>
                                    </div>

                                </div>

                            </article>

                            <article class="vendedor-card">

                                <img
                                    src="recursos/img/vendedor1.jpg"
                                    alt="Fotografía de la vendedora Mariana López"
                                >

                                <div class="vendedor-card-contenido">

                                    <h3>Mariana López</h3>

                                    <p class="vendedor-zona">
                                        Zona de trabajo: Metepec
                                    </p>

                                    <div class="vendedor-datos">
                                        <span>3 años de experiencia</span>
                                        <span>★ 4.6</span>
                                    </div>

                                    <div class="vendedor-acciones">
                                        <a
                                            href="perfil-vendedor.php?id=2"
                                            class="btn btn-claro"
                                        >
                                            Ver perfil
                                        </a>

                                        <a
                                            href="login.php"
                                            class="btn btn-principal"
                                        >
                                            Agendar cita
                                        </a>
                                    </div>

                                </div>

                            </article>

                        </div>

                        <p class="aviso-visitante">
                            Debes iniciar sesión como comprador para seleccionar
                            un vendedor y agendar una cita.
                        </p>

                    </article>

                    <!-- COMENTARIOS -->
                    <article class="detalle-bloque">

                        <div class="titulo-con-accion">
                            <div>
                                <p class="etiqueta etiqueta-oscura">
                                    Opiniones
                                </p>
                                <h2>Comentarios del inmueble</h2>
                            </div>

                            <span class="calificacion-general">
                                ★ 4.7
                            </span>
                        </div>

                        <div class="comentarios-lista">

                            <article class="comentario">
                                <div class="comentario-cabecera">
                                    <div>
                                        <strong>Ana Martínez</strong>
                                        <span>12 de junio de 2026</span>
                                    </div>

                                    <span class="comentario-estrellas">
                                        ★★★★★
                                    </span>
                                </div>

                                <p>
                                    La zona es tranquila y la propiedad tiene muy
                                    buena distribución. La atención fue clara.
                                </p>
                            </article>

                            <article class="comentario">
                                <div class="comentario-cabecera">
                                    <div>
                                        <strong>Luis Ramírez</strong>
                                        <span>5 de junio de 2026</span>
                                    </div>

                                    <span class="comentario-estrellas">
                                        ★★★★☆
                                    </span>
                                </div>

                                <p>
                                    La casa coincide con las fotografías y se encuentra
                                    cerca de servicios importantes.
                                </p>
                            </article>

                        </div>

                    </article>

                </div>

                <!-- RESUMEN LATERAL -->
                <aside class="detalle-resumen">

                    <div class="detalle-resumen-card">

                        <span class="estado-inmueble estado-estatico">
                            Disponible
                        </span>

                        <h2>¿Te interesa este inmueble?</h2>

                        <p>
                            Inicia sesión como comprador para elegir un vendedor
                            y solicitar una cita.
                        </p>

                        <a
                            href="login.php"
                            class="btn btn-principal btn-completo"
                        >
                            Iniciar sesión
                        </a>

                        <a
                            href="registro-comprador.php"
                            class="btn btn-secundario btn-completo"
                        >
                            Crear cuenta de comprador
                        </a>

                        <div class="resumen-separador"></div>

                        <div class="resumen-dato">
                            <span>ID del inmueble</span>
                            <strong>#<?php echo $idInmueble; ?></strong>
                        </div>

                        <div class="resumen-dato">
                            <span>Condición</span>
                            <strong>Seminuevo</strong>
                        </div>

                        <div class="resumen-dato">
                            <span>Publicado</span>
                            <strong>18 de junio de 2026</strong>
                        </div>

                    </div>

                </aside>

            </div>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
