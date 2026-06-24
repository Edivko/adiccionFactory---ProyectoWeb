<?php
$tituloPagina = "Citas Solicitadas | Adicción Factory Inmobiliaria";
include("../public/includes/header.php");
?>

<main>
    <section class="seccion">
        <div class="contenedor">
            <div class="titulo-seccion">
                <p class="etiqueta">Agenda</p>
                <h2>Citas Solicitadas</h2>
                <p>Gestiona las solicitudes de los compradores para visitar tus propiedades.</p>
            </div>

            <div class="grid-2">
                <article class="card">
                    <div class="card-contenido">
                        <span class="badge" style="background: #fff3e0; color: #e65100;">Pendiente</span>
                        <h3 style="margin-top: 10px;">Casa moderna en zona residencial</h3>
                        <p><strong>Comprador:</strong> Eduardo Ivan Hernández</p>
                        <p><strong>Fecha solicitada:</strong> 30 de Junio, 2026</p>
                        <p><strong>Hora:</strong> 14:00 hrs</p>
                        
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <button class="btn btn-principal" style="flex: 1;">✔️ Aceptar</button>
                            <button class="btn btn-claro" style="flex: 1;">❌ Rechazar</button>
                        </div>
                    </div>
                </article>

                <article class="card">
                    <div class="card-contenido">
                        <span class="badge" style="background: #e8f5e9; color: #2e7d32;">Aceptada</span>
                        <h3 style="margin-top: 10px;">Residencia amplia con jardín</h3>
                        <p><strong>Comprador:</strong> Santiago Macías</p>
                        <p><strong>Fecha acordada:</strong> 25 de Junio, 2026</p>
                        <p><strong>Hora:</strong> 10:30 hrs</p>
                        
                        <div style="margin-top: 20px;">
                            <a href="calificar-comprador.php" class="btn btn-secundario btn-completo">Marcar como realizada y Calificar</a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>

<?php include("../public/includes/footer.php"); ?>