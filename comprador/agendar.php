<?php
    $tituloPagina = "Agendar Cita | Adicción Factory";
    include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <div style="display: flex; gap: 10px; margin-bottom: 40px; flex-wrap: wrap; justify-content: center;">
            <a href="index.php" class="btn btn-claro">Volver al Catálogo</a>
            <a href="perfil.php" class="btn btn-claro">Mi Perfil</a>
            <a href="citas.php" class="btn btn-claro">Mis Citas</a>
        </div>

        <div class="titulo-seccion">
            <h2>Agendar Cita</h2>
            <p>Selecciona la fecha y hora para visitar esta propiedad. Un vendedor te confirmará pronto.</p>
        </div>

        <div class="grid-2">
            
            <!-- MAQUETA VISUAL DE LA CASA (Se llenará con BD después) -->
            <div class="card-inmueble">
                <img src="/public/recursos/img/casa1.jpg" alt="Fachada">
                <div class="card-contenido">
                    <h3>Residencia de Lujo</h3>
                    <p class="ubicacion">📍 Ixtapaluca, Estado de México</p>
                    <p class="precio">$3,500,000 MXN</p>
                </div>
            </div>

            <!-- FORMULARIO LIMPIO -->
            <div class="card" style="padding: 30px;">
                <h3 style="margin-bottom: 20px;">Detalles de la visita</h3>
                
                <form class="formulario" action="citas.php" method="POST">
                    <div>
                        <label for="fecha">Fecha sugerida</label>
                        <input type="date" id="fecha" name="fecha" required>
                    </div>

                    <div style="margin-top: 15px;">
                        <label for="hora">Hora sugerida</label>
                        <input type="time" id="hora" name="hora" required>
                    </div>

                    <div style="margin-top: 15px;">
                        <label for="mensaje">Mensaje para el vendedor (Opcional)</label>
                        <textarea id="mensaje" name="mensaje" placeholder="Ej. Me interesa ver los acabados de la cocina..."></textarea>
                    </div>

                    <div style="margin-top: 25px;">
                        <button type="submit" class="btn btn-principal btn-completo">Confirmar Cita</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>

<?php include '../public/includes/footer.php'; ?>