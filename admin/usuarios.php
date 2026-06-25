<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

$idAdmin = (int) $_SESSION['id_usuario'];

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Cargar compradores y vendedores (excluye admins) ────────────────────────

$usuarios = [];

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.correo,
            u.telefono,
            u.id_estado_cuenta,
            ec.nombre_estado AS estado,
            r.nombre_rol     AS rol
        FROM Usuario u
        INNER JOIN EstadoCuenta ec ON ec.id_estado_cuenta = u.id_estado_cuenta
        INNER JOIN RolUsuario   r  ON r.id_rol = u.id_rol
        WHERE u.id_rol IN (1, 2)
        ORDER BY u.id_rol ASC, u.id_estado_cuenta ASC, u.id_usuario DESC
    ');
    mysqli_stmt_execute($stmt);
    $usuarios = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    $errorGeneral = 'No fue posible cargar la lista de usuarios.';
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'usuarios.php';
$tituloPagina = 'Gestión de usuarios | Adicción Factory Inmobiliaria';

include '../public/includes/header.php';

// Colores de estado
function colorEstadoAdmin(int $id): string
{
    return match ($id) {
        1       => 'color:#f39c12;',           // pendiente
        2       => 'color:#27ae60;',           // activa
        3       => 'color:#e94b27;',           // bloqueada
        4       => 'color:#95a5a6;',           // rechazada
        default => '',
    };
}
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Administración</p>
            <h2>Gestión de Usuarios</h2>
            <p>Bloquea o reactiva cuentas de compradores y vendedores.</p>
        </div>

        <?php if ($mensajeExito !== null): ?>
            <div class="mensaje-exito" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorGeneral !== null): ?>
            <div class="mensaje-error" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <article class="card" style="padding:20px;overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;text-align:left;">
                <thead>
                    <tr style="border-bottom:2px solid var(--color-borde);">
                        <th style="padding:12px 10px;">#</th>
                        <th style="padding:12px 10px;">Nombre</th>
                        <th style="padding:12px 10px;">Correo</th>
                        <th style="padding:12px 10px;">Rol</th>
                        <th style="padding:12px 10px;">Estado</th>
                        <th style="padding:12px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="6" style="padding:20px;text-align:center;color:#888;">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u):
                        $uid      = (int) $u['id_usuario'];
                        $estado   = (int) $u['id_estado_cuenta'];
                        $nombre   = htmlspecialchars($u['nombre'] . ' ' . $u['apellido'], ENT_QUOTES, 'UTF-8');
                        $correo   = htmlspecialchars($u['correo'],      ENT_QUOTES, 'UTF-8');
                        $rolLabel = htmlspecialchars(ucfirst($u['rol']), ENT_QUOTES, 'UTF-8');
                        $estLabel = htmlspecialchars(ucfirst($u['estado']), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr style="border-bottom:1px solid var(--color-borde);">
                        <td style="padding:12px 10px;color:#999;"><?php echo $uid; ?></td>
                        <td style="padding:12px 10px;font-weight:600;"><?php echo $nombre; ?></td>
                        <td style="padding:12px 10px;"><?php echo $correo; ?></td>
                        <td style="padding:12px 10px;">
                            <span class="badge"><?php echo $rolLabel; ?></span>
                        </td>
                        <td style="padding:12px 10px;font-weight:700;<?php echo colorEstadoAdmin($estado); ?>">
                            <?php echo $estLabel; ?>
                        </td>
                        <td style="padding:12px 10px;">
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">

                            <?php if ($estado === 2): /* activa → bloquear */ ?>
                                <form method="POST" action="../procesos/procesar-usuario.php">
                                    <input type="hidden" name="id_usuario" value="<?php echo $uid; ?>">
                                    <input type="hidden" name="accion"     value="bloquear">
                                    <input type="hidden" name="volver"     value="usuarios">
                                    <button type="submit"
                                            class="btn btn-claro"
                                            style="font-size:13px;color:#e94b27;border-color:#e94b27;"
                                            onclick="return confirm('¿Bloquear a este usuario?');">
                                        Bloquear
                                    </button>
                                </form>

                            <?php elseif ($estado === 3): /* bloqueada → desbloquear */ ?>
                                <form method="POST" action="../procesos/procesar-usuario.php">
                                    <input type="hidden" name="id_usuario" value="<?php echo $uid; ?>">
                                    <input type="hidden" name="accion"     value="desbloquear">
                                    <input type="hidden" name="volver"     value="usuarios">
                                    <button type="submit"
                                            class="btn btn-claro"
                                            style="font-size:13px;color:#27ae60;border-color:#27ae60;">
                                        Desbloquear
                                    </button>
                                </form>

                            <?php else: /* pendiente / rechazada / inactiva */ ?>
                                <span style="font-size:13px;color:#aaa;">—</span>
                            <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </article>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>
