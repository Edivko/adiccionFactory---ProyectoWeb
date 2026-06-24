<?php
$tituloPagina = "Contacto | Adicción Factory Inmobiliaria";
include("includes/header.php");
?>

<main>

    <section class="encabezado-pagina encabezado-contacto">
        <div class="contenedor">

            <nav class="migas-pan" aria-label="Ruta de navegación">
                <a href="index.php">Inicio</a>
                <span>/</span>
                <span>Contacto</span>
            </nav>

            <p class="etiqueta">Estamos para ayudarte</p>

            <h1>Contacta a la inmobiliaria</h1>

            <p>
                Envíanos tus dudas, comentarios o solicitudes de información.
                Nuestro equipo se pondrá en contacto contigo.
            </p>

        </div>
    </section>

    <section class="seccion contacto-seccion">
        <div class="contenedor contacto-layout">

            <!-- INFORMACIÓN DE CONTACTO -->
            <aside class="contacto-informacion">

                <div class="contacto-info-card">

                    <span class="form-info-icono">AF</span>

                    <h2>Adicción Factory Inmobiliaria</h2>

                    <p>
                        Atención para compradores, vendedores y personas interesadas
                        en conocer nuestros inmuebles disponibles.
                    </p>

                    <div class="contacto-lista">

                        <div class="contacto-item">
                            <span>Teléfono</span>
                            <a href="tel:+525512345678">
                                55 1234 5678
                            </a>
                        </div>

                        <div class="contacto-item">
                            <span>Correo electrónico</span>
                            <a href="mailto:contacto@adiccionfactory.com">
                                contacto@adiccionfactory.com
                            </a>
                        </div>

                        <div class="contacto-item">
                            <span>Ubicación</span>
                            <strong>
                                Estado de México, México
                            </strong>
                        </div>

                        <div class="contacto-item">
                            <span>Horario de atención</span>
                            <strong>
                                Lunes a viernes de 9:00 a 18:00
                            </strong>
                        </div>

                    </div>

                    <div class="contacto-acceso">
                        <h3>¿Buscas un inmueble?</h3>

                        <p>
                            Consulta las propiedades disponibles y conoce
                            a los vendedores asignados.
                        </p>

                        <a
                            href="catalogo.php"
                            class="btn btn-secundario btn-completo"
                        >
                            Ver catálogo
                        </a>
                    </div>

                </div>

            </aside>

            <!-- FORMULARIO -->
            <section class="form-card contacto-form-card">

                <div class="form-card-encabezado">
                    <p class="etiqueta etiqueta-oscura">Escríbenos</p>
                    <h2>Formulario de contacto</h2>

                    <p>
                        Completa los campos y describe brevemente el motivo
                        de tu mensaje.
                    </p>
                </div>

                <form
                    action=""
                    method="POST"
                    class="formulario"
                    autocomplete="on"
                >

                    <div class="form-grid">

                        <div class="campo">
                            <label for="nombre">Nombre *</label>

                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                placeholder="Ingresa tu nombre"
                                maxlength="100"
                                autocomplete="given-name"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="apellido">Apellido *</label>

                            <input
                                type="text"
                                id="apellido"
                                name="apellido"
                                placeholder="Ingresa tu apellido"
                                maxlength="100"
                                autocomplete="family-name"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="correo">Correo electrónico *</label>

                            <input
                                type="email"
                                id="correo"
                                name="correo"
                                placeholder="ejemplo@correo.com"
                                maxlength="150"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="telefono">Teléfono</label>

                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                placeholder="Ej. 5512345678"
                                maxlength="20"
                                pattern="[0-9+\s()-]{10,20}"
                                autocomplete="tel"
                            >
                        </div>

                        <div class="campo campo-completo">
                            <label for="motivo">Motivo de contacto *</label>

                            <select
                                id="motivo"
                                name="motivo"
                                required
                            >
                                <option value="">Selecciona una opción</option>
                                <option value="informacion-inmueble">
                                    Información sobre un inmueble
                                </option>
                                <option value="registro-comprador">
                                    Ayuda con registro de comprador
                                </option>
                                <option value="registro-vendedor">
                                    Ayuda con registro de vendedor
                                </option>
                                <option value="cita">
                                    Información sobre citas
                                </option>
                                <option value="soporte">
                                    Soporte general
                                </option>
                                <option value="otro">
                                    Otro
                                </option>
                            </select>
                        </div>

                        <div class="campo campo-completo">
                            <label for="asunto">Asunto *</label>

                            <input
                                type="text"
                                id="asunto"
                                name="asunto"
                                placeholder="Escribe el asunto del mensaje"
                                maxlength="150"
                                required
                            >
                        </div>

                        <div class="campo campo-completo">
                            <label for="mensaje">Mensaje *</label>

                            <textarea
                                id="mensaje"
                                name="mensaje"
                                placeholder="Escribe tu mensaje"
                                maxlength="1500"
                                required
                            ></textarea>

                            <small class="ayuda">
                                Máximo 1500 caracteres.
                            </small>
                        </div>

                    </div>

                    <label class="opcion-check">
                        <input
                            type="checkbox"
                            name="acepta_privacidad"
                            value="1"
                            required
                        >

                        <span>
                            Acepto el tratamiento de mis datos conforme
                            al aviso de privacidad.
                        </span>
                    </label>

                    <button
                        type="submit"
                        class="btn btn-principal btn-completo"
                    >
                        Enviar mensaje
                    </button>

                </form>

            </section>

        </div>
    </section>

    <!-- INFORMACIÓN ADICIONAL -->
    <section class="seccion seccion-clara">
        <div class="contenedor">

            <div class="titulo-seccion">
                <p class="etiqueta etiqueta-oscura">Atención</p>
                <h2>También puedes comenzar desde aquí</h2>

                <p>
                    Accede directamente a las principales opciones
                    de la plataforma.
                </p>
            </div>

            <div class="contacto-opciones-grid">

                <article class="contacto-opcion">
                    <span>01</span>

                    <h3>Explorar inmuebles</h3>

                    <p>
                        Consulta casas, departamentos, terrenos
                        y residencias disponibles.
                    </p>

                    <a href="catalogo.php">
                        Ir al catálogo
                    </a>
                </article>

                <article class="contacto-opcion">
                    <span>02</span>

                    <h3>Crear cuenta de comprador</h3>

                    <p>
                        Regístrate para seleccionar vendedores
                        y solicitar citas.
                    </p>

                    <a href="registro-comprador.php">
                        Registrarme
                    </a>
                </article>

                <article class="contacto-opcion">
                    <span>03</span>

                    <h3>Crear cuenta de vendedor</h3>

                    <p>
                        Registra tu perfil profesional para colaborar
                        con la inmobiliaria.
                    </p>

                    <a href="registro-vendedor.php">
                        Registro vendedor
                    </a>
                </article>

            </div>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
