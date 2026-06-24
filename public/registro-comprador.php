<?php

session_start();

$errores      = $_SESSION['errores_registro_comprador'] ?? [];
$datosPrevios = $_SESSION['datos_registro_comprador']   ?? [];
$errorGeneral = $_SESSION['error_general']              ?? '';

unset(
    $_SESSION['errores_registro_comprador'],
    $_SESSION['datos_registro_comprador'],
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

$tituloPagina = "Registro de comprador | Adicción Factory Inmobiliaria";
include("includes/header.php");
?>

<main class="pagina-formulario">

    <section class="encabezado-pagina">
        <div class="contenedor">
            <p class="etiqueta">Crear cuenta</p>
            <h1>Registro de comprador</h1>
            <p>
                Crea tu cuenta para consultar inmuebles, seleccionar vendedores
                y agendar citas.
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
                    action="../procesos/procesar-registro-comprador.php"
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
                        <h2>Preferencias de búsqueda</h2>
                        <p>
                            Esta información ayudará a mostrarte inmuebles relacionados
                            con tus intereses.
                        </p>
                    </div>

                    <div class="form-grid">

                        <div class="campo">
                            <label for="presupuesto_minimo">
                                Presupuesto mínimo
                            </label>
                            <input
                                type="number"
                                id="presupuesto_minimo"
                                name="presupuesto_minimo"
                                placeholder="Ej. 1000000"
                                min="0"
                                step="0.01"
                                value="<?php echo val($datosPrevios, 'presupuesto_minimo'); ?>"
                            >
                            <?php echo errorCampo($errores, 'presupuesto_minimo'); ?>
                        </div>

                        <div class="campo">
                            <label for="presupuesto_maximo">
                                Presupuesto máximo
                            </label>
                            <input
                                type="number"
                                id="presupuesto_maximo"
                                name="presupuesto_maximo"
                                placeholder="Ej. 3000000"
                                min="0"
                                step="0.01"
                                value="<?php echo val($datosPrevios, 'presupuesto_maximo'); ?>"
                            >
                            <?php echo errorCampo($errores, 'presupuesto_maximo'); ?>
                        </div>

                        <div class="campo campo-completo">
                            <label for="zona_interes">Zona de interés</label>
                            <input
                                type="text"
                                id="zona_interes"
                                name="zona_interes"
                                placeholder="Ej. Metepec, Toluca o CDMX"
                                maxlength="150"
                                value="<?php echo val($datosPrevios, 'zona_interes'); ?>"
                            >
                            <?php echo errorCampo($errores, 'zona_interes'); ?>
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
                        Crear cuenta de comprador
                    </button>

                    <p class="texto-acceso">
                        ¿Ya tienes una cuenta?
                        <a href="login.php">Inicia sesión</a>
                    </p>

                </form>

            </article>

            <aside class="form-info">
                <span class="form-info-icono">01</span>

                <h2>Encuentra tu próximo inmueble</h2>

                <p>
                    Al registrarte como comprador podrás acceder a las funciones
                    necesarias para solicitar una visita.
                </p>

                <ul class="lista-beneficios">
                    <li>Buscar y filtrar inmuebles.</li>
                    <li>Consultar vendedores disponibles.</li>
                    <li>Agendar y administrar citas.</li>
                    <li>Calificar inmuebles y vendedores.</li>
                </ul>
            </aside>

        </div>
    </section>

</main>

<?php
include("includes/footer.php");
?>
