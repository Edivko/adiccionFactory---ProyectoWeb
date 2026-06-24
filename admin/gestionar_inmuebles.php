<?php
declare(strict_types=1);

// 1. Requerimos la conexión
require_once __DIR__ . '/../config/conexion.php';

$mensajeError = '';

// 2. Lógica para Dar de Baja (Eliminar) protegida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_inmueble'])) {
    $id_inmueble = (int)$_POST['id_inmueble'];
    $accion = $_POST['accion'];

    try {
        if ($accion === 'eliminar') {
            mysqli_query($conexion, "DELETE FROM Inmueble WHERE id_inmueble = $id_inmueble");
        }
        
        header("Location: gestionar_inmuebles.php");
        exit;

    } catch (mysqli_sql_exception $e) {
        $mensajeError = "No se puede dar de baja este inmueble porque tiene citas agendadas, fotos o comentarios vinculados en otras tablas.";
    }
}

// 3. Traemos todos los inmuebles
$queryInmuebles = mysqli_query($conexion, "SELECT * FROM Inmueble ORDER BY id_inmueble DESC");
?>
<?php
$tituloPagina = "Gestión de Inmuebles | Adicción Factory";
include '../public/includes/header.php';
?>
<main class="seccion seccion-clara">
    <div class="contenedor">
        <?php include 'nav_admin.php'; ?>
        
        <div class="titulo-seccion">
            <h2>Catálogo Total de Inmuebles</h2>
            <p>Audita las propiedades publicadas, revisa precios y oculta anuncios que incumplan las normas.</p>
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
                        <th style="padding: 15px 10px;">Título</th>
                        <th style="padding: 15px 10px;">Vendedor</th>
                        <th style="padding: 15px 10px;">Precio</th>
                        <th style="padding: 15px 10px;">Estatus</th>
                        <th style="padding: 15px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($queryInmuebles) > 0): 
                        while ($inmueble = mysqli_fetch_assoc($queryInmuebles)): 
                            // Asignación con los nombres EXACTOS de tu phpMyAdmin
                            $id = $inmueble['id_inmueble'] ?? 0;
                            $titulo = $inmueble['titulo'] ?? 'Sin título';
                            $precio = $inmueble['precio'] ?? 0;
                            $moneda = $inmueble['moneda'] ?? 'MXN';
                            $vendedor = $inmueble['id_usuario_publicador'] ?? 'Desconocido';
                            $estadoPublicacion = $inmueble['id_estado_publicacion'] ?? 'N/A';
                            
                            // Formato de dinero inteligente
                            $precioFormat = is_numeric($precio) ? '$' . number_format((float)$precio, 2) . ' ' . $moneda : $precio;
                    ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 10px;"><?php echo htmlspecialchars((string)$titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                            
                            <td style="padding: 15px 10px;">Usuario ID: <?php echo htmlspecialchars((string)$vendedor, ENT_QUOTES, 'UTF-8'); ?></td>
                            
                            <td style="padding: 15px 10px;"><?php echo htmlspecialchars((string)$precioFormat, ENT_QUOTES, 'UTF-8'); ?></td>
                            
                            <td style="padding: 15px 10px; color: green; font-weight: bold;">Est. Pub: <?php echo htmlspecialchars((string)$estadoPublicacion, ENT_QUOTES, 'UTF-8'); ?></td>
                            
                            <td style="padding: 15px 10px; display: flex; gap: 5px;">
                                
                                <button type="button" class="btn btn-claro" style="padding: 5px 10px; font-size: 14px; cursor: pointer;">Auditar</button>

                                <form method="POST" style="margin: 0;" onsubmit="return confirm('¿Estás seguro de dar de baja este inmueble definitivamente?');">
                                    <input type="hidden" name="id_inmueble" value="<?php echo $id; ?>">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <button type="submit" class="btn btn-principal" style="padding: 5px 10px; font-size: 14px; cursor: pointer;">Dar de Baja</button>
                                </form>

                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="5" style="padding: 15px 10px; text-align: center;">No hay inmuebles publicados en el sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </article>
    </div>
</main>
<?php include '../public/includes/footer.php'; ?>