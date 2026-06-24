<?php
$tituloPagina = "Editar Perfil | Adicción Factory Inmobiliaria";
include("../public/includes/header.php");
?>

<main>
    <section class="seccion seccion-clara">
        <div class="contenedor">
            
            <div class="titulo-seccion">
                <p class="etiqueta">Configuración</p>
                <h2>Editar Información</h2>
                <p>Mantén tus datos actualizados para que los compradores puedan contactarte fácilmente.</p>
            </div>

            <!-- Contenedor principal estilo tarjeta -->
            <div class="card">
                <div class="card-contenido">
                    
                    <!-- Formulario. El atributo enctype es vital para poder subir imágenes -->
                    <form action="perfil.php" method="POST" enctype="multipart/form-data" class="formulario">
                        
                        <!-- Dividimos el formulario en 2 columnas para que no se vea tan largo -->
                        <div class="grid-2">
                            
                            <!-- Columna 1: Tabla Usuario -->
                            <div>
                                <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--color-borde); padding-bottom: 5px; font-size: 18px;">Datos de Acceso y Contacto</h3>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="nombre">Nombre(s)</label>
                                    <input type="text" id="nombre" name="nombre" value="Leonardo" required>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="apellido">Apellidos</label>
                                    <input type="text" id="apellido" name="apellido" value="Becerra Lugo" required>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="correo">Correo Electrónico</label>
                                    <input type="email" id="correo" name="correo" value="lbecerral@alumno.ipn.mx" required>
                                </div>
                                
                                <div>
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" id="telefono" name="telefono" value="55 1234 5678">
                                </div>
                            </div>

                            <!-- Columna 2: Tabla Vendedor -->
                            <div>
                                <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--color-borde); padding-bottom: 5px; font-size: 18px;">Perfil Público</h3>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="zona_trabajo">Zona de Trabajo principal</label>
                                    <input type="text" id="zona_trabajo" name="zona_trabajo" value="Coacalco, Estado de México y Zona Metropolitana." required>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="experiencia">Años de Experiencia</label>
                                    <input type="number" id="experiencia" name="experiencia" value="3" min="0" required>
                                </div>
                                
                                <div>
                                    <label for="foto_perfil">Actualizar Fotografía</label>
                                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="padding: 9px;">
                                    <p style="font-size: 12px; color: #888; margin-top: 4px;">Formatos permitidos: JPG, PNG. Tamaño máx: 2MB.</p>
                                </div>
                            </div>

                        </div>

                        <!-- Fila completa para la descripción -->
                        <div style="margin-top: 25px;">
                            <label for="descripcion">Descripción Pública (Acerca de tu trabajo)</label>
                            <textarea id="descripcion" name="descripcion" required>Soy un asesor inmobiliario comprometido con encontrar el hogar ideal para mis clientes. Cuento con amplia disponibilidad para agendar visitas presenciales y resolver cualquier duda legal o técnica durante el proceso de compra.</textarea>
                        </div>

                        <!-- Botones de acción alineados a la derecha -->
                        <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid var(--color-borde); padding-top: 20px;">
                            <a href="perfil.php" class="btn btn-claro">Cancelar</a>
                            <button type="submit" class="btn btn-principal">Guardar Cambios</button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </section>
</main>

<?php
include("../public/includes/footer.php");
?>