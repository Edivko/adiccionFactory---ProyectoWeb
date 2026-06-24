<?php
$tituloPagina = "Mis Inmuebles | Adicción Factory Inmobiliaria";
include("../public/includes/header.php");
?>

<main>
    <section class="seccion seccion-clara">
        <div class="contenedor">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
                <div>
                    <p class="etiqueta">Gestión</p>
                    <h2 style="font-size: 34px; color: var(--color-oscuro);">Mis Inmuebles</h2>
                </div>
                <a href="agregar-inmueble.php" class="btn btn-principal">➕ Agregar Inmueble</a>
            </div>

            <div class="grid-3">
                <article class="card card-inmueble">
                    <img src="/adiccionFactory/public/recursos/img/casa1.jpg" alt="Casa">
                    <div class="card-contenido">
                        <span class="badge">Activo</span>
                        <h3>Casa moderna en zona residencial</h3>
                        <p class="precio">$2,500,000 MXN</p>
                        <p class="ubicacion">Metepec, Estado de México</p>
                        
                        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                            <a href="editar-inmueble.php?id=1" class="btn btn-claro btn-completo">✏️ Editar datos</a>
                            <a href="subir-fotos.php?id=1" class="btn btn-claro btn-completo">📷 Subir/Gestionar fotos</a>
                            <button class="btn btn-secundario btn-completo">⏸️ Pausar publicación</button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>

<?php include("../public/includes/footer.php"); ?>