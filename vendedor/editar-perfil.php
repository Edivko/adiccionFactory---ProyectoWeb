<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idUsuario = (int) $_SESSION['id_usuario'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$errorGeneral = $_SESSION['error_general']              ?? null;
$errores      = $_SESSION['errores_perfil_vendedor']    ?? [];
$datosPrev    = $_SESSION['datos_perfil_vendedor']      ?? null;
unset(
    $_SESSION['error_general'],
    $_SESSION['errores_perfil_vendedor'],
    $_SESSION['datos_perfil_vendedor']
);

// ─── Cargar datos actuales ────────────────────────────────────────────────────

$datos = [
    'nombre'      => '',
    'apellido'    => '',
    'correo'      => '',
    'telefono'    => '',
    'descripcion' => '',
    'experiencia' => '',
    'zona_trabajo'=> '',
    'foto_perfil' => '',
];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT u.nombre, u.apellido, u.correo, u.telefono,
               v.descripcion, v.experiencia, v.zona_trabajo, v.foto_perfil
        FROM Vendedor v
        INNER JOIN Usuario u ON u.id_usuario = v.id_usuario
        WHERE v.id_usuario = ?
        LIMIT 1
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($fila !== null) {
        $datos = array_merge($datos, $fila);
    }
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar la información del perfil.';
}

if ($datosPrev !== null) {
    $datos = array_merge($datos, $datosPrev);
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'editar-perfil.php';
$tituloPagina = 'Editar perfil | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';

$v = fn(string $campo): string =>
    htmlspecialchars((string) ($datos[$campo] ?? ''), ENT_QUOTES, 'UTF-8');

$e = fn(string $campo): string => isset($errores[$campo])
    ? '<p class="form-error">' . htmlspecialchars($errores[$campo], ENT_QUOTES, 'UTF-8') . '</p>'
    : '';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Configuración</p>
            <h2>Editar información</h2>
            <p>Mantén tus datos actualizados para que los compradores puedan contactarte fácilmente.</p>
        </div>

        <?php if ($errorGeneral !== null): ?>
            <div class="mensaje-error" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-contenido">
                <form action="../procesos/procesar-perfil-vendedor.php"
                      method="POST"
                      enctype="multipart/form-data"
                      class="formulario">

                    <div class="grid-2">

                        <!-- Columna 1: Datos de Usuario -->
                        <div>
                            <h3 style="margin-bottom:15px;border-bottom:1px solid var(--color-borde);padding-bottom:5px;">
                                Datos de acceso y contacto
                            </h3>

                            <div style="margin-bottom:15px;">
                                <label for="nombre">Nombre(s) <span style="color:#e94b27;">*</span></label>
                                <input type="text" id="nombre" name="nombre"
                                       value="<?php echo $v('nombre'); ?>"
                                       maxlength="100" required>
                                <?php echo $e('nombre'); ?>
                            </div>

                            <div style="margin-bottom:15px;">
                                <label for="apellido">Apellidos <span style="color:#e94b27;">*</span></label>
                                <input type="text" id="apellido" name="apellido"
                                       value="<?php echo $v('apellido'); ?>"
                                       maxlength="100" required>
                                <?php echo $e('apellido'); ?>
                            </div>

                            <div style="margin-bottom:15px;">
                                <label for="correo">Correo electrónico <span style="color:#e94b27;">*</span></label>
                                <input type="email" id="correo" name="correo"
                                       value="<?php echo $v('correo'); ?>"
                                       maxlength="150" required>
                                <?php echo $e('correo'); ?>
                            </div>

                            <div>
                                <label for="telefono">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono"
                                       value="<?php echo $v('telefono'); ?>"
                                       maxlength="20">
                                <?php echo $e('telefono'); ?>
                            </div>
                        </div>

                        <!-- Columna 2: Datos de Vendedor -->
                        <div>
                            <h3 style="margin-bottom:15px;border-bottom:1px solid var(--color-borde);padding-bottom:5px;">
                                Perfil público
                            </h3>

                            <div style="margin-bottom:15px;">
                                <label for="zona_trabajo">Zona de trabajo principal</label>
                                <input type="text" id="zona_trabajo" name="zona_trabajo"
                                       value="<?php echo $v('zona_trabajo'); ?>"
                                       maxlength="150">
                                <?php echo $e('zona_trabajo'); ?>
                            </div>

                            <div style="margin-bottom:15px;">
                                <label for="experiencia">Años de experiencia</label>
                                <input type="number" id="experiencia" name="experiencia"
                                       value="<?php echo $v('experiencia'); ?>"
                                       min="0" max="99">
                                <?php echo $e('experiencia'); ?>
                            </div>

                            <div>
                                <label for="foto_perfil">Actualizar fotografía de perfil</label>
                                <input type="file" id="foto_perfil" name="foto_perfil"
                                       accept="image/jpeg,image/png,image/webp"
                                       style="padding:9px;">
                                <p style="font-size:12px;color:#888;margin-top:4px;">
                                    Formatos: JPG, PNG, WEBP. Máx. 5 MB.
                                </p>
                                <?php echo $e('foto_perfil'); ?>
                                <?php if (!empty($datos['foto_perfil'])): ?>
                                    <p style="font-size:12px;color:#666;margin-top:4px;">
                                        Foto actual guardada. Sube una nueva para reemplazarla.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <!-- Descripción (ancho completo) -->
                    <div style="margin-top:25px;">
                        <label for="descripcion">Descripción pública (acerca de tu trabajo)</label>
                        <textarea id="descripcion" name="descripcion"><?php echo $v('descripcion'); ?></textarea>
                        <?php echo $e('descripcion'); ?>
                    </div>

                    <div style="margin-top:30px;display:flex;gap:15px;justify-content:flex-end;border-top:1px solid var(--color-borde);padding-top:20px;">
                        <a href="perfil.php" class="btn btn-claro">Cancelar</a>
                        <button type="submit" class="btn btn-principal">Guardar cambios</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
