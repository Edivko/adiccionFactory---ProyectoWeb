<?php
$tituloPagina = "Gestión de Vendedores | Adicción Factory";
include '../public/includes/header.php';
?>
<main class="seccion seccion-clara">
    <div class="contenedor">
        <?php include 'nav_admin.php'; ?>
        
        <div class="titulo-seccion">
            <h2>Gestión de Vendedores</h2>
            <p>Verifica y administra las cuentas de las agencias inmobiliarias y vendedores independientes.</p>
        </div>
        
        <article class="card" style="padding: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #ccc;">
                        <th style="padding: 15px 10px;">Empresa/Vendedor</th>
                        <th style="padding: 15px 10px;">Teléfono</th>
                        <th style="padding: 15px 10px;">Estatus de Verificación</th>
                        <th style="padding: 15px 10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px 10px;">Inmobiliaria Sol</td>
                        <td style="padding: 15px 10px;">555-1234</td>
                        <td style="padding: 15px 10px; color: #f39c12; font-weight: bold;">Pendiente</td>
                        <td style="padding: 15px 10px; display: flex; gap: 5px;">
                            <button class="btn btn-secundario" style="padding: 5px 10px; font-size: 14px;">Aprobar</button>
                            <button class="btn btn-principal" style="padding: 5px 10px; font-size: 14px;">Rechazar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </article>
    </div>
</main>
<?php include '../public/includes/footer.php'; ?>