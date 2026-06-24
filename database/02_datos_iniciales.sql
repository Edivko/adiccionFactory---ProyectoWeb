-- ============================================================================
-- Adicción Factory Inmobiliaria
-- Script de datos iniciales (catálogos)
-- Compatible con MariaDB 11.8
--
-- Este script debe ejecutarse DESPUÉS de 01_crear_tablas.sql.
-- Sin estos datos, no es posible insertar ningún Usuario ni Inmueble,
-- porque sus llaves foráneas exigen que ya exista al menos un valor
-- válido en cada catálogo referenciado.
--
-- Fuente de cada bloque de datos:
--   - RolUsuario, EstadoCuenta, CondicionInmueble, EstadoPublicacion,
--     EstadoCita, EstadoComentario: decisiones funcionales ya aprobadas
--     (valores cerrados, no se deben modificar sin nueva autorización).
--   - CategoriaInmueble, Servicio, Amenidad: ejemplos tomados del PDF
--     del proyecto (pág. 9, 11, 23). Son una PROPUESTA TÉCNICA de datos
--     de prueba, no una lista cerrada por el profesor; se pueden ampliar
--     más adelante sin afectar la estructura de las tablas.
-- ============================================================================

USE adiccion_factory;

-- ----------------------------------------------------------------------------
-- RolUsuario (cerrado: comprador, vendedor, administrador)
-- ----------------------------------------------------------------------------
INSERT INTO RolUsuario (nombre_rol) VALUES
    ('comprador'),
    ('vendedor'),
    ('administrador');

-- ----------------------------------------------------------------------------
-- EstadoCuenta (cerrado: pendiente, activa, bloqueada, rechazada, inactiva)
-- ----------------------------------------------------------------------------
INSERT INTO EstadoCuenta (nombre_estado) VALUES
    ('pendiente'),
    ('activa'),
    ('bloqueada'),
    ('rechazada'),
    ('inactiva');

-- ----------------------------------------------------------------------------
-- CondicionInmueble (cerrado: nuevo, usado, remodelado, preventa)
-- ----------------------------------------------------------------------------
INSERT INTO CondicionInmueble (nombre_condicion) VALUES
    ('nuevo'),
    ('usado'),
    ('remodelado'),
    ('preventa');

-- ----------------------------------------------------------------------------
-- EstadoPublicacion (cerrado: borrador, pendiente, publicado, rechazado,
-- pausado, vendido, archivado)
-- ----------------------------------------------------------------------------
INSERT INTO EstadoPublicacion (nombre_estado) VALUES
    ('borrador'),
    ('pendiente'),
    ('publicado'),
    ('rechazado'),
    ('pausado'),
    ('vendido'),
    ('archivado');

-- ----------------------------------------------------------------------------
-- EstadoCita (cerrado: pendiente, aceptada, rechazada, cancelada, realizada)
-- ----------------------------------------------------------------------------
INSERT INTO EstadoCita (nombre_estado) VALUES
    ('pendiente'),
    ('aceptada'),
    ('rechazada'),
    ('cancelada'),
    ('realizada');

-- ----------------------------------------------------------------------------
-- EstadoComentario (cerrado: pendiente, visible, oculto, eliminado)
-- ----------------------------------------------------------------------------
INSERT INTO EstadoComentario (nombre_estado) VALUES
    ('pendiente'),
    ('visible'),
    ('oculto'),
    ('eliminado');

-- ----------------------------------------------------------------------------
-- CategoriaInmueble (PROPUESTA TÉCNICA de ejemplo, basada en el PDF pág. 9)
-- Se puede ampliar libremente desde el panel de administrador (RF-ADM-002)
-- ----------------------------------------------------------------------------
INSERT INTO CategoriaInmueble (nombre_categoria, descripcion, activo) VALUES
    ('Casa', 'Vivienda unifamiliar independiente', TRUE),
    ('Departamento', 'Vivienda dentro de un edificio o condominio', TRUE),
    ('Terreno', 'Lote sin construcción', TRUE),
    ('Local comercial', 'Espacio destinado a uso comercial', TRUE);

-- ----------------------------------------------------------------------------
-- Servicio (PROPUESTA TÉCNICA de ejemplo, basada en el PDF pág. 23)
-- ----------------------------------------------------------------------------
INSERT INTO Servicio (nombre_servicio, activo) VALUES
    ('Agua', TRUE),
    ('Luz', TRUE),
    ('Gas', TRUE),
    ('Internet', TRUE);

-- ----------------------------------------------------------------------------
-- Amenidad (PROPUESTA TÉCNICA de ejemplo, basada en el PDF pág. 23)
-- ----------------------------------------------------------------------------
INSERT INTO Amenidad (nombre_amenidad, activo) VALUES
    ('Alberca', TRUE),
    ('Gimnasio', TRUE),
    ('Área de juegos', TRUE),
    ('Seguridad 24 horas', TRUE);
