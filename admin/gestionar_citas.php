<?php
$tituloPagina = "Gestión de Citas | Adicción Factory";
include '../public/includes/header.php';
?>
<main class="seccion seccion-clara">
    <div class="contenedor">
        <?php include 'nav_admin.php'; ?>
        
        <div class="titulo-seccion">
            <h2>Supervisión de Citas</h2>
            <p>Monitorea las visitas agendadas entre compradores y vendedores.</p>
        </div>
        
        <article class="card" style="padding: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #ccc;">
                        <th style="padding: 15px 10px;">Fecha y Hora</th>
                        <th style="padding: 15px 10px;">Comprador</th>
                        <th style="padding: 15px 10px;">Vendedor</th>
                        <th style="padding: 15px 10px;">Inmueble</th>
                        <th style="padding: 15px 10px;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px 10px;">25/06/2026 10:00 AM</td>
                        <td style="padding: 15px 10px;">Diego</td>
                        <td style="padding: 15px 10px;">Inmobiliaria Sol</td>
                        <td style="padding: 15px 10px;">Residencia de Lujo</td>
                        <td style="padding: 15px 10px; color: #3498db; font-weight: bold;">Agendada</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px 10px;">26/06/2026 12:30 PM</td>
                        <td style="padding: 15px 10px;">Fernando</td>
                        <td style="padding: 15px 10px;">Inmobiliaria Sol</td>
                        <td style="padding: 15px 10px;">Casa Moderna Minimalista</td>
                        <td style="padding: 15px 10px; color: green; font-weight: bold;">Realizada</td>
                    </tr>
                </tbody>
            </table>
        </article>
    </div>
</main>
<?php include '../public/includes/footer.php'; ?>
