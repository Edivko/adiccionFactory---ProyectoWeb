<?php

session_start();

$errores      = $_SESSION['errores_registro_vendedor'] ?? [];
$datosPrevios = $_SESSION['datos_registro_vendedor']   ?? [];
$errorGeneral = $_SESSION['error_general']             ?? '';

unset(
    $_SESSION['errores_registro_vendedor'],
    $_SESSION['datos_registro_vendedor'],
    $_SESSION['error_general']
);

function val(array $datos, string $campo): string
{
    return htmlspecialchars($datos[$campo] ?? '', ENT_QUOTES, 'UTF-8');
}

function errorCampo(array $errores, string $campo): string
{
    if (!isset($errores[$campo])) {
        return '';
    }
    return '<small class="mensaje mensaje-error">'
        . htmlspecialchars($errores[$campo], ENT_QUOTES, 'UTF-8')
        . '</small>';
}

$tituloPagina = "Registro de vendedor | Adicción Factory Inmobiliaria";
include("includes/header.php");
?>

<main class="pagina-formulario">

    <section class="encabezado-pagina">
        <div class="contenedor">
            <p class="etiqueta">Crear cuenta</p>
            <h1>Registro de vendedor</h1>
            <p>
                Registra tus datos personales y crea tu perfil profesional
                dentro de la inmobiliaria.
            </p>
        </div>
    </section>

    <section class="seccion">
        <div class="contenedor contenedor-formulario">

            <article class="form-card">

                <div class="form-card-encabezado">
                    <h2>Datos personales</h2>
                    <p>Los campos marcados con * son obligatorios.</p>
                </div>

                <?php if ($errorGeneral !== ''): ?>
                    <div class="mensaje mensaje-error-general">
                        <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form
                    action="../procesos/procesar-registro-vendedor.php"
                    method="POST"
                    enctype="multipart/form-data"
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
                                value="<?php echo val($datosPrevios, 'nombre'); ?>"
                                required
                            >
                            <?php echo errorCampo($errores, 'nombre'); ?>
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
                                value="<?php echo val($datosPrevios, 'apellido'); ?>"
                                required
                            >
                            <?php echo errorCampo($errores, 'apellido'); ?>
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
                                value="<?php echo val($datosPrevios, 'correo'); ?>"
                                required
                            >
                            <?php echo errorCampo($errores, 'correo'); ?>
                        </div>

                        <div class="campo">
                            <label for="telefono">Teléfono *</label>
                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                placeholder="Ej. 5512345678"
                                maxlength="20"
                                pattern="[0-9+\s()-]{10,20}"
                                autocomplete="tel"
                                value="<?php echo val($datosPrevios, 'telefono'); ?>"
                                required
                            >
                            <?php echo errorCampo($errores, 'telefono'); ?>
                        </div>

                        <div class="campo">
                            <label for="password">Contraseña *</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Mínimo 8 caracteres"
                                minlength="8"
                                maxlength="72"
                                autocomplete="new-password"
                                required
                            >
                            <small class="ayuda">
                                Utiliza al menos 8 caracteres.
                            </small>
                            <?php echo errorCampo($errores, 'password'); ?>
                        </div>

                        <div class="campo">
                            <label for="confirmar_password">
                                Confirmar contraseña *
                            </label>
                            <input
                                type="password"
                                id="confirmar_password"
                                name="confirmar_password"
                                placeholder="Repite tu contraseña"
                                minlength="8"
                                maxlength="72"
                                autocomplete="new-password"
                                required
                            >
                            <?php echo errorCampo($errores, 'confirmar_password'); ?>
                        </div>

                    </div>

                    <div class="separador-formulario"></div>

                    <div class="form-card-encabezado">
                        <h2>Perfil profesional</h2>
                        <p>
                            Estos datos podrán mostrarse posteriormente en tu perfil
                            público como vendedor.
                        </p>
                    </div>

                    <div class="form-grid">

                        <div class="campo campo-completo">
                            <label for="foto_perfil">
                                Fotografía de perfil
                            </label>
                            <input
                                type="file"
                                id="foto_perfil"
                                name="foto_perfil"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            >
                            <small class="ayuda">
                                Formatos permitidos: JPG, PNG o WEBP. Máximo 5 MB.
                            </small>
                            <?php echo errorCampo($errores, 'foto_perfil'); ?>
                        </div>

                        <div class="campo">
                            <label for="experiencia">
                                Años de experiencia *
                            </label>
                            <input
                                type="number"
                                id="experiencia"
                                name="experiencia"
                                placeholder="Ej. 3"
                                min="0"
                                max="60"
                                value="<?php echo val($datosPrevios, 'experiencia'); ?>"
                                required
                            >
                            <?php echo errorCampo($errores, 'experiencia'); ?>
                        </div>

                        <div class="campo">
                            <label for="zona_trabajo">
                                Zona de trabajo *
                            </label>
                            <input
                                type="text"
                                id="zona_trabajo"
                                name="zona_trabajo"
                                placeholder="Ej. Metepec y Toluca"
                                maxlength="150"
                                value="<?php echo val($datosPrevios, 'zona_trabajo'); ?>"
                                required
                            >
                            <?php echo errorCampo($errores, 'zona_trabajo'); ?>
                        </div>

                        <div class="campo campo-completo">
                            <label for="descripcion">
                                Descripción profesional *
                            </label>
                            <textarea
                                id="descripcion"
                                name="descripcion"
                                placeholder="Describe tu experiencia, especialidad y forma de trabajo."
                                maxlength="1000"
                                required
                            ><?php echo val($datosPrevios, 'descripcion'); ?></textarea>
                            <?php echo errorCampo($errores, 'descripcion'); ?>
                        </div>

                    </div>

                    <label class="opcion-check">
                        <input
                            type="checkbox"
                            name="acepta_terminos"
                            value="1"
                            required
                        >
                        <span>
                            He leído y acepto los términos, condiciones y el aviso
                            de privacidad.
                        </span>
                    </label>
                    <?php echo errorCampo($errores, 'acepta_terminos'); ?>

                    <button
                        type="submit"
                        class="btn btn-principal btn-completo"
                    >
                        Crear cuenta de vendedor
                    </button>

                    <p class="aviso-formulario">
                        El registro podrá quedar sujeto a revisión y aprobación
                        por parte del administrador.
                    </p>

                    <p class="texto-acceso">
                        ¿Ya tienes una cuenta?
                        <a href="login.php">Inicia sesión</a>
                    </p>

                </form>

            </article>

            <aside class="form-info">
                <span class="form-info-icono">02</span>

                <h2>Forma parte del equipo de vendedores</h2>

                <p>
                    Crea un perfil profesional para atender solicitudes y colaborar
                    con la publicación y venta de inmuebles.
                </p>

                <ul class="lista-beneficios">
                    <li>Crear un perfil público profesional.</li>
                    <li>Consultar inmuebles asignados.</li>
                    <li>Recibir solicitudes de citas.</li>
                    <li>Consultar comentarios y calificaciones.</li>
                </ul>
            </aside>

        </div>
    </section>

</main>

<script>
(function () {
    var inputFoto   = document.getElementById('foto_perfil');
    var MAX_BYTES   = 5 * 1024 * 1024;

    if (!inputFoto) return;

    inputFoto.addEventListener('change', function () {
        var archivo = this.files[0];
        if (archivo && archivo.size > MAX_BYTES) {
            alert('La fotografía no puede superar los 5 MB. Selecciona otra imagen.');
            this.value = '';
        }
    });
}());
</script>

<?php
include("includes/footer.php");
?>
