<?php
    $tituloPagina = "Panel de Comprador | Adicción Factory";
    include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <div style="display: flex; gap: 10px; margin-bottom: 40px; flex-wrap: wrap; justify-content: center;">
            <a href="index.php" class="btn btn-principal">Catálogo / Panel</a>
            <a href="perfil.php" class="btn btn-claro">Mi Perfil</a>
            <a href="citas.php" class="btn btn-claro">Mis Citas</a>
            <a href="comentarios.php" class="btn btn-claro">Mis Comentarios</a>
        </div>

        <div class="titulo-seccion">
            <h2>Propiedades Disponibles</h2>
            <p>Bienvenido a tu panel de comprador. Explora nuestro catálogo y encuentra tu próximo hogar.</p>
        </div>

        <div class="grid-cards">
            <!-- TARJETA 1 -->
            <div class="card-inmueble">
                <img src="/public/recursos/img/casa1.jpg" alt="Fachada de Residencia">
                <div class="card-contenido">
                    <span class="badge">En Venta</span>
                    <h3>Residencia de Lujo</h3>
                    <p class="ubicacion">📍 Ixtapaluca, Estado de México</p>
                    <p class="precio">$3,500,000 MXN</p>
                    <div class="caracteristicas">
                        <span>🛏️ 4 Hab</span> <span>🛁 3 Baños</span> <span>🚗 2 Estac.</span>
                    </div>
                    <a href="agendar.php" class="btn btn-principal btn-completo">Ver Detalles y Agendar</a>
                </div>
            </div>

            <!-- TARJETA 2 -->
            <div class="card-inmueble">
                <img src="/public/recursos/img/casa2.jpg" alt="Fachada de Casa Moderna">
                <div class="card-contenido">
                    <span class="badge">En Venta</span>
                    <h3>Casa Moderna Minimalista</h3>
                    <p class="ubicacion">📍 Chalco, Estado de México</p>
                    <p class="precio">$2,100,000 MXN</p>
                    <div class="caracteristicas">
                        <span>🛏️ 3 Hab</span> <span>🛁 2 Baños</span> <span>🚗 1 Estac.</span>
                    </div>
                    <a href="agendar.php" class="btn btn-principal btn-completo">Ver Detalles y Agendar</a>
                </div>
            </div>

            <!-- TARJETA 3 -->
            <div class="card-inmueble">
                <img src="/public/recursos/img/casa3.jpeg" alt="Fachada de Hogar Familiar">
                <div class="card-contenido">
                    <span class="badge">En Venta</span>
                    <h3>Hogar Familiar Tradicional</h3>
                    <p class="ubicacion">📍 Valle de Chalco, Estado de México</p>
                    <p class="precio">$2,850,000 MXN</p>
                    <div class="caracteristicas">
                        <span>🛏️ 3 Hab</span> <span>🛁 2.5 Baños</span> <span>🚗 2 Estac.</span>
                    </div>
                    <a href="agendar.php" class="btn btn-principal btn-completo">Ver Detalles y Agendar</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../public/includes/footer.php'; ?>