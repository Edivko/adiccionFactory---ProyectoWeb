<?php
    $tituloPagina = "Mi Perfil | Adicción Factory";
    include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <!-- Menú de navegación interno del comprador -->
        <!-- Usamos flexbox en línea para acomodarlo rápido y los botones de su CSS -->
        <div style="display: flex; gap: 10px; margin-bottom: 40px; flex-wrap: wrap; justify-content: center;">
            <a href="index.php" class="btn btn-claro">Catálogo / Panel</a>
            <a href="perfil.php" class="btn btn-principal">Mi Perfil</a>
            <a href="citas.php" class="btn btn-claro">Mis Citas</a>
            <a href="comentarios.php" class="btn btn-claro">Mis Comentarios</a>
        </div>

        <div class="titulo-seccion">
            <h2>Mi Perfil</h2>
            <p>Consulta y actualiza tu información personal y datos de contacto.</p>
        </div>

        <!-- Usamos un div con la clase "card" para que se vea como una caja blanca con sombra -->
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-contenido">
                
                <!-- Clase "formulario" que ya tiene estilos para los inputs -->
                <form class="formulario" action="#" method="POST">
                    
                    <!-- Clase "grid-2" para poner nombre y apellidos a dos columnas -->
                    <div class="grid-2">
                        <div>
                            <label for="nombre">Nombre(s)</label>
                            <!-- Nota: En un futuro aquí se imprimirá el nombre con PHP -->
                            <input type="text" id="nombre" name="nombre" value="Nombre" disabled>
                        </div>
                        <div>
                            <label for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" value="Apellido Paterno - Apellido Materno" disabled>
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" value="ejemplo@gmail.com">
                    </div>

                    <div style="margin-top: 15px;">
                        <label for="telefono">Teléfono a 10 dígitos</label>
                        <input type="tel" id="telefono" name="telefono" value="5512345678">
                    </div>

                    <div style="margin-top: 30px; text-align: center;">
                        <button type="submit" class="btn btn-principal">Guardar Cambios</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</main>

<?php
    include '../public/includes/footer.php';
?>