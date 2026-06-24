<?php
$tituloPagina = "Categorías | Adicción Factory";
include '../public/includes/header.php';
?>
<main class="seccion seccion-clara">
    <div class="contenedor">
        <?php include 'nav_admin.php'; ?>
        
        <div class="titulo-seccion">
            <h2>Gestión de Categorías</h2>
            <p>Agrega o elimina los tipos de inmuebles disponibles en el catálogo.</p>
        </div>
        
        <!-- Usamos un grid para separar el formulario de agregar y la lista -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            
            <article class="card" style="padding: 20px;">
                <h3>Agregar Nueva</h3>
                <form style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
                    <input type="text" placeholder="Ej. Departamento, Local, Terreno" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 100%;">
                    <button type="button" class="btn btn-secundario btn-completo">Guardar Categoría</button>
                </form>
            </article>

            <article class="card" style="padding: 20px;">
                <h3>Categorías Existentes</h3>
                <ul style="list-style: none; padding: 0; margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f4f4f4; border-radius: 5px;">
                        <span>Casas</span>
                        <button class="btn btn-principal" style="padding: 5px 10px; font-size: 12px;">Eliminar</button>
                    </li>
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f4f4f4; border-radius: 5px;">
                        <span>Terrenos</span>
                        <button class="btn btn-principal" style="padding: 5px 10px; font-size: 12px;">Eliminar</button>
                    </li>
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f4f4f4; border-radius: 5px;">
                        <span>Locales Comerciales</span>
                        <button class="btn btn-principal" style="padding: 5px 10px; font-size: 12px;">Eliminar</button>
                    </li>
                </ul>
            </article>

        </div>
    </div>
</main>
<?php include '../public/includes/footer.php'; ?>