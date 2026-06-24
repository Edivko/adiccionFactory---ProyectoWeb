<?php
    $tituloPagina = "Mis Citas | Adicción Factory";
    include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <div style="display: flex; gap: 10px; margin-bottom: 40px; flex-wrap: wrap; justify-content: center;">
            <a href="index.php" class="btn btn-claro">Catálogo / Panel</a>
            <a href="perfil.php" class="btn btn-claro">Mi Perfil</a>
            <a href="citas.php" class="btn btn-principal">Mis Citas</a>
            <a href="comentarios.php" class="btn btn-claro">Mis Comentarios</a>
        </div>

        <div class="titulo-seccion">
            <h2>Mis Citas Programadas</h2>
            <p>Aquí podrás ver el estatus de las visitas que tienes agendadas para conocer los inmuebles.</p>
        </div>

        <!-- NOTA PARA EL EQUIPO: Aquí iniciará el ciclo PHP de la Base de Datos -->
        
        <!-- MAQUETA DE TARJETA DE CITA -->
        <div class="card" style="display: flex; gap: 25px; align-items: center; padding: 20px; flex-wrap: wrap; margin-bottom: 20px;">
            <img src="/public/recursos/img/casa1.jpg" alt="Fachada" style="width: 200px; height: 150px; object-fit: cover; border-radius: 10px;">
            
            <div style="flex: 1;">
                <span class="badge" style="background-color: #ffde59; color: #211d1d;">Pendiente de confirmación</span>
                <h3 style="margin-top: 10px; color: #211d1d;">Residencia de Lujo</h3>
                
                <div style="margin-top: 10px; color: #4a4a4a;">
                    <p><strong>📅 Fecha de visita:</strong> 2026-06-25</p>
                    <p><strong>⏰ Hora:</strong> 18:00</p>
                    <p style="margin-top: 5px;"><strong>📝 Mensaje enviado:</strong> <i>"Maqueta de diseño."</i></p>
                </div>
                
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <a href="#" class="btn btn-secundario" style="padding: 8px 16px; font-size: 14px;">Reprogramar</a>
                    <a href="#" class="btn btn-claro" style="padding: 8px 16px; font-size: 14px; color: #e94b27; border-color: #e94b27;">Cancelar Cita</a>
                </div>
            </div>
        </div>

        <!-- ESTADO VACÍO (Ocultar con PHP cuando haya citas) -->
        <!--
        <div class="card" style="padding: 40px; text-align: center;">
            <h3 style="color: #666; font-weight: normal;">Aún no tienes citas agendadas.</h3>
            <p style="margin-top: 15px;">Explora el catálogo y agenda una cita para conocer tu próximo hogar.</p>
            <a href="index.php" class="btn btn-secundario" style="margin-top: 20px;">Ir al Catálogo</a>
        </div>
        -->

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>