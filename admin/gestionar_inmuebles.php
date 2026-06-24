<?php
$tituloPagina = "Gestión de Inmuebles | Adicción Factory";
include '../public/includes/header.php';
?>
<main class="seccion seccion-clara">
    <div class="contenedor">
        <?php include 'nav_admin.php'; ?>
        
        <div class="titulo-seccion">
            <h2>Catálogo Total de Inmuebles</h2>
            <p>Audita las propiedades publicadas, revisa precios y oculta anuncios que incumplan las normas.</p>
        </div>
        
        <article class="card" style="padding: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #ccc;">
                        <th style="padding: 15px 10px;">Título</th>
                        <th style="padding: 15px 10px;">Vendedor</th>
                        <th style="padding: 15px 10px;">Precio</th>
                        <th style="padding: 15px 10px;">Estatus</th>
                        <th style="padding: 15px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px 10px;">Casa Moderna Minimalista</td>
                        <td style="padding: 15px 10px;">Inmobiliaria Sol</td>
                        <td style="padding: 15px 10px;">$2,100,000 MXN</td>
                        <td style="padding: 15px 10px; color: green; font-weight: bold;">Publicado</td>
                        <td style="padding: 15px 10px; display: flex; gap: 5px;">
                            <button class="btn btn-claro" style="padding: 5px 10px; font-size: 14px;">Auditar</button>
                            <button class="btn btn-principal" style="padding: 5px 10px; font-size: 14px;">Dar de Baja</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </article>
    </div>
</main>
<?php include '../public/includes/footer.php'; ?>