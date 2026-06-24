<?php
declare(strict_types=1);

// 1. Requerimos la conexión a la base de datos
require_once __DIR__ . '/../config/conexion.php';

$mensajeError = '';

// 2. Lógica para Aprobar o Rechazar/Eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_vendedor'])) {
    $id_vendedor = (int)$_POST['id_vendedor'];
    $accion = $_POST['accion'];

    try {
        if ($accion === 'aprobar') {
            // Actualizamos el estatus (ajusta 'estatus' al nombre de la columna real que usen para verificar)
            mysqli_query($conexion, "UPDATE Vendedor SET estatus = 'Aprobado' WHERE id_vendedor = $id_vendedor");
        } elseif ($accion === 'rechazar') {
            // Elimina al vendedor (esto NO borra su cuenta de Usuario, solo le quita el rol de Vendedor)
            mysqli_query($conexion, "DELETE FROM Vendedor WHERE id_vendedor = $id_vendedor");
        }
        
        header("Location: gestionar_vendedores.php");
        exit;

    } catch (mysqli_sql_exception $e) {
        $mensajeError = "No se puede rechazar/eliminar este vendedor porque ya tiene inmuebles o información vinculada en el sistema.";
    }
}

// 3. Consulta maestra: Unimos Vendedor con Usuario para traer el Nombre y el Teléfono
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
                        <th style="padding: 15px 10px;">Estatus de Verificación</th>
                        <th style="padding: 15px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($queryVendedores && mysqli_num_rows($queryVendedores) > 0): 
                        while ($vendedor = mysqli_fetch_assoc($queryVendedores)): 
                            $id_vendedor = $vendedor['id_vendedor'] ?? 0;
                            // Juntamos nombre y apellido
                            $nombreCompleto = trim(($vendedor['nombre'] ?? '') . ' ' . ($vendedor['apellido'] ?? ''));
                            $nombreCompleto = $nombreCompleto === '' ? 'Vendedor Desconocido' : $nombreCompleto;
                            $telefono = $vendedor['telefono'] ?? 'Sin teléfono';
                            
                            // Asumimos que hay una columna 'estatus'. Si no existe, mostrará 'Pendiente' por defecto.
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

                                <form method="POST" style="margin: 0;" onsubmit="return confirm('¿Seguro que deseas rechazar y eliminar a este vendedor?');">
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