<?php
    $tituloPagina = "Mis Comentarios | Adicción Factory";
    include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <!-- Menú de navegación interno -->
        <div style="display: flex; gap: 10px; margin-bottom: 40px; flex-wrap: wrap; justify-content: center;">
            <a href="index.php" class="btn btn-claro">Catálogo / Panel</a>
            <a href="perfil.php" class="btn btn-claro">Mi Perfil</a>
            <a href="citas.php" class="btn btn-claro">Mis Citas</a>
            <a href="comentarios.php" class="btn btn-principal">Mis Comentarios</a>
        </div>

        <div class="titulo-seccion">
            <h2>Mis Comentarios y Calificaciones</h2>
            <p>Historial de las reseñas que has dejado a los vendedores y las propiedades.</p>
        </div>

        <!-- Cascarón para el historial de comentarios -->
        <div class="card" style="padding: 40px; text-align: center;">
            <h3 style="color: #666; font-weight: normal;">No hay comentarios registrados.</h3>
            <p style="margin-top: 15px;">Después de asistir a una cita podrás calificar al vendedor y el inmueble.</p>
        </div>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>