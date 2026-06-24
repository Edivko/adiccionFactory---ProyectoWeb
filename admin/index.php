<?php
declare(strict_types=1);

// 1. conexión a la base de datos
require_once __DIR__ . '/../config/conexion.php';

// 2. Consultas para obtener los totales

// Contar usuarios
$queryUsuarios = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM Usuario");
$totalUsuarios = mysqli_fetch_assoc($queryUsuarios)['total'] ?? 0;

// Contar inmuebles
$queryInmuebles = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM Inmueble");
$totalInmuebles = mysqli_fetch_assoc($queryInmuebles)['total'] ?? 0;

// Contar comentarios (Ajustado para que coincida con tu tarjeta de Moderación)
$queryComentarios = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM Comentario");
$totalComentarios = mysqli_fetch_assoc($queryComentarios)['total'] ?? 0;
?>
<?php
$tituloPagina = "Dashboard Admin | Adicción Factory";
include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">
        
        <?php include 'nav_admin.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">Panel de Control Supremo</p>
            <h2>Panel de Administración</h2>
            <p>Supervisa el estado general de la plataforma, gestiona los accesos y modera el contenido.</p>
        </div>

        <div class="grid-3">
            <article class="card">
                <div class="card-contenido">
                    <h3>Usuarios Registrados</h3>
                    <p>Total de compradores activos en la plataforma: <strong><?php echo $totalUsuarios; ?></strong></p>
                    <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                        <a href="gestionar_usuarios.php" class="btn btn-principal btn-completo">Administrar Usuarios</a>
                    </div>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <h3>Catálogo de Inmuebles</h3>
                    <p>Propiedades publicadas y pendientes de auditoría: <strong><?php echo $totalInmuebles; ?></strong></p>
                    <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                        <a href="gestionar_inmuebles.php" class="btn btn-secundario btn-completo">Auditar Inmuebles</a>
                    </div>
                </div>
            </article>

            <article class="card">
                <div class="card-contenido">
                    <h3>Moderación</h3>
                    <p>Comentarios y reportes pendientes de revisión: <strong><?php echo $totalComentarios; ?></strong></p>
                    <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                        <a href="moderar_comentarios.php" class="btn btn-claro btn-completo">Revisar Comentarios</a>
                    </div>
                </div>
            </article>
        </div>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>