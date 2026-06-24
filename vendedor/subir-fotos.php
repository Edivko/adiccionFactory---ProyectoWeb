<?php
$tituloPagina = "Subir Fotos | Adicción Factory Inmobiliaria";
include("../public/includes/header.php");
?>

<main>
    <section class="seccion seccion-clara">
        <div class="contenedor">
            <div class="titulo-seccion">
                <p class="etiqueta">Multimedia</p>
                <h2>Galería del Inmueble</h2>
            </div>

            <div class="card" style="margin-bottom: 30px;">
                <div class="card-contenido text-center" style="text-align: center; padding: 40px;">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <label style="display: block; margin-bottom: 15px; font-weight: bold;">Selecciona imágenes para subir</label>
                        <input type="file" name="fotos[]" multiple accept="image/*" style="margin-bottom: 20px;">
                        <br>
                        <button type="submit" class="btn btn-principal">Subir fotografías</button>
                    </form>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="mis-inmuebles.php" class="btn btn-claro">Volver a mis inmuebles</a>
            </div>
        </div>
    </section>
</main>

<?php include("../public/includes/footer.php"); ?>