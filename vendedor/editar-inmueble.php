<?php
$tituloPagina = "Editar Inmueble | Adicción Factory Inmobiliaria";
include("../public/includes/header.php");
?>

<main>
    <section class="seccion">
        <div class="contenedor">
            <div class="titulo-seccion">
                <p class="etiqueta">Actualización de datos</p>
                <h2>Editar Inmueble</h2>
            </div>

            <div class="card">
                <div class="card-contenido">
                    <form action="mis-inmuebles.php" method="POST" class="formulario">
                        <div class="grid-2">
                            <!-- Datos Generales -->
                            <div>
                                <h3 style="border-bottom: 1px solid var(--color-borde); margin-bottom: 15px;">Datos Generales</h3>
                                <div style="margin-bottom: 12px;">
                                    <label>Título de la publicación</label>
                                    <input type="text" name="titulo" value="Casa moderna en zona residencial" required>
                                </div>
                                <div class="grid-2" style="gap: 15px; margin-bottom: 12px;">
                                    <div>
                                        <label>Precio</label>
                                        <input type="number" name="precio" value="2500000" required>
                                    </div>
                                    <div>
                                        <label>Moneda</label>
                                        <select name="moneda">
                                            <option value="MXN" selected>MXN</option>
                                            <option value="USD">USD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid-2" style="gap: 15px; margin-bottom: 12px;">
                                    <div>
                                        <label>Recámaras</label>
                                        <input type="number" name="recamaras" value="3">
                                    </div>
                                    <div>
                                        <label>Baños</label>
                                        <input type="number" step="0.5" name="banos" value="2">
                                    </div>
                                </div>
                            </div>

                            
                            <div>
                                <h3 style="border-bottom: 1px solid var(--color-borde); margin-bottom: 15px;">Ubicación</h3>
                                <div style="margin-bottom: 12px;">
                                    <label>Estado</label>
                                    <input type="text" name="estado" value="Estado de México" required>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label>Ciudad / Municipio</label>
                                    <input type="text" name="ciudad" value="Metepec" required>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label>Colonia y Dirección</label>
                                    <input type="text" name="direccion" value="Zona Residencial San Carlos" required>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <label>Descripción completa</label>
                            <textarea name="descripcion" required>Hermosa casa moderna con acabados de lujo, excelente iluminación natural y amplios espacios. Cuenta con jardín trasero, área de lavado y estacionamiento techado para dos vehículos. Ideal para familias que buscan tranquilidad y seguridad.</textarea>
                        </div>

                        <div style="text-align: right; margin-top: 20px;">
                            <a href="mis-inmuebles.php" class="btn btn-claro">Cancelar</a>
                            <button type="submit" class="btn btn-principal">Actualizar Inmueble</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("../public/includes/footer.php"); ?>