<?php
$tituloPagina = "Panel de Vendedor | Adicción Factory Inmobiliaria";

include("../public/includes/header.php");
?>

<main>
    <section class="seccion seccion-clara">
        <div class="contenedor">
            
            <div class="titulo-seccion">
                <p class="etiqueta">Panel de Control</p>
                <h2>Bienvenido, Vendedor</h2>
                <p>Gestiona tus inmuebles, atiende las solicitudes de tus clientes y revisa tu reputación en la plataforma.</p>
            </div>
   
           
            <div class="grid-3">
 
               
                <article class="card">
                    <div class="card-contenido">
                        <h3>Mis Inmuebles</h3>
                        <p>Administra las propiedades que tienes publicadas. Da de alta nuevos inmuebles, sube fotografías o actualiza los datos.</p>
                         
                       
                        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                            <a href="mis-inmuebles.php" class="btn btn-principal btn-completo">Ver mis inmuebles</a>
                            <a href="agregar-inmueble.php" class="btn btn-claro btn-completo">Agregar inmueble</a>
                        </div>
                    </div>
                </article>

               
                <article class="card">
                    <div class="card-contenido">
                        <h3>Citas Solicitadas</h3>
                        <p>Revisa las solicitudes de los compradores para visitar tus propiedades. Acepta, rechaza o marca como realizadas las visitas.</p>
                        
                        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                            <a href="citas.php" class="btn btn-secundario btn-completo">Gestionar citas</a>
                        </div>
                    </div>
                </article>

                
                <article class="card">
                    <div class="card-contenido">
                        <h3>Mi Perfil Público</h3>
                        <p>Actualiza tu información personal, fotografía, zona de trabajo y experiencia para generar mayor confianza a los compradores.</p>
                        
                        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                            <a href="perfil.php" class="btn btn-claro btn-completo">Ver mi perfil</a>
                            <a href="editar-perfil.php" class="btn btn-claro btn-completo">Editar perfil</a>
                        </div>
                    </div>
                </article>

                
                <article class="card">
                    <div class="card-contenido">
                        <h3>Mi Reputación</h3>
                        <p>Consulta los comentarios y las calificaciones que te han dejado los compradores después de realizar una visita a tus inmuebles.</p>
                        
                        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                            <a href="reputacion.php" class="btn btn-claro btn-completo">Ver comentarios y calificaciones</a>
                            <a href="calificar-comprador.php" class="btn btn-claro btn-completo">Calificar a un comprador</a>
                        </div>
                    </div>
                </article>

            </div>
        </div>
    </section>
</main>

<?php
include("../public/includes/footer.php");
?>