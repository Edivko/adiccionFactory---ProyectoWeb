<?php

session_start();
require_once __DIR__ . '/../config/conexion.php';

// ─── Leer y limpiar variables de sesión ──────────────────────────────────────

$errores      = $_SESSION['errores_contacto']        ?? [];
$datosPrevios = $_SESSION['datos_contacto']           ?? [];
$errorGeneral = $_SESSION['error_general']            ?? '';
$mensajeExito = $_SESSION['mensaje_exito_contacto']   ?? '';

unset(
    $_SESSION['errores_contacto'],
    $_SESSION['datos_contacto'],
    $_SESSION['error_general'],
    $_SESSION['mensaje_exito_contacto']
);

// ─── Funciones de presentación ────────────────────────────────────────────────

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

// ─── Obtener motivos del catálogo para el <select> ────────────────────────────

$motivos = [];
try {
    $stmtMot = mysqli_prepare(
        $conexion,
        'SELECT id_motivo, nombre_motivo FROM MotivoContacto ORDER BY id_motivo ASC'
    );
    mysqli_stmt_execute($stmtMot);
    $motivos = mysqli_fetch_all(mysqli_stmt_get_result($stmtMot), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtMot);
} catch (mysqli_sql_exception $e) {
    // Si falla la carga de motivos el formulario se muestra sin opciones
}

$motivoSeleccionado = $datosPrevios['motivo'] ?? '';

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

                <?php if ($mensajeExito !== ''): ?>
                    <div class="mensaje mensaje-exito">
                        <?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorGeneral !== ''): ?>
                    <div class="mensaje mensaje-error-general">
                        <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form
                    action="../procesos/procesar-contacto.php"
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
                            <label for="telefono">Teléfono</label>

                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                placeholder="Ej. 5512345678"
                                maxlength="20"
                                pattern="[0-9+\s()-]{10,20}"
                                autocomplete="tel"
                                value="<?php echo val($datosPrevios, 'telefono'); ?>"
                            >
                            <?php echo errorCampo($errores, 'telefono'); ?>
                        </div>

                        <div class="campo campo-completo">
                            <label for="motivo">Motivo de contacto *</label>

                            <select
                                id="motivo"
                                name="motivo"
                                required
                            >
                                <option value="">Selecciona una opción</option>
                                <?php foreach ($motivos as $m): ?>
                                    <option
                                        value="<?php echo (int) $m['id_motivo']; ?>"
                                        <?php echo (string) $motivoSeleccionado === (string) $m['id_motivo'] ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($m['nombre_motivo'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php echo errorCampo($errores, 'motivo'); ?>
                        </div>

                        <div class="campo campo-completo">
                            <label for="asunto">Asunto *</label>

                            <input
                                type="text"
                                id="asunto"
                                name="asunto"
                                placeholder="Escribe el asunto del mensaje"
                                maxlength="150"
                                value="<?php echo val($datosPrevios, 'asunto'); ?>"
                                required
                            >
                            <?php echo errorCampo($errores, 'asunto'); ?>
                        </div>

                        <div class="campo campo-completo">
                            <label for="mensaje">Mensaje *</label>

                            <textarea
                                id="mensaje"
                                name="mensaje"
                                placeholder="Escribe tu mensaje"
                                maxlength="1500"
                                required
                            ><?php echo val($datosPrevios, 'mensaje'); ?></textarea>

                            <small class="ayuda">
                                Máximo 1500 caracteres.
                            </small>
                            <?php echo errorCampo($errores, 'mensaje'); ?>
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
                    <?php echo errorCampo($errores, 'acepta_privacidad'); ?>

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
