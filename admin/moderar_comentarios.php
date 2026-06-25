<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

$mensajeError = '';

// Lógica para Aprobar o Eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_comentario'])) {
    $id_comentario = (int)$_POST['id_comentario'];
    $accion = $_POST['accion'];

    try {
        if ($accion === 'aprobar') {
            // Marcamos como aprobado/visible
            mysqli_query($conexion, "UPDATE Comentario SET estatus = 'Aprobado' WHERE id_comentario = $id_comentario");
        } elseif ($accion === 'eliminar') {
            // Eliminamos el comentario ofensivo o irrelevante
            mysqli_query($conexion, "DELETE FROM Comentario WHERE id_comentario = $id_comentario");
        }
        
        header("Location: moderar_comentarios.php");
        exit;

    } catch (mysqli_sql_exception $e) {
        $mensajeError = "No se pudo realizar la acción: " . $e->getMessage();
    }
}

// Consulta maestra: Unimos Comentario con Usuario e Inmueble para saber quién escribió y dónde
$queryComentarios = mysqli_query($conexion, "
    SELECT c.*, u.nombre, u.apellido, i.titulo AS nombre_inmueble 
    FROM Comentario c 
    INNER JOIN Usuario u ON c.id_usuario = u.id_usuario 
    INNER JOIN Inmueble i ON c.id_inmueble = i.id_inmueble
    ORDER BY c.id_comentario DESC
");
?>
<?php
$tituloPagina = "Moderación | Adicción Factory";
include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        <?php include 'nav_admin.php'; ?>

        <div class="titulo-seccion">
            <h2>Comentarios y Reseñas</h2>
            <p>Audita los comentarios realizados en las propiedades o perfiles.</p>
        </div>

        <?php if ($mensajeError !== ''): ?>
            <div style="background-color: #ffe6e6; color: #cc0000; padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 5px solid #cc0000;">
                <strong>Error:</strong> <?php echo $mensajeError; ?>
            </div>
        <?php endif; ?>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php 
            if ($queryComentarios && mysqli_num_rows($queryComentarios) > 0): 
                while ($c = mysqli_fetch_assoc($queryComentarios)): 
                    $estatus = $c['estatus'] ?? 'Pendiente';
            ?>
            <article class="card" style="padding: 20px;">
                <p class="etiqueta">Reporte en: <strong><?php echo htmlspecialchars($c['nombre_inmueble'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                <h3 style="margin-top: 10px;">Usuario: <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p style="font-style: italic; color: #555;">"<?php echo htmlspecialchars($c['contenido'], ENT_QUOTES, 'UTF-8'); ?>"</p>
                
                <p style="margin-top: 10px; font-weight: bold; color: <?php echo ($estatus === 'Aprobado') ? 'green' : '#f39c12'; ?>;">
                    Estatus: <?php echo $estatus; ?>
                </p>

                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <?php if ($estatus !== 'Aprobado'): ?>
                    <form method="POST">
                        <input type="hidden" name="id_comentario" value="<?php echo $c['id_comentario']; ?>">
                        <input type="hidden" name="accion" value="aprobar">
                        <button type="submit" class="btn btn-secundario">Aprobar</button>
                    </form>
                    <?php endif; ?>

                    <form method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este comentario?');">
                        <input type="hidden" name="id_comentario" value="<?php echo $c['id_comentario']; ?>">
                        <input type="hidden" name="accion" value="eliminar">
                        <button type="submit" class="btn btn-principal">Eliminar Comentario</button>
                    </form>
                </div>
            </article>
            <?php 
                endwhile; 
            else: 
            ?>
                <p style="text-align: center;">No hay comentarios pendientes por moderar.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../public/includes/footer.php'; ?>