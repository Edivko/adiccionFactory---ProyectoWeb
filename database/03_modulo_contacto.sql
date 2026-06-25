-- ============================================================================
-- Adicción Factory Inmobiliaria
-- Extensión: módulo de contacto (formulario público "Contactar inmobiliaria")
-- Compatible con MariaDB 11.8
--
-- Justificación: el profesor exige el caso de uso "Contactar inmobiliaria"
-- para el actor Visitante (PDF, pág. 6: pantalla "Contacto"), pero el
-- modelo lógico original no incluía una tabla para esto. Esta extensión
-- sigue el mismo patrón ya usado en el resto del proyecto: catálogo +
-- entidad con estado, en vez de campos VARCHAR libres o ENUM.
--
-- Este script debe ejecutarse DESPUÉS de 01_crear_tablas.sql y
-- 02_datos_iniciales.sql. No modifica ninguna tabla existente.
-- ============================================================================

USE adiccion_factory;

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Catálogo: MotivoContacto
-- Lista fija de opciones para el <select> del formulario de contacto.
-- ----------------------------------------------------------------------------
CREATE TABLE MotivoContacto (
    id_motivo     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_motivo VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_motivocontacto_nombre (nombre_motivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de motivos del formulario de contacto público';

-- ----------------------------------------------------------------------------
-- Catálogo: EstadoMensajeContacto
-- Estado de seguimiento del mensaje por parte del administrador.
-- ----------------------------------------------------------------------------
CREATE TABLE EstadoMensajeContacto (
    id_estado_mensaje INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_estado     VARCHAR(50) NOT NULL,
    UNIQUE KEY uq_estadomensajecontacto_nombre (nombre_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo: pendiente, atendido';

-- ----------------------------------------------------------------------------
-- Entidad: MensajeContacto
-- Mensaje enviado por un visitante (o usuario registrado) desde la pantalla
-- pública "Contacto". No requiere sesión, por lo que no se relaciona con
-- Usuario de forma obligatoria.
-- ----------------------------------------------------------------------------
CREATE TABLE MensajeContacto (
    id_mensaje        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_motivo         INT UNSIGNED NOT NULL,
    id_estado_mensaje INT UNSIGNED NOT NULL,
    nombre            VARCHAR(100) NOT NULL,
    apellido          VARCHAR(100) NOT NULL,
    correo            VARCHAR(150) NOT NULL,
    telefono          VARCHAR(20) NULL,
    asunto            VARCHAR(150) NOT NULL,
    mensaje           TEXT NOT NULL,
    fecha_envio       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mensajecontacto_motivo
        FOREIGN KEY (id_motivo) REFERENCES MotivoContacto (id_motivo)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_mensajecontacto_estado
        FOREIGN KEY (id_estado_mensaje) REFERENCES EstadoMensajeContacto (id_estado_mensaje)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Mensajes enviados desde el formulario público de contacto';

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Datos iniciales de los catálogos nuevos
-- (PROPUESTA TÉCNICA de ejemplo; ampliable desde el panel de administrador)
-- ----------------------------------------------------------------------------
INSERT INTO MotivoContacto (nombre_motivo) VALUES
    ('Información general'),
    ('Soporte'),
    ('Queja'),
    ('Otro');

INSERT INTO EstadoMensajeContacto (nombre_estado) VALUES
    ('pendiente'),
    ('atendido');
