<?php
    $tituloPagina = "Calificar Vendedor | Adicción Factory";
    include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <div style="display: flex; gap: 10px; margin-bottom: 40px; flex-wrap: wrap; justify-content: center;">
            <a href="index.php" class="btn btn-claro">Catálogo / Panel</a>
            <a href="perfil.php" class="btn btn-claro">Mi Perfil</a>
            <a href="citas.php" class="btn btn-claro">Mis Citas</a>
            <a href="comentarios.php" class="btn btn-claro">Mis Comentarios</a>
        </div>

        <div class="titulo-seccion">
            <h2>Calificar Vendedor</h2>
            <p>Tu opinión es muy importante. Evalúa el servicio y la atención brindada por el asesor inmobiliario.</p>
        </div>

        <div class="card" style="max-width: 600px; margin: 0 auto; padding: 30px;">
            
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; border-bottom: 1px solid var(--color-borde); padding-bottom: 15px;">
                <div style="width: 60px; height: 60px; background: var(--color-borde); border-radius: 50%; display: grid; place-items: center; font-size: 24px;">👤</div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; color: var(--color-oscuro);">ASESOR</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-texto);">Asesor Asignado</p>
                </div>
            </div>

            <form class="formulario" action="comentarios.php" method="POST">
                <div>
                    <label for="puntuacion">¿Cómo calificarías la atención?</label>
                    <select id="puntuacion" name="puntuacion" required>
                        <option value="">-- Selecciona una opción --</option>
                        <option value="5">⭐⭐⭐⭐⭐ (Excelente atención)</option>
                        <option value="4">⭐⭐⭐⭐ (Buena)</option>
                        <option value="3">⭐⭐⭐ (Regular)</option>
                        <option value="2">⭐⭐ (Mala)</option>
                        <option value="1">⭐ (Pésimo servicio)</option>
                    </select>
                </div>

                <div style="margin-top: 15px;">
                    <label for="comentario">Escribe tu reseña u opinión</label>
                    <textarea id="comentario" name="comentario" placeholder="Cuéntanos los detalles de tu experiencia con este asesor..." required></textarea>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="btn btn-principal btn-completo">Enviar Calificación</button>
                </div>
            </form>
        </div>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>