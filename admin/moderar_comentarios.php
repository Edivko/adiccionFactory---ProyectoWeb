<?php
$tituloPagina = "Moderación | Adicción Factory";
include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <?php 
        // Cambiamos el color activo del menú actualizando las clases si es necesario,
        // pero por ahora solo incluimos el general.
        include 'nav_admin.php'; 
        ?>

        <div class="titulo-seccion">
            <h2>Comentarios y Reseñas</h2>
            <p>Audita los comentarios realizados en las propiedades o perfiles.</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <article class="card" style="padding: 20px;">
                <p class="etiqueta">Reporte en: Casa Moderna Minimalista</p>
                <h3 style="margin-top: 10px;">Usuario: Diego</h3>
                <p style="font-style: italic; color: #555;">"Excelente ubicación en Chalco, pero las fotos no muestran bien el patio trasero."</p>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <button class="btn btn-secundario">Aprobar</button>
                    <button class="btn btn-principal">Eliminar Comentario</button>
                </div>
            </article>

            <article class="card" style="padding: 20px;">
                <p class="etiqueta">Reporte en: Residencia de Lujo</p>
                <h3 style="margin-top: 10px;">Usuario: Fernando</h3>
                <p style="font-style: italic; color: #555;">"El vendedor canceló la cita a última hora sin avisar. Muy mala experiencia en Ixtapaluca."</p>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <button class="btn btn-secundario">Aprobar y Mostrar</button>
                    <button class="btn btn-principal">Eliminar por Ofensivo</button>
                </div>
            </article>
        </div>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>
