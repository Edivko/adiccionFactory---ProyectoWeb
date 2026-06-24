<?php
$tituloPagina = "Gestión de Usuarios | Adicción Factory";
include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <?php include 'nav_admin.php'; ?>

        <div class="titulo-seccion">
            <h2>Gestión de Usuarios (Compradores)</h2>
            <p>Bloquea, elimina o revisa la información de los compradores registrados.</p>
        </div>

        <article class="card" style="padding: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #ccc;">
                        <th style="padding: 15px 10px;">ID</th>
                        <th style="padding: 15px 10px;">Nombre</th>
                        <th style="padding: 15px 10px;">Correo</th>
                        <th style="padding: 15px 10px;">Estado</th>
                        <th style="padding: 15px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px 10px;">1</td>
                        <td style="padding: 15px 10px;">usuario</td>
                        <td style="padding: 15px 10px;">usuario@email.com</td>
                        <td style="padding: 15px 10px; color: green; font-weight: bold;">Activo</td>
                        <td style="padding: 15px 10px; display: flex; gap: 5px;">
                            <button class="btn btn-claro" style="padding: 5px 10px; font-size: 14px;">Suspender</button>
                            <button class="btn btn-principal" style="padding: 5px 10px; font-size: 14px;">Eliminar</button>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px 10px;">2</td>
                        <td style="padding: 15px 10px;">usuario 2</td>
                        <td style="padding: 15px 10px;">usuario2@email.com</td>
                        <td style="padding: 15px 10px; color: green; font-weight: bold;">Activo</td>
                        <td style="padding: 15px 10px; display: flex; gap: 5px;">
                            <button class="btn btn-claro" style="padding: 5px 10px; font-size: 14px;">Suspender</button>
                            <button class="btn btn-principal" style="padding: 5px 10px; font-size: 14px;">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </article>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>