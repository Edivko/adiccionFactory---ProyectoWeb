<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

$idComprador = (int) $_SESSION['id_perfil'];

// ─── Leer mensajes de sesión ─────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito']            ?? null;
$errorGeneral = $_SESSION['error_general']            ?? null;
$errores      = $_SESSION['errores_perfil_comprador'] ?? [];
$datosPrev    = $_SESSION['datos_perfil_comprador']   ?? null;

unset(
    $_SESSION['mensaje_exito'],
    $_SESSION['error_general'],
    $_SESSION['errores_perfil_comprador'],
    $_SESSION['datos_perfil_comprador']
);

// ─── Cargar datos desde la BD ─────────────────────────────────────────────────

$datos = [
    'nombre'             => '',
    'apellido'           => '',
    'correo'             => '',
    'telefono'           => '',
    'presupuesto_minimo' => '',
    'presupuesto_maximo' => '',
    'zona_interes'       => '',
];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            u.nombre,
            u.apellido,
            u.correo,
            u.telefono,
            c.presupuesto_minimo,
            c.presupuesto_maximo,
            c.zona_interes
        FROM Comprador c
        INNER JOIN Usuario u ON u.id_usuario = c.id_usuario
        WHERE c.id_comprador = ?
        LIMIT 1
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idComprador);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($fila !== null) {
        $datos = $fila;
    }
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar la información del perfil.';
}

// Si hubo envío con error, restaurar datos anteriores del formulario
if ($datosPrev !== null) {
    $datos = array_merge($datos, $datosPrev);
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function valCampo(array $datos, string $campo): string {
    return htmlspecialchars((string) ($datos[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
}
function errCampo(array $errores, string $campo): string {
    return isset($errores[$campo])
        ? '<p class="form-error">' . htmlspecialchars($errores[$campo], ENT_QUOTES, 'UTF-8') . '</p>'
        : '';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'perfil.php';
$tituloPagina = 'Mi perfil | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <h2>Mi Perfil</h2>
            <p>Consulta y actualiza tu información personal y datos de contacto.</p>
        </div>

        <?php if ($mensajeExito !== null): ?>
            <div class="mensaje-exito" style="max-width:800px;margin:0 auto 24px;">
                <?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorGeneral !== null): ?>
            <div class="mensaje-error" style="max-width:800px;margin:0 auto 24px;">
                <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width:800px;margin:0 auto;">
            <div class="card-contenido">

                <form class="formulario" action="../procesos/procesar-perfil-comprador.php" method="POST">

                    <div class="grid-2">
                        <div>
                            <label for="nombre">Nombre(s) <span style="color:#e94b27;">*</span></label>
                            <input type="text" id="nombre" name="nombre"
                                   value="<?php echo valCampo($datos, 'nombre'); ?>"
                                   maxlength="100" required>
                            <?php echo errCampo($errores, 'nombre'); ?>
                        </div>
                        <div>
                            <label for="apellido">Apellidos <span style="color:#e94b27;">*</span></label>
                            <input type="text" id="apellido" name="apellido"
                                   value="<?php echo valCampo($datos, 'apellido'); ?>"
                                   maxlength="100" required>
                            <?php echo errCampo($errores, 'apellido'); ?>
                        </div>
                    </div>

                    <div style="margin-top:15px;">
                        <label for="correo">Correo electrónico <span style="color:#e94b27;">*</span></label>
                        <input type="email" id="correo" name="correo"
                               value="<?php echo valCampo($datos, 'correo'); ?>"
                               maxlength="150" required>
                        <?php echo errCampo($errores, 'correo'); ?>
                    </div>

                    <div style="margin-top:15px;">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono"
                               value="<?php echo valCampo($datos, 'telefono'); ?>"
                               maxlength="20" placeholder="10 dígitos">
                        <?php echo errCampo($errores, 'telefono'); ?>
                    </div>

                    <hr style="margin:24px 0;border:none;border-top:1px solid #eee;">
                    <p style="font-weight:600;color:var(--color-oscuro);margin-bottom:16px;">
                        Preferencias de búsqueda
                    </p>

                    <div class="grid-2">
                        <div>
                            <label for="presupuesto_minimo">Presupuesto mínimo (MXN)</label>
                            <input type="number" id="presupuesto_minimo" name="presupuesto_minimo"
                                   value="<?php echo valCampo($datos, 'presupuesto_minimo'); ?>"
                                   min="0" step="1000" placeholder="Opcional">
                            <?php echo errCampo($errores, 'presupuesto_minimo'); ?>
                        </div>
                        <div>
                            <label for="presupuesto_maximo">Presupuesto máximo (MXN)</label>
                            <input type="number" id="presupuesto_maximo" name="presupuesto_maximo"
                                   value="<?php echo valCampo($datos, 'presupuesto_maximo'); ?>"
                                   min="0" step="1000" placeholder="Opcional">
                            <?php echo errCampo($errores, 'presupuesto_maximo'); ?>
                        </div>
                    </div>

                    <div style="margin-top:15px;">
                        <label for="zona_interes">Zona de interés</label>
                        <input type="text" id="zona_interes" name="zona_interes"
                               value="<?php echo valCampo($datos, 'zona_interes'); ?>"
                               maxlength="150" placeholder="Ej. Iztapalapa, CDMX">
                        <?php echo errCampo($errores, 'zona_interes'); ?>
                    </div>

                    <div style="margin-top:30px;text-align:center;">
                        <button type="submit" class="btn btn-principal">Guardar cambios</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
