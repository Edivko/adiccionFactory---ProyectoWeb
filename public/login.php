<?php

session_start();

// Si ya hay sesión activa, redirigir al área correspondiente
if (isset($_SESSION['id_usuario'])) {
    $rutasPorRol = [
        1 => '../comprador/index.php',
        2 => '../vendedor/index.php',
        3 => '../admin/dashboard.php',
    ];
    $idRol = (int) ($_SESSION['id_rol'] ?? 0);
    if (isset($rutasPorRol[$idRol])) {
        header('Location: ' . $rutasPorRol[$idRol]);
        exit;
    }
}

// Recuperar mensajes de sesión y limpiarlos de inmediato
$errorLogin    = $_SESSION['error_login']    ?? '';
$correoAnterior = $_SESSION['correo_login']  ?? '';
$mensajeExito  = $_SESSION['mensaje_exito']  ?? '';

unset(
    $_SESSION['error_login'],
    $_SESSION['correo_login'],
    $_SESSION['mensaje_exito']
);

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

                <?php if ($mensajeExito !== ''): ?>
                    <div class="mensaje mensaje-exito">
                        <?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form
                    action="../procesos/procesar-login.php"
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
                            value="<?php echo htmlspecialchars($correoAnterior, ENT_QUOTES, 'UTF-8'); ?>"
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

                    <?php if ($errorLogin !== ''): ?>
                        <div class="mensaje mensaje-error-general">
                            <?php echo htmlspecialchars($errorLogin, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

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
                        Tu contraseña se almacena protegida mediante un hash
                        y nunca se guarda como texto visible.
                    </p>
                </div>

            </aside>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
