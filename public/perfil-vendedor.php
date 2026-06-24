<?php
$tituloPagina = "Perfil del vendedor | Adicción Factory Inmobiliaria";
include("includes/header.php");

/*
 * Más adelante este ID se utilizará para consultar
 * la información del vendedor en la base de datos.
 */
$idVendedor = isset($_GET["id"]) ? (int) $_GET["id"] : 1;
?>

<main>

    <section class="encabezado-pagina encabezado-vendedor">
        <div class="contenedor">

            <nav class="migas-pan" aria-label="Ruta de navegación">
                <a href="index.php">Inicio</a>
                <span>/</span>
                <a href="catalogo.php">Catálogo</a>
                <span>/</span>
                <span>Perfil del vendedor</span>
            </nav>

            <p class="etiqueta">Asesor inmobiliario</p>
            <h1>Perfil público del vendedor</h1>

            <p>
                Consulta su experiencia, zona de trabajo, inmuebles asignados
                y opiniones recibidas.
            </p>

        </div>
    </section>

    <section class="seccion perfil-vendedor-seccion">
        <div class="contenedor perfil-vendedor-layout">

            <!-- INFORMACIÓN PRINCIPAL -->
            <section class="perfil-vendedor-contenido">

                <article class="perfil-principal">

                    <div class="perfil-foto">
                        <img
                            src="recursos/img/vendedor1.jpg"
                            alt="Fotografía del vendedor Carlos Hernández"
                        >
                    </div>

                    <div class="perfil-informacion">

                        <span class="badge">Vendedor verificado</span>

                        <h2>Carlos Hernández</h2>

                        <p class="perfil-zona">
                            Metepec y Toluca, Estado de México
                        </p>

                        <div class="perfil-calificacion">
                            <span class="estrellas">★★★★★</span>
                            <strong>4.8</strong>
                            <small>Basado en 18 calificaciones</small>
                        </div>

                        <p class="perfil-descripcion">
                            Asesor inmobiliario especializado en propiedades
                            residenciales. Brindo acompañamiento durante el proceso
                            de selección, visita y adquisición del inmueble.
                        </p>

                        <div class="perfil-datos-grid">

                            <div class="perfil-dato">
                                <strong>5 años</strong>
                                <span>Experiencia</span>
                            </div>

                            <div class="perfil-dato">
                                <strong>12</strong>
                                <span>Inmuebles asignados</span>
                            </div>

                            <div class="perfil-dato">
                                <strong>34</strong>
                                <span>Citas realizadas</span>
                            </div>

                        </div>

                    </div>

                </article>

                <!-- SOBRE EL VENDEDOR -->
                <article class="detalle-bloque">
                    <h2>Sobre el vendedor</h2>

                    <p>
                        Carlos cuenta con experiencia en la atención de compradores
                        interesados en casas, departamentos y residencias ubicadas
                        principalmente en Metepec y Toluca.
                    </p>

                    <p>
                        Su objetivo es ofrecer información clara sobre cada propiedad,
                        resolver dudas y facilitar la programación de visitas.
                    </p>
                </article>

                <!-- INMUEBLES ASIGNADOS -->
                <article class="detalle-bloque">

                    <div class="titulo-con-accion">

                        <div>
                            <p class="etiqueta etiqueta-oscura">
                                Propiedades
                            </p>

                            <h2>Inmuebles que atiende</h2>
                        </div>

                        <a href="catalogo.php" class="enlace-seccion">
                            Ver catálogo completo
                        </a>

                    </div>

                    <div class="perfil-inmuebles-grid">

                        <article class="card card-inmueble">

                            <div class="card-imagen">
                                <img
                                    src="recursos/img/casa1.jpg"
                                    alt="Casa moderna en zona residencial"
                                >

                                <span class="estado-inmueble">
                                    Disponible
                                </span>
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
                                </div>

                                <a
                                    href="detalle-inmueble.php?id=1"
                                    class="btn btn-secundario btn-completo"
                                >
                                    Ver detalle
                                </a>
                            </div>

                        </article>

                        <article class="card card-inmueble">

                            <div class="card-imagen">
                                <img
                                    src="recursos/img/casa2.jpg"
                                    alt="Departamento céntrico con amenidades"
                                >

                                <span class="estado-inmueble">
                                    Disponible
                                </span>
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
                                </div>

                                <a
                                    href="detalle-inmueble.php?id=2"
                                    class="btn btn-secundario btn-completo"
                                >
                                    Ver detalle
                                </a>
                            </div>

                        </article>

                    </div>

                </article>

                <!-- COMENTARIOS -->
                <article class="detalle-bloque">

                    <div class="titulo-con-accion">

                        <div>
                            <p class="etiqueta etiqueta-oscura">
                                Experiencias
                            </p>

                            <h2>Comentarios recibidos</h2>
                        </div>

                        <span class="calificacion-general">
                            ★ 4.8
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
                                Explicó claramente las características del inmueble
                                y respondió todas nuestras preguntas durante la visita.
                            </p>

                        </article>

                        <article class="comentario">

                            <div class="comentario-cabecera">

                                <div>
                                    <strong>Luis Ramírez</strong>
                                    <span>4 de junio de 2026</span>
                                </div>

                                <span class="comentario-estrellas">
                                    ★★★★★
                                </span>

                            </div>

                            <p>
                                La atención fue puntual y profesional. Nos mostró
                                distintas opciones de acuerdo con nuestro presupuesto.
                            </p>

                        </article>

                        <article class="comentario">

                            <div class="comentario-cabecera">

                                <div>
                                    <strong>María González</strong>
                                    <span>27 de mayo de 2026</span>
                                </div>

                                <span class="comentario-estrellas">
                                    ★★★★☆
                                </span>

                            </div>

                            <p>
                                Buena comunicación y disponibilidad para organizar
                                la visita al inmueble.
                            </p>

                        </article>

                    </div>

                </article>

            </section>

            <!-- PANEL LATERAL -->
            <aside class="perfil-vendedor-resumen">

                <div class="perfil-resumen-card">

                    <span class="form-info-icono">CV</span>

                    <h2>Contacta a este vendedor</h2>

                    <p>
                        Para solicitar una cita debes elegir primero uno de los
                        inmuebles que el vendedor tiene asignados.
                    </p>

                    <a
                        href="catalogo.php"
                        class="btn btn-principal btn-completo"
                    >
                        Ver inmuebles
                    </a>

                    <a
                        href="login.php"
                        class="btn btn-secundario btn-completo"
                    >
                        Iniciar sesión
                    </a>

                    <div class="resumen-separador"></div>

                    <div class="resumen-dato">
                        <span>ID del vendedor</span>
                        <strong>#<?php echo $idVendedor; ?></strong>
                    </div>

                    <div class="resumen-dato">
                        <span>Estado</span>
                        <strong>Verificado</strong>
                    </div>

                    <div class="resumen-dato">
                        <span>Zona de trabajo</span>
                        <strong>Metepec y Toluca</strong>
                    </div>

                    <div class="resumen-dato">
                        <span>Experiencia</span>
                        <strong>5 años</strong>
                    </div>

                </div>

            </aside>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
