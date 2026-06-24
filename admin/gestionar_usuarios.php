<?php
declare(strict_types=1);

// 1. Requerimos la conexión a la base de datos
require_once __DIR__ . '/../config/conexion.php';

$mensajeError = '';

// 2. Lógica para Eliminar, Suspender o Activar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_usuario'])) {
    $id_usuario = (int)$_POST['id_usuario'];
    $accion = $_POST['accion'];

    try {
        if ($accion === 'eliminar') {
            mysqli_query($conexion, "DELETE FROM Usuario WHERE id_usuario = $id_usuario");
        } elseif ($accion === 'suspender') {
            mysqli_query($conexion, "UPDATE Usuario SET id_estado_cuenta = 3 WHERE id_usuario = $id_usuario");
        } elseif ($accion === 'activar') {
            // Regresa el estado a 2 (Activo)
            mysqli_query($conexion, "UPDATE Usuario SET id_estado_cuenta = 2 WHERE id_usuario = $id_usuario");
        }
        
        header("Location: gestionar_usuarios.php");
        exit;

    } catch (mysqli_sql_exception $e) {
        $mensajeError = "No se puede eliminar este usuario porque tiene datos vinculados (ej. es un comprador/vendedor registrado o tiene citas). Por seguridad, te recomendamos 'Suspenderlo'.";
    }
}

// 3. Traemos a todos los usuarios
$queryUsuarios = mysqli_query($conexion, "SELECT * FROM Usuario ORDER BY id_usuario DESC");
?>
<?php
$tituloPagina = "Gestión de Usuarios | Adicción Factory";
include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <?php include 'nav_admin.php'; ?>

        <div class="titulo-seccion">
            <h2>Gestión de Usuarios (Compradores)</h2>
            <p>Bloquea, elimina o revisa la información de los compradores registrados.</p>
        </div>

        <?php if ($mensajeError !== ''): ?>
            <div style="background-color: #ffe6e6; color: #cc0000; padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 5px solid #cc0000;">
                <strong>¡Acción denegada!</strong> <?php echo $mensajeError; ?>
            </div>
        <?php endif; ?>

        <article class="card" style="padding: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #ccc;">
                        <th style="padding: 15px 10px;">ID</th>
                        <th style="padding: 15px 10px;">Nombre</th>
                        <th style="padding: 15px 10px;">Correo</th>
                        <th style="padding: 15px 10px;">Estado</th>
                        <th style="padding: 15px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($queryUsuarios) > 0): 
                        while ($usuario = mysqli_fetch_assoc($queryUsuarios)): 
                            $estadoTexto = ($usuario['id_estado_cuenta'] == 2) ? 'Activo' : 'Suspendido';
                            $colorEstado = ($usuario['id_estado_cuenta'] == 2) ? 'green' : 'orange';
                    ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 10px;"><?php echo htmlspecialchars((string)$usuario['id_usuario'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="padding: 15px 10px;"><?php echo htmlspecialchars($usuario['nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="padding: 15px 10px;"><?php echo htmlspecialchars($usuario['correo'] ?? 'Sin correo', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="padding: 15px 10px; color: <?php echo $colorEstado; ?>; font-weight: bold;">
                                <?php echo $estadoTexto; ?>
                            </td>
                            <td style="padding: 15px 10px; display: flex; gap: 5px;">
                                
                                <?php if ($usuario['id_estado_cuenta'] == 2): ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                        <input type="hidden" name="accion" value="suspender">
                                        <button type="submit" class="btn btn-claro" style="padding: 5px 10px; font-size: 14px; cursor: pointer;">Suspender</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                        <input type="hidden" name="accion" value="activar">
                                        <button type="submit" class="btn btn-secundario" style="padding: 5px 10px; font-size: 14px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 3px;">Activar</button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" style="margin: 0;" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario definitivamente?');">
                                    <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <button type="submit" class="btn btn-principal" style="padding: 5px 10px; font-size: 14px; cursor: pointer;">Eliminar</button>
                                </form>

                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="5" style="padding: 15px 10px; text-align: center;">No hay usuarios registrados en el sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </article>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>