<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_once __DIR__ . '/../config/conexion.php';

$idComprador = (int) $_SESSION['id_perfil'];

// ─── Leer mensajes de sesión ─────────────────────────────────────────────────

$errorGeneral = $_SESSION['error_agendar'] ?? null;
unset($_SESSION['error_agendar']);

// ─── Inmueble pre-seleccionado (opcional vía GET) ─────────────────────────────

$idInmueble   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$inmueble     = null;
$vendedores   = [];

if ($idInmueble !== null && $idInmueble !== false) {

    try {
        $stmt = mysqli_prepare($conexion, '
            SELECT
                i.id_inmueble,
                i.titulo,
                i.precio,
                i.moneda,
                i.ciudad,
                i.estado          AS estado_geo,
                (SELECT url_foto
                 FROM FotoInmueble
                 WHERE id_inmueble = i.id_inmueble AND principal = TRUE
                 LIMIT 1) AS url_foto
            FROM Inmueble i
            WHERE i.id_inmueble = ? AND i.id_estado_publicacion = 3
            LIMIT 1
        ');
        mysqli_stmt_bind_param($stmt, 'i', $idInmueble);
        mysqli_stmt_execute($stmt);
        $inmueble = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        $inmueble = null;
    }

    if ($inmueble !== null) {
        try {
            $stmtV = mysqli_prepare($conexion, '
                SELECT v.id_vendedor, u.nombre, u.apellido
                FROM Vendedor v
                INNER JOIN Usuario u ON u.id_usuario = v.id_usuario
                INNER JOIN InmuebleVendedor iv ON iv.id_vendedor = v.id_vendedor
                WHERE iv.id_inmueble = ? AND iv.activo = TRUE
                ORDER BY u.apellido, u.nombre
            ');
            mysqli_stmt_bind_param($stmtV, 'i', $idInmueble);
            mysqli_stmt_execute($stmtV);
            $vendedores = mysqli_fetch_all(mysqli_stmt_get_result($stmtV), MYSQLI_ASSOC);
            mysqli_stmt_close($stmtV);
        } catch (mysqli_sql_exception $e) {
            $vendedores = [];
        }
    }
}

// ─── Lista de inmuebles publicados (para el selector) ────────────────────────

$inmuebles = [];

try {
    $stmtI = mysqli_prepare($conexion, '
        SELECT i.id_inmueble, i.titulo, i.ciudad, i.estado AS estado_geo
        FROM Inmueble i
        WHERE i.id_estado_publicacion = 3
        ORDER BY i.titulo
    ');
    mysqli_stmt_execute($stmtI);
    $inmuebles = mysqli_fetch_all(mysqli_stmt_get_result($stmtI), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtI);
} catch (mysqli_sql_exception $e) {
    $inmuebles = [];
}

// ─── Lista de vendedores activos (cuando no hay inmueble pre-seleccionado) ────

$todosVendedores = [];

if ($inmueble === null) {
    try {
        $stmtAV = mysqli_prepare($conexion, '
            SELECT v.id_vendedor, u.nombre, u.apellido
            FROM Vendedor v
            INNER JOIN Usuario u ON u.id_usuario = v.id_usuario
            ORDER BY u.apellido, u.nombre
        ');
        mysqli_stmt_execute($stmtAV);
        $todosVendedores = mysqli_fetch_all(mysqli_stmt_get_result($stmtAV), MYSQLI_ASSOC);
        mysqli_stmt_close($stmtAV);
    } catch (mysqli_sql_exception $e) {
        $todosVendedores = [];
    }
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'agendar.php';
$tituloPagina = 'Agendar cita | Adicción Factory Inmobiliaria';

include __DIR__ . '/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <h2>Agendar Cita</h2>
            <p>Selecciona la fecha y hora para visitar una propiedad. El vendedor te confirmará pronto.</p>
        </div>

        <?php if ($errorGeneral !== null): ?>
            <div class="mensaje-error" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($inmuebles)): ?>

            <div class="card" style="padding:40px;text-align:center;">
                <h3 style="color:#666;font-weight:normal;">No hay propiedades disponibles en este momento.</h3>
                <p style="margin-top:15px;">Vuelve más tarde o explora el catálogo público.</p>
                <a href="../public/catalogo.php" class="btn btn-secundario" style="margin-top:20px;">
                    Ver catálogo
                </a>
            </div>

        <?php else: ?>

            <div class="grid-2">

                <!-- Columna izquierda: vista previa del inmueble -->
                <?php if ($inmueble !== null): ?>
                    <div class="card-inmueble">
                        <?php if (!empty($inmueble['url_foto'])): ?>
                            <img src="<?php echo htmlspecialchars($inmueble['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($inmueble['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <div class="sin-foto" aria-label="Sin fotografía"></div>
                        <?php endif; ?>
                        <div class="card-contenido">
                            <h3><?php echo htmlspecialchars($inmueble['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <?php
                            $partes = array_filter([
                                $inmueble['ciudad']     ?? '',
                                $inmueble['estado_geo'] ?? '',
                            ]);
                            if (!empty($partes)):
                            ?>
                                <p class="ubicacion">
                                    <?php echo htmlspecialchars(implode(', ', $partes), ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($inmueble['precio'])): ?>
                                <p class="precio">
                                    $<?php echo number_format((float) $inmueble['precio'], 0, '.', ','); ?>
                                    <?php echo htmlspecialchars($inmueble['moneda'] ?? 'MXN', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card" style="padding:30px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;gap:12px;">
                        <p style="font-size:40px;">🏠</p>
                        <p style="color:#666;">Selecciona una propiedad del catálogo para ver su vista previa aquí.</p>
                        <a href="../public/catalogo.php" class="btn btn-claro" style="margin-top:8px;">
                            Ver catálogo
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Columna derecha: formulario -->
                <div class="card" style="padding:30px;">
                    <h3 style="margin-bottom:20px;">Detalles de la visita</h3>

                    <form class="formulario" action="../procesos/procesar-agendar-cita.php" method="POST">

                        <?php if ($inmueble !== null): ?>
                            <input type="hidden" name="id_inmueble"
                                   value="<?php echo (int) $inmueble['id_inmueble']; ?>">
                        <?php else: ?>
                            <div>
                                <label for="id_inmueble">Propiedad <span style="color:#e94b27;">*</span></label>
                                <select id="id_inmueble" name="id_inmueble" required>
                                    <option value="">-- Elige una propiedad --</option>
                                    <?php foreach ($inmuebles as $inm): ?>
                                        <option value="<?php echo (int) $inm['id_inmueble']; ?>">
                                            <?php
                                            $partes = array_filter([
                                                $inm['ciudad']     ?? '',
                                                $inm['estado_geo'] ?? '',
                                            ]);
                                            $lugar = !empty($partes) ? ' — ' . implode(', ', $partes) : '';
                                            echo htmlspecialchars($inm['titulo'] . $lugar, ENT_QUOTES, 'UTF-8');
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top:15px;">
                            <label for="id_vendedor">Vendedor <span style="color:#e94b27;">*</span></label>
                            <select id="id_vendedor" name="id_vendedor" required>
                                <option value="">-- Elige un vendedor --</option>
                                <?php
                                $listaVendedores = ($inmueble !== null) ? $vendedores : $todosVendedores;
                                foreach ($listaVendedores as $vend):
                                ?>
                                    <option value="<?php echo (int) $vend['id_vendedor']; ?>">
                                        <?php echo htmlspecialchars(
                                            $vend['nombre'] . ' ' . $vend['apellido'],
                                            ENT_QUOTES, 'UTF-8'
                                        ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($inmueble !== null && empty($vendedores)): ?>
                                <p class="form-error">Esta propiedad no tiene vendedores asignados actualmente.</p>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top:15px;">
                            <label for="fecha">Fecha sugerida <span style="color:#e94b27;">*</span></label>
                            <input type="date" id="fecha" name="fecha"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                   required>
                        </div>

                        <div style="margin-top:15px;">
                            <label for="hora">Hora sugerida (09:00 – 17:00) <span style="color:#e94b27;">*</span></label>
                            <input type="time" id="hora" name="hora"
                                   min="09:00" max="17:00" required>
                        </div>

                        <div style="margin-top:15px;">
                            <label for="comentario_solicitud">Mensaje para el vendedor (opcional)</label>
                            <textarea id="comentario_solicitud" name="comentario_solicitud"
                                      placeholder="Ej. Me interesa ver los acabados de la cocina..."></textarea>
                        </div>

                        <div style="margin-top:25px;">
                            <button type="submit" class="btn btn-principal btn-completo">Confirmar cita</button>
                        </div>

                    </form>
                </div>

            </div>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
