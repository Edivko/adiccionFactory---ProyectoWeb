<?php
$tituloPagina = "Iniciar sesión | Adicción Factory Inmobiliaria";
include("includes/header.php");
?>

<main class="pagina-formulario">

    <section class="encabezado-pagina">
        <div class="contenedor">
            <p class="etiqueta">Acceso al sistema</p>
            <h1>Iniciar sesión</h1>
            <p>
                Ingresa con tu correo electrónico y contraseña para acceder
                a las funciones de tu cuenta.
            </p>
        </div>
    </section>

    <section class="seccion">
        <div class="contenedor login-contenedor">

            <article class="form-card login-card">

                <div class="form-card-encabezado">
                    <h2>Bienvenido</h2>
                    <p>Ingresa tus datos para continuar.</p>
                </div>

                <form
                    action=""
                    method="POST"
                    class="formulario"
                    autocomplete="on"
                >

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
                        <label for="password">Contraseña *</label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Ingresa tu contraseña"
                            minlength="8"
                            maxlength="72"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="login-opciones">

                        <label class="opcion-check opcion-check-login">
                            <input
                                type="checkbox"
                                name="recordar"
                                value="1"
                            >

                            <span>Recordar sesión</span>
                        </label>

                        <a href="#" class="enlace-recuperacion">
                            ¿Olvidaste tu contraseña?
                        </a>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-principal btn-completo"
                    >
                        Iniciar sesión
                    </button>

                </form>

                <div class="separador-texto">
                    <span>¿Todavía no tienes cuenta?</span>
                </div>

                <div class="registro-opciones">

                    <a
                        href="registro-comprador.php"
                        class="btn btn-claro btn-completo"
                    >
                        Registrarme como comprador
                    </a>

                    <a
                        href="registro-vendedor.php"
                        class="btn btn-secundario btn-completo"
                    >
                        Registrarme como vendedor
                    </a>

                </div>

            </article>

            <aside class="form-info login-info">

                <span class="form-info-icono">03</span>

                <h2>Accede a tu cuenta</h2>

                <p>
                    El sistema identificará el tipo de usuario y mostrará
                    las funciones correspondientes.
                </p>

                <ul class="lista-beneficios">
                    <li>Los compradores podrán consultar y administrar citas.</li>
                    <li>Los vendedores podrán revisar solicitudes recibidas.</li>
                    <li>Los administradores podrán gestionar el sistema.</li>
                </ul>

                <div class="aviso-seguridad">
                    <strong>Seguridad</strong>

                    <p>
                        La contraseña no se almacenará como texto visible.
                        Posteriormente será protegida mediante un hash.
                    </p>
                </div>

            </aside>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
