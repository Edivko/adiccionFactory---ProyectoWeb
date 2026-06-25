<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';

// ─── Obtener ID del inmueble ──────────────────────────────────────────────────

$idInmueble = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$idInmueble) {
    header('Location: inmuebles.php');
    exit;
}

// ─── Mensajes de sesión ───────────────────────────────────────────────────────

$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
$errorGeneral = $_SESSION['error_general'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['error_general']);

// ─── Cargar datos completos del inmueble ──────────────────────────────────────

$inmueble = null;

try {
    $stmt = mysqli_prepare($conexion, '
        SELECT
            i.id_inmueble,
            i.titulo,
            i.descripcion,
            i.precio,
            i.moneda,
            i.estado        AS estado_geo,
            i.ciudad,
            i.colonia,
            i.direccion,
            i.codigo_postal,
            i.recamaras,
            i.banos,
            i.estacionamientos,
            i.metros_terreno,
            i.metros_construccion,
            i.antiguedad,
            i.fecha_registro,
            i.id_estado_publicacion,
            i.id_categoria,
            i.id_usuario_publicador,
            ep.nombre_estado   AS estado_pub,
            ci.nombre_categoria AS categoria,
            co.nombre_condicion AS condicion,
            u.nombre            AS nombre_pub,
            u.apellido          AS apellido_pub,
            u.correo            AS correo_pub,
            u.telefono          AS telefono_pub
        FROM Inmueble i
        INNER JOIN EstadoPublicacion ep ON ep.id_estado_publicacion = i.id_estado_publicacion
        INNER JOIN CategoriaInmueble ci ON ci.id_categoria          = i.id_categoria
        LEFT  JOIN CondicionInmueble co ON co.id_condicion          = i.id_condicion
        INNER JOIN Usuario           u  ON u.id_usuario             = i.id_usuario_publicador
        WHERE i.id_inmueble = ?
        LIMIT 1
    ');
    mysqli_stmt_bind_param($stmt, 'i', $idInmueble);
    mysqli_stmt_execute($stmt);
    $inmueble = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    // $inmueble queda null
}

if (!$inmueble) {
    header('Location: inmuebles.php');
    exit;
}

$idEstadoPub    = (int) $inmueble['id_estado_publicacion'];
$esPendiente    = $idEstadoPub === 2;
$idCategoria    = (int) $inmueble['id_categoria'];
$idPublicador   = (int) $inmueble['id_usuario_publicador'];
$direccion      = $inmueble['direccion'];
$codigoPostal   = $inmueble['codigo_postal'];

// ─── Fotos del inmueble ───────────────────────────────────────────────────────

$fotos = [];

try {
    $stmtF = mysqli_prepare($conexion,
        'SELECT url_foto, descripcion, principal
         FROM FotoInmueble
         WHERE id_inmueble = ?
         ORDER BY principal DESC, id_foto ASC'
    );
    mysqli_stmt_bind_param($stmtF, 'i', $idInmueble);
    mysqli_stmt_execute($stmtF);
    $fotos = mysqli_fetch_all(mysqli_stmt_get_result($stmtF), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtF);
} catch (mysqli_sql_exception $e) {
    // Sin fotos
}

// ─── Advertencia de coincidencias (OR entre 4 condiciones) ───────────────────

$coincidencias = [];

try {
    // Condiciones dinámicas: solo incluir campo si no es NULL
    $condWhere = [];
    $paramTypes = 'i'; // el primer ? es siempre id_inmueble
    $paramVals  = [$idInmueble];

    if ($direccion !== null && $direccion !== '') {
        $condWhere[] = '(i.direccion IS NOT NULL AND i.direccion = ?)';
        $paramTypes .= 's';
        $paramVals[] = $direccion;
    }
    if ($codigoPostal !== null && $codigoPostal !== '') {
        $condWhere[] = '(i.codigo_postal IS NOT NULL AND i.codigo_postal = ?)';
        $paramTypes .= 's';
        $paramVals[] = $codigoPostal;
    }
    $condWhere[] = 'i.id_usuario_publicador = ?';
    $paramTypes .= 'i';
    $paramVals[] = $idPublicador;

    $condWhere[] = 'i.id_categoria = ?';
    $paramTypes .= 'i';
    $paramVals[] = $idCategoria;

    $sqlCoinc = '
        SELECT i.id_inmueble, i.titulo, ep.nombre_estado AS estado
        FROM Inmueble i
        INNER JOIN EstadoPublicacion ep ON ep.id_estado_publicacion = i.id_estado_publicacion
        WHERE i.id_inmueble != ?
          AND (' . implode(' OR ', $condWhere) . ')
        ORDER BY i.id_inmueble DESC
        LIMIT 10
    ';
    $stmtC = mysqli_prepare($conexion, $sqlCoinc);
    mysqli_stmt_bind_param($stmtC, $paramTypes, ...$paramVals);
    mysqli_stmt_execute($stmtC);
    $coincidencias = mysqli_fetch_all(mysqli_stmt_get_result($stmtC), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtC);
} catch (mysqli_sql_exception $e) {
    // No bloquea
}

// ─── Historial de revisiones anteriores ──────────────────────────────────────

$revisiones = [];

try {
    $stmtR = mysqli_prepare($conexion, '
        SELECT
            ri.fecha_revision,
            ri.motivo,
            ep.nombre_estado AS estado_resultado,
            u.nombre         AS nombre_admin,
            u.apellido       AS apellido_admin
        FROM RevisionInmueble ri
        INNER JOIN EstadoPublicacion ep ON ep.id_estado_publicacion = ri.id_estado_publicacion
        INNER JOIN Administrador     a  ON a.id_administrador       = ri.id_administrador
        INNER JOIN Usuario           u  ON u.id_usuario             = a.id_usuario
        WHERE ri.id_inmueble = ?
        ORDER BY ri.fecha_revision DESC
    ');
    mysqli_stmt_bind_param($stmtR, 'i', $idInmueble);
    mysqli_stmt_execute($stmtR);
    $revisiones = mysqli_fetch_all(mysqli_stmt_get_result($stmtR), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtR);
} catch (mysqli_sql_exception $e) {
    // Sin historial
}

// ─── Vista ────────────────────────────────────────────────────────────────────

$paginaActual = 'inmuebles.php';
$tituloPagina = 'Revisar inmueble | Adicción Factory Inmobiliaria';
$tituloEsc    = htmlspecialchars($inmueble['titulo'], ENT_QUOTES, 'UTF-8');

include '../public/includes/header.php';
?>

<main class="seccion seccion-clara">
    <div class="contenedor">

        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="titulo-seccion">
            <p class="etiqueta">
                <a href="inmuebles.php" style="color:inherit;">← Inmuebles</a>
            </p>
            <h2><?php echo $tituloEsc; ?></h2>
            <p>
                Estado actual:
                <strong><?php echo htmlspecialchars(ucfirst($inmueble['estado_pub']), ENT_QUOTES, 'UTF-8'); ?></strong>
            </p>
        </div>

        <?php if ($mensajeExito !== null): ?>
            <div class="mensaje-exito" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorGeneral !== null): ?>
            <div class="mensaje-error" style="margin-bottom:24px;">
                <?php echo htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Advertencia informativa de coincidencias -->
        <?php if (!empty($coincidencias)): ?>
            <div class="card" style="padding:20px;margin-bottom:28px;border-left:4px solid #f39c12;">
                <p style="font-weight:700;color:#f39c12;margin-bottom:10px;">
                    Aviso informativo: otros inmuebles con datos similares
                </p>
                <p style="font-size:13px;color:#666;margin-bottom:12px;">
                    Los siguientes inmuebles comparten dirección, código postal, publicador o categoría con este.
                    Esta advertencia no impide la aprobación.
                </p>
                <ul style="margin:0;padding-left:20px;font-size:13px;">
                    <?php foreach ($coincidencias as $c): ?>
                        <li style="margin-bottom:4px;">
                            <a href="revisar-inmueble.php?id=<?php echo (int) $c['id_inmueble']; ?>"
                               style="color:var(--color-principal);">
                                #<?php echo (int) $c['id_inmueble']; ?> —
                                <?php echo htmlspecialchars($c['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <span style="color:#aaa;"> (<?php echo htmlspecialchars(ucfirst($c['estado']), ENT_QUOTES, 'UTF-8'); ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;margin-bottom:32px;">

            <!-- Datos del publicador -->
            <div class="card detalle-bloque">
                <div class="card-contenido">
                    <p class="etiqueta">Publicador</p>
                    <p style="font-size:18px;font-weight:700;margin-bottom:6px;">
                        <?php echo htmlspecialchars($inmueble['nombre_pub'] . ' ' . $inmueble['apellido_pub'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p style="color:#666;"><?php echo htmlspecialchars($inmueble['correo_pub'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if (!empty($inmueble['telefono_pub'])): ?>
                        <p style="color:#666;"><?php echo htmlspecialchars($inmueble['telefono_pub'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Datos principales -->
            <div class="card detalle-bloque">
                <div class="card-contenido">
                    <p class="etiqueta">Datos del inmueble</p>
                    <?php
                    $campos = [
                        'Categoría'          => $inmueble['categoria'],
                        'Condición'          => $inmueble['condicion'],
                        'Precio'             => $inmueble['precio'] !== null
                            ? '$' . number_format((float) $inmueble['precio'], 0, '.', ',') . ' ' . ($inmueble['moneda'] ?? '')
                            : null,
                        'Estado (geog.)'     => $inmueble['estado_geo'],
                        'Ciudad'             => $inmueble['ciudad'],
                        'Colonia'            => $inmueble['colonia'],
                        'Dirección'          => $inmueble['direccion'],
                        'Código postal'      => $inmueble['codigo_postal'],
                        'Recámaras'          => $inmueble['recamaras'],
                        'Baños'              => $inmueble['banos'],
                        'Estacionamientos'   => $inmueble['estacionamientos'],
                        'm² terreno'         => $inmueble['metros_terreno'],
                        'm² construcción'    => $inmueble['metros_construccion'],
                        'Antigüedad (años)'  => $inmueble['antiguedad'],
                        'Registrado'         => date('d/m/Y', strtotime($inmueble['fecha_registro'])),
                    ];
                    foreach ($campos as $lbl => $val):
                        if ($val === null || $val === '') continue;
                    ?>
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--color-borde);font-size:14px;">
                            <span style="color:#888;"><?php echo htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span style="font-weight:600;"><?php echo htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Descripción -->
        <?php if (!empty($inmueble['descripcion'])): ?>
            <div class="card" style="padding:24px;margin-bottom:28px;">
                <p class="etiqueta">Descripción</p>
                <p style="color:#4a4a4a;line-height:1.7;margin-top:8px;">
                    <?php echo nl2br(htmlspecialchars($inmueble['descripcion'], ENT_QUOTES, 'UTF-8')); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Galería de fotos -->
        <?php if (!empty($fotos)): ?>
            <div style="margin-bottom:32px;">
                <h3 style="margin-bottom:16px;">Fotografías (<?php echo count($fotos); ?>)</h3>
                <div class="grid-3">
                    <?php foreach ($fotos as $foto): ?>
                        <div style="position:relative;border-radius:8px;overflow:hidden;">
                            <img src="../public/<?php echo htmlspecialchars($foto['url_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($foto['descripcion'] ?? $inmueble['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                 style="width:100%;height:200px;object-fit:cover;display:block;"
                                 onerror="this.src='../public/recursos/img/no-imagen.png'">
                            <?php if ($foto['principal']): ?>
                                <span style="position:absolute;top:8px;left:8px;background:#e94b27;color:#fff;
                                             font-size:12px;font-weight:700;padding:3px 8px;border-radius:4px;">
                                    Principal
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card" style="padding:20px;text-align:center;margin-bottom:28px;color:#888;">
                Sin fotografías adjuntas.
            </div>
        <?php endif; ?>

        <!-- Historial de revisiones -->
        <?php if (!empty($revisiones)): ?>
            <div style="margin-bottom:32px;">
                <h3 style="margin-bottom:16px;">Historial de revisiones</h3>
                <div class="card" style="padding:0;overflow:hidden;">
                    <?php foreach ($revisiones as $i => $rev): ?>
                        <div style="padding:16px 20px;<?php echo $i > 0 ? 'border-top:1px solid var(--color-borde);' : ''; ?>display:flex;gap:16px;align-items:flex-start;">
                            <div style="flex:0 0 auto;font-size:13px;color:#aaa;white-space:nowrap;">
                                <?php echo date('d/m/Y H:i', strtotime($rev['fecha_revision'])); ?>
                            </div>
                            <div style="flex:1;">
                                <span style="font-weight:700;">
                                    <?php echo htmlspecialchars(ucfirst($rev['estado_resultado']), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                por <?php echo htmlspecialchars($rev['nombre_admin'] . ' ' . $rev['apellido_admin'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!empty($rev['motivo'])): ?>
                                    <p style="margin:6px 0 0;font-size:13px;color:#666;font-style:italic;">
                                        "<?php echo htmlspecialchars($rev['motivo'], ENT_QUOTES, 'UTF-8'); ?>"
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulario de revisión (solo si está pendiente) -->
        <?php if ($esPendiente): ?>
            <div class="card" style="padding:30px;margin-bottom:32px;">
                <h3 style="margin-top:0;">Decisión de revisión</h3>
                <p style="color:#666;margin-bottom:20px;">
                    Al aprobar, el inmueble pasará a estado <strong>publicado</strong> y será visible en el catálogo.
                    Al rechazar, deberás indicar el motivo obligatoriamente.
                </p>

                <form action="../procesos/procesar-revision-inmueble.php"
                      method="POST"
                      class="formulario"
                      id="form-revision">

                    <input type="hidden" name="id_inmueble" value="<?php echo $idInmueble; ?>">

                    <div>
                        <label for="motivo">Motivo (obligatorio si rechazas)</label>
                        <textarea id="motivo"
                                  name="motivo"
                                  rows="4"
                                  placeholder="Describe el motivo del rechazo o deja en blanco si apruebas…"></textarea>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap;">
                        <button type="submit"
                                name="accion"
                                value="aprobar"
                                class="btn btn-secundario"
                                onclick="return confirm('¿Aprobar y publicar este inmueble?');">
                            Aprobar y publicar
                        </button>
                        <button type="submit"
                                name="accion"
                                value="rechazar"
                                class="btn btn-claro"
                                style="color:#e94b27;border-color:#e94b27;"
                                onclick="return confirm('¿Rechazar este inmueble? Asegúrate de haber escrito el motivo.');">
                            Rechazar
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="card" style="padding:20px;text-align:center;margin-bottom:28px;">
                <p style="color:#888;">
                    Este inmueble no está en estado <strong>pendiente</strong>.
                    No se puede emitir una nueva revisión desde aquí.
                </p>
            </div>
        <?php endif; ?>

        <div>
            <a href="inmuebles.php" class="btn btn-claro">← Volver a inmuebles</a>
        </div>

    </div>
</main>

<?php include '../public/includes/footer.php'; ?>
