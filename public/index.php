<?php
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

            <div class="grid-3">

                <article class="card card-inmueble">
                    <img 
                        src="recursos/img/casa1.jpg" 
                        alt="Casa moderna en venta"
                    >

                    <div class="card-contenido">
                        <span class="badge">Casa</span>
                        <h3>Casa moderna en zona residencial</h3>
                        <p class="precio">$2,500,000 MXN</p>
                        <p class="ubicacion">Metepec, Estado de México</p>

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

                <article class="card card-inmueble">
                    <img 
                        src="recursos/img/casa2.jpg" 
                        alt="Departamento en venta"
                    >

                    <div class="card-contenido">
                        <span class="badge">Departamento</span>
                        <h3>Departamento céntrico con amenidades</h3>
                        <p class="precio">$1,850,000 MXN</p>
                        <p class="ubicacion">Toluca, Estado de México</p>

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

                <article class="card card-inmueble">
                    <img 
                        src="recursos/img/casa3.jpeg" 
                        alt="Residencia en venta"
                    >

                    <div class="card-contenido">
                        <span class="badge">Residencia</span>
                        <h3>Residencia amplia con jardín</h3>
                        <p class="precio">$4,200,000 MXN</p>
                        <p class="ubicacion">Santa Fe, CDMX</p>

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

            </div>

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