<?php
$paginaActual = $paginaActual ?? '';

$enlaces = [
    'index.php'              => 'Inicio',
    'mis-inmuebles.php'      => 'Mis inmuebles',
    'agregar-inmueble.php'   => 'Agregar inmueble',
    'citas.php'              => 'Citas',
    'perfil.php'             => 'Mi perfil',
    'reputacion.php'         => 'Reputación',
    'calificar-comprador.php' => 'Calificar comprador',
];
?>
<div style="display:flex;gap:10px;margin-bottom:40px;flex-wrap:wrap;justify-content:center;">
    <?php foreach ($enlaces as $href => $label): ?>
        <a href="<?php echo $href; ?>"
           class="btn <?php echo $paginaActual === $href ? 'btn-principal' : 'btn-claro'; ?>">
            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    <?php endforeach; ?>
</div>
