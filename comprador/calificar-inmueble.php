<?php
    $tituloPagina = "Calificar Inmueble | Adicción Factory";
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
            <h2>Calificar Propiedad</h2>
            <p>Ayuda a otros usuarios compartiendo tu reseña sobre las condiciones físicas y la ubicación de este inmueble.</p>
        </div>

        <div class="card" style="max-width: 600px; margin: 0 auto; padding: 30px;">
            
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; border-bottom: 1px solid var(--color-borde); padding-bottom: 15px;">
                <img src="/public/recursos/img/casa1.jpg" alt="Inmueble" style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; color: var(--color-oscuro);">Residencia de Lujo</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-texto);">📍 Ixtapaluca, Estado de México</p>
                </div>
            </div>

            <form class="formulario" action="comentarios.php" method="POST">
                <div>
                    <label for="puntuacion">¿Qué calificación le das a la propiedad?</label>
                    <select id="puntuacion" name="puntuacion" required>
                        <option value="">-- Selecciona una opción --</option>
                        <option value="5">⭐⭐⭐⭐⭐ (Excelente estado y ubicación)</option>
                        <option value="4">⭐⭐⭐⭐ (Buen estado general)</option>
                        <option value="3">⭐⭐⭐ (Detalles aceptables / regular)</option>
                        <option value="2">⭐⭐ (Requiere mantenimiento urgente)</option>
                        <option value="1">⭐ (Malas condiciones)</option>
                    </select>
                </div>

                <div style="margin-top: 15px;">
                    <label for="comentario">Escribe tus comentarios sobre el inmueble</label>
                    <textarea id="comentario" name="comentario" placeholder="Ej. La zona es muy tranquila y segura, pero hace falta impermeabilizar la azotea..." required></textarea>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="btn btn-principal btn-completo">Enviar Comentario</button>
                </div>
            </form>
        </div>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>