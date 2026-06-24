<?php
$tituloPagina = "Calificar Comprador | Adicción Factory Inmobiliaria";
include("../public/includes/header.php");
?>

<main>
    <section class="seccion seccion-clara">
        <div class="contenedor" style="max-width: 600px;">
            <div class="titulo-seccion">
                <h2>Evaluar Visita</h2>
            </div>

            <div class="card">
                <div class="card-contenido">
                    <form action="citas.php" method="POST" class="formulario">
                        <p style="margin-bottom: 20px;">Estás calificando a <strong>Santiago Macías</strong> por la visita a "Residencia amplia con jardín".</p>
                        
                        <div style="margin-bottom: 20px;">
                            <label>Puntuación (1 al 5)</label>
                            <select name="puntuacion" required>
                                <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                <option value="4">⭐⭐⭐⭐ Bueno</option>
                                <option value="3">⭐⭐⭐ Regular</option>
                                <option value="2">⭐⭐ Malo</option>
                                <option value="1">⭐ Pésimo</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label>Comentario sobre el comprador</label>
                            <textarea name="comentario" required placeholder="¿Llegó a tiempo? ¿Mostró interés genuino?"></textarea>
                        </div>

                        <button type="submit" class="btn btn-principal btn-completo">Enviar Calificación</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("../public/includes/footer.php"); ?>