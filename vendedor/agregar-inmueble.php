<?php
$tituloPagina = "Agregar Inmueble | Adicción Factory Inmobiliaria";
include("../public/includes/header.php");
?>

<main>
    <section class="seccion">
        <div class="contenedor">
            <div class="titulo-seccion">
                <p class="etiqueta">Nuevo Registro</p>
                <h2>Agregar Inmueble</h2>
            </div>

            <div class="card">
                <div class="card-contenido">
                    <form action="mis-inmuebles.php" method="POST" class="formulario">
                        <div class="grid-2">
                            <div>
                                <h3 style="border-bottom: 1px solid var(--color-borde); margin-bottom: 15px;">Datos Generales</h3>
                                <div style="margin-bottom: 12px;">
                                    <label>Título de la publicación</label>
                                    <input type="text" name="titulo" required>
                                </div>
                                <div class="grid-2" style="gap: 15px; margin-bottom: 12px;">
                                    <div>
                                        <label>Precio</label>
                                        <input type="number" name="precio" required>
                                    </div>
                                    <div>
                                        <label>Moneda</label>
                                        <select name="moneda">
                                            <option value="MXN">MXN</option>
                                            <option value="USD">USD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid-2" style="gap: 15px; margin-bottom: 12px;">
                                    <div>
                                        <label>Recámaras</label>
                                        <input type="number" name="recamaras">
                                    </div>
                                    <div>
                                        <label>Baños</label>
                                        <input type="number" step="0.5" name="banos">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 style="border-bottom: 1px solid var(--color-borde); margin-bottom: 15px;">Ubicación</h3>
                                <div style="margin-bottom: 12px;">
                                    <label>Estado</label>
                                    <input type="text" name="estado" required>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label>Ciudad / Municipio</label>
                                    <input type="text" name="ciudad" required>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label>Colonia y Dirección</label>
                                    <input type="text" name="direccion" required>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <label>Descripción completa</label>
                            <textarea name="descripcion" required></textarea>
                        </div>

                        <div style="text-align: right; margin-top: 20px;">
                            <a href="mis-inmuebles.php" class="btn btn-claro">Cancelar</a>
                            <button type="submit" class="btn btn-principal">Guardar Inmueble</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("../public/includes/footer.php"); ?>