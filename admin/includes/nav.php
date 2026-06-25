<?php
// $paginaActual debe estar definida antes de incluir este archivo
$paginaActual ??= '';
?>
<nav style="display:flex;gap:10px;margin-bottom:40px;flex-wrap:wrap;">
    <a href="index.php"
       class="btn <?php echo $paginaActual === 'index.php'     ? 'btn-principal' : 'btn-claro'; ?>">
        Panel
    </a>
    <a href="usuarios.php"
       class="btn <?php echo $paginaActual === 'usuarios.php'  ? 'btn-principal' : 'btn-claro'; ?>">
        Usuarios
    </a>
    <a href="vendedores.php"
       class="btn <?php echo $paginaActual === 'vendedores.php' ? 'btn-principal' : 'btn-claro'; ?>">
        Vendedores
    </a>
    <a href="inmuebles.php"
       class="btn <?php echo $paginaActual === 'inmuebles.php' ? 'btn-principal' : 'btn-claro'; ?>">
        Inmuebles
    </a>
    <a href="categorias.php"
       class="btn <?php echo $paginaActual === 'categorias.php' ? 'btn-principal' : 'btn-claro'; ?>">
        Catálogos
    </a>
    <a href="comentarios.php"
       class="btn <?php echo $paginaActual === 'comentarios.php' ? 'btn-principal' : 'btn-claro'; ?>">
        Comentarios
    </a>
    <a href="citas.php"
       class="btn <?php echo $paginaActual === 'citas.php' ? 'btn-principal' : 'btn-claro'; ?>">
        Citas
    </a>
    <a href="../procesos/cerrar-sesion.php" class="btn btn-claro"
       style="margin-left:auto;color:#e94b27;border-color:#e94b27;">
        Cerrar sesión
    </a>
</nav>
