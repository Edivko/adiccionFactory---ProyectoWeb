<?php
$tituloPagina = "Mi Perfil Público | Adicción Factory Inmobiliaria";
include("../public/includes/header.php");
?>

<main>
    <section class="seccion seccion-clara">
        <div class="contenedor">
            
            <div class="titulo-seccion">
                <p class="etiqueta">Perfil de Usuario</p>
                <h2>Mi Perfil Público</h2>
                <p>Así es como los visitantes y compradores ven tu información cuando consultan tus propiedades o agendan una cita.</p>
            </div>

           
            <div class="grid-2">

               
                <article class="card" style="text-align: center; padding: 40px 20px;">
                    
                    <img src="/adiccionFactory/public/recursos/img/vendedor1.jpg" alt="Foto de perfil" style="width: 160px; height: 160px; border-radius: 50%; object-fit: cover; margin: 0 auto 20px; border: 4px solid var(--color-claro); box-shadow: var(--sombra);">
                    
                    <h3>Leonardo Becerra Lugo</h3>
                    <p class="etiqueta" style="margin-bottom: 5px;">Vendedor Inmobiliario</p>
                    
                    
                    <p style="color: var(--color-oscuro); font-weight: bold; margin-bottom: 20px;">
                        ⭐⭐⭐⭐⭐ <span style="font-weight: normal; color: var(--color-texto);">(4.8 / 5)</span>
                    </p>
                    
                    <a href="editar-perfil.php" class="btn btn-secundario btn-completo">Editar Información</a>
                </article>

                
                <article class="card">
                    <div class="card-contenido">
                        <h3 style="border-bottom: 1px solid var(--color-borde); padding-bottom: 10px; margin-bottom: 15px;">Información de Contacto</h3>
                        <p style="margin-bottom: 10px;"><strong>Correo Electrónico:</strong> lbecerral@alumno.ipn.mx</p>
                        <p style="margin-bottom: 25px;"><strong>Teléfono:</strong> 55 1234 5678</p>
                        
                        <h3 style="border-bottom: 1px solid var(--color-borde); padding-bottom: 10px; margin-bottom: 15px;">Acerca de mi trabajo</h3>
                        <p style="margin-bottom: 15px;">
                            Soy un asesor inmobiliario comprometido con encontrar el hogar ideal para mis clientes. 
                            Cuento con amplia disponibilidad para agendar visitas presenciales y resolver cualquier 
                            duda legal o técnica durante el proceso de compra.
                        </p>
                        
                        <div style="background: var(--color-claro); padding: 15px; border-radius: 8px;">
                            <p style="margin-bottom: 5px;"><strong>📍 Zona de Trabajo:</strong> Coacalco, Estado de México y Zona Metropolitana.</p>
                            <p><strong>💼 Experiencia:</strong> 3 años en el sector inmobiliario.</p>
                        </div>
                    </div>
                </article>

            </div>
            
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="index.php" class="btn btn-claro">⬅ Volver al Panel de Control</a>
            </div>

        </div>
    </section>
</main>

<?php
include("../public/includes/footer.php");
?>