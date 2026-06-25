<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_vendedor'])) {
    $id_vendedor = (int)$_POST['id_vendedor'];
    $accion = $_POST['accion'];

    try {
        if ($accion === 'aprobar') {
            // 1. Extraemos el id_usuario REAL directo de la tabla (infalible)
            $queryBusqueda = mysqli_query($conexion, "SELECT id_usuario FROM Vendedor WHERE id_vendedor = $id_vendedor");
            
            if (!$queryBusqueda) {
                die("🚨 ERROR MYSQL (SELECT): " . mysqli_error($conexion));
            }

            $resultado = mysqli_fetch_assoc($queryBusqueda);
            
            if ($resultado) {
                $id_usuario_real = (int)$resultado['id_usuario'];
                
                // 2. Aprobamos al Vendedor
                $updateVendedor = mysqli_query($conexion, "UPDATE Vendedor SET estatus = 'Aprobado' WHERE id_vendedor = $id_vendedor");
                if (!$updateVendedor) die("🚨 ERROR MYSQL (UPDATE Vendedor): " . mysqli_error($conexion));
                
                // 3. Activamos obligatoriamente la cuenta de Usuario
                $updateUsuario = mysqli_query($conexion, "UPDATE Usuario SET id_estado_cuenta = 2 WHERE id_usuario = $id_usuario_real");
                if (!$updateUsuario) die("🚨 ERROR MYSQL (UPDATE Usuario): " . mysqli_error($conexion));

            } else {
                die("🚨 ERROR LÓGICO: No se encontró un id_usuario para el vendedor #$id_vendedor.");
            }

        } elseif ($accion === 'rechazar') {
            // Buscamos el ID real antes de destruir todo
            $queryBusqueda = mysqli_query($conexion, "SELECT id_usuario FROM Vendedor WHERE id_vendedor = $id_vendedor");
            if ($resultado = mysqli_fetch_assoc($queryBusqueda)) {
                $id_usuario_real = (int)$resultado['id_usuario'];
                
                $delVendedor = mysqli_query($conexion, "DELETE FROM Vendedor WHERE id_vendedor = $id_vendedor");
                if (!$delVendedor) die("🚨 ERROR MYSQL (DELETE Vendedor): " . mysqli_error($conexion));
                
                $delUsuario = mysqli_query($conexion, "DELETE FROM Usuario WHERE id_usuario = $id_usuario_real");
                if (!$delUsuario) die("🚨 ERROR MYSQL (DELETE Usuario): " . mysqli_error($conexion));
            }
        }
        
        header("Location: gestionar_vendedores.php");
        exit;

    } catch (mysqli_sql_exception $e) {
        $mensajeError = "No se puede completar la acción por restricciones de la base de datos: " . $e->getMessage();
    }
}

// Consulta maestra
$queryVendedores = mysqli_query($conexion, "
    SELECT v.*, u.nombre, u.apellido, u.telefono 
    FROM Vendedor v 
    INNER JOIN Usuario u ON v.id_usuario = u.id_usuario 
    ORDER BY v.id_vendedor DESC
");
?>
<?php
$tituloPagina = "Gestión de Vendedores | Adicción Factory";
include '../public/includes/header.php';
?>
<main class="seccion seccion-clara">
    <div class="contenedor">
        <?php include 'nav_admin.php'; ?>
        
        <div class="titulo-seccion">
            <h2>Gestión de Vendedores</h2>
            <p>Verifica y administra las cuentas de las agencias inmobiliarias y vendedores independientes.</p>
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
                        <th style="padding: 15px 10px;">ID / Vendedor</th>
                        <th style="padding: 15px 10px;">Teléfono</th>
                        <th style="padding: 15px 10px;">Estatus</th>
                        <th style="padding: 15px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($queryVendedores && mysqli_num_rows($queryVendedores) > 0): 
                        while ($vendedor = mysqli_fetch_assoc($queryVendedores)): 
                            $id_vendedor = $vendedor['id_vendedor'] ?? 0;
                            
                            $nombreCompleto = trim(($vendedor['nombre'] ?? '') . ' ' . ($vendedor['apellido'] ?? ''));
                            $nombreCompleto = $nombreCompleto === '' ? 'Vendedor Desconocido' : $nombreCompleto;
                            $telefono = $vendedor['telefono'] ?? 'Sin teléfono';
                            
                            $estatus = $vendedor['estatus'] ?? 'Pendiente';
                            $colorEstatus = ($estatus === 'Aprobado') ? 'green' : '#f39c12';
                    ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 10px;">
                                <strong>#<?php echo $id_vendedor; ?></strong> - <?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td style="padding: 15px 10px;"><?php echo htmlspecialchars((string)$telefono, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="padding: 15px 10px; color: <?php echo $colorEstatus; ?>; font-weight: bold;">
                                <?php echo htmlspecialchars((string)$estatus, ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td style="padding: 15px 10px; display: flex; gap: 5px;">
                                
                                <?php if ($estatus !== 'Aprobado'): ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="id_vendedor" value="<?php echo $id_vendedor; ?>">
                                    <input type="hidden" name="accion" value="aprobar">
                                    <button type="submit" class="btn btn-secundario" style="padding: 5px 10px; font-size: 14px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 3px;">Aprobar</button>
                                </form>
                                <?php endif; ?>

                                <form method="POST" style="margin: 0;" onsubmit="return confirm('¿Seguro que deseas rechazar y eliminar totalmente a este vendedor del sistema?');">
                                    <input type="hidden" name="id_vendedor" value="<?php echo $id_vendedor; ?>">
                                    <input type="hidden" name="accion" value="rechazar">
                                    <button type="submit" class="btn btn-principal" style="padding: 5px 10px; font-size: 14px; cursor: pointer;">Rechazar</button>
                                </form>

                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="4" style="padding: 15px 10px; text-align: center;">No hay vendedores registrados en el sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </article>
    </div>
</main>
<?php include '../public/includes/footer.php'; ?>