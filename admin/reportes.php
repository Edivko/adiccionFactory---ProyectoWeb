<?php
$tituloPagina = "Reportes | Adicción Factory";
include '../public/includes/header.php';
?>
<main class="seccion seccion-clara">
    <div class="contenedor">
        <?php include 'nav_admin.php'; ?>
        
        <div class="titulo-seccion">
            <h2>Reportes del Sistema</h2>
            <p>Genera estadísticas sobre las ventas, citas y usuarios activos en un periodo determinado.</p>
        </div>
        
        <article class="card" style="padding: 20px;">
            <form style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px;">
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label for="fecha_inicio" style="font-weight: bold;">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label for="fecha_fin" style="font-weight: bold;">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <button type="button" class="btn btn-principal" style="padding: 10px 20px;">Generar Reporte</button>
            </form>
            
            <div style="background-color: #f9f9f9; padding: 40px; text-align: center; border: 2px dashed #ccc; border-radius: 5px;">
                <p style="color: #666; font-size: 16px;">Seleccione un rango de fechas y presione "Generar" para visualizar la gráfica de actividad.</p>
            </div>
        </article>
    </div>
</main>
<?php include '../public/includes/footer.php'; ?>
