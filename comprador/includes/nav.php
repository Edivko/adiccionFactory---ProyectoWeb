<?php
// $paginaActual debe definirse antes de incluir este archivo
$paginaActual = $paginaActual ?? '';

$enlaces = [
    'index.php'              => 'Inicio',
    'agendar.php'            => 'Agendar cita',
    'citas.php'              => 'Mis citas',
    'perfil.php'             => 'Mi perfil',
    'comentarios.php'        => 'Comentarios',
    'calificar-inmueble.php' => 'Calificar inmueble',
    'calificar-vendedor.php' => 'Calificar vendedor',
];
?>
<div style="display:flex;gap:10px;margin-bottom:40px;flex-wrap:wrap;justify-content:center;">
    <?php foreach ($enlaces as $href => $label): ?>
        <a
            href="<?php echo $href; ?>"
            class="btn <?php echo $paginaActual === $href ? 'btn-principal' : 'btn-claro'; ?>"
        >
            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    <?php endforeach; ?>
</div>
