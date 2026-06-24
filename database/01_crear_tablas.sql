-- ============================================================================
-- Adicción Factory Inmobiliaria
-- Script de creación de tablas (modelo físico)
-- Motor: InnoDB | Codificación: utf8mb4 | Compatible con MariaDB 11.8
--
-- Orden de creación: respeta las dependencias de claves foráneas.
-- Política general de integridad referencial:
--   ON DELETE RESTRICT  -> no se permite borrar un registro si tiene
--                          dependientes (alineado con la regla de
--                          eliminación lógica del proyecto).
--   ON UPDATE CASCADE   -> si un identificador cambiara, se propaga
--                          automáticamente a las tablas hijas.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- MÓDULO 1: CATÁLOGOS
-- ============================================================================

CREATE TABLE RolUsuario (
    id_rol      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_rol  VARCHAR(50) NOT NULL,
    UNIQUE KEY uq_rolusuario_nombre (nombre_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de roles: comprador, vendedor, administrador';

CREATE TABLE EstadoCuenta (
    id_estado_cuenta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_estado    VARCHAR(50) NOT NULL,
    UNIQUE KEY uq_estadocuenta_nombre (nombre_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo: pendiente, activa, bloqueada, rechazada, inactiva';

CREATE TABLE CategoriaInmueble (
    id_categoria     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion      TEXT NULL,
    activo           BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE KEY uq_categoriainmueble_nombre (nombre_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo: casa, departamento, terreno, local, etc.';

CREATE TABLE CondicionInmueble (
    id_condicion     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_condicion VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_condicioninmueble_nombre (nombre_condicion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo: nuevo, usado, remodelado, preventa';

CREATE TABLE EstadoPublicacion (
    id_estado_publicacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_estado         VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_estadopublicacion_nombre (nombre_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo: borrador, pendiente, publicado, rechazado, pausado, vendido, archivado';

CREATE TABLE EstadoCita (
    id_estado_cita INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_estado  VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_estadocita_nombre (nombre_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo: pendiente, aceptada, rechazada, cancelada, realizada';

CREATE TABLE EstadoComentario (
    id_estado_comentario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_estado         VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_estadocomentario_nombre (nombre_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo: pendiente, visible, oculto, eliminado';

CREATE TABLE Servicio (
    id_servicio    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_servicio VARCHAR(100) NOT NULL,
    activo         BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE KEY uq_servicio_nombre (nombre_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de servicios del inmueble (luz, agua, gas, etc.)';

CREATE TABLE Amenidad (
    id_amenidad    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_amenidad VARCHAR(100) NOT NULL,
    activo         BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE KEY uq_amenidad_nombre (nombre_amenidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de amenidades del inmueble (alberca, gimnasio, etc.)';

-- ============================================================================
-- MÓDULO 2: USUARIOS Y ROLES
-- ============================================================================

CREATE TABLE Usuario (
    id_usuario       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol           INT UNSIGNED NOT NULL,
    id_estado_cuenta INT UNSIGNED NOT NULL,
    nombre           VARCHAR(100) NOT NULL,
    apellido         VARCHAR(100) NOT NULL,
    correo           VARCHAR(150) NOT NULL,
    telefono         VARCHAR(20) NULL,
    password_hash    VARCHAR(255) NOT NULL,
    fecha_registro   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_usuario_correo (correo),

    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (id_rol) REFERENCES RolUsuario (id_rol)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_usuario_estadocuenta
        FOREIGN KEY (id_estado_cuenta) REFERENCES EstadoCuenta (id_estado_cuenta)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Datos generales de acceso de todo usuario registrado';

CREATE TABLE Comprador (
    id_comprador       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario         INT UNSIGNED NOT NULL,
    presupuesto_minimo DECIMAL(12,2) NULL,
    presupuesto_maximo DECIMAL(12,2) NULL,
    zona_interes       VARCHAR(150) NULL,

    UNIQUE KEY uq_comprador_usuario (id_usuario),

    CONSTRAINT fk_comprador_usuario
        FOREIGN KEY (id_usuario) REFERENCES Usuario (id_usuario)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Datos específicos del comprador (relación 1 a 1 con Usuario)';

CREATE TABLE Vendedor (
    id_vendedor   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario    INT UNSIGNED NOT NULL,
    descripcion   TEXT NULL,
    experiencia   INT NULL,
    foto_perfil   VARCHAR(255) NULL,
    zona_trabajo  VARCHAR(150) NULL,

    UNIQUE KEY uq_vendedor_usuario (id_usuario),

    CONSTRAINT fk_vendedor_usuario
        FOREIGN KEY (id_usuario) REFERENCES Usuario (id_usuario)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Perfil público del vendedor (relación 1 a 1 con Usuario). Sin calificacion_promedio: se calcula con AVG sobre CalificacionVendedor';

CREATE TABLE Administrador (
    id_administrador INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario        INT UNSIGNED NOT NULL,
    nivel_acceso      VARCHAR(50) NULL,

    UNIQUE KEY uq_administrador_usuario (id_usuario),

    CONSTRAINT fk_administrador_usuario
        FOREIGN KEY (id_usuario) REFERENCES Usuario (id_usuario)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Datos específicos del administrador (relación 1 a 1 con Usuario)';

-- ============================================================================
-- MÓDULO 3: INMUEBLES Y RELACIONADAS
-- ============================================================================

CREATE TABLE Inmueble (
    id_inmueble           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario_publicador INT UNSIGNED NOT NULL,
    id_categoria          INT UNSIGNED NOT NULL,
    id_condicion          INT UNSIGNED NULL,
    id_estado_publicacion INT UNSIGNED NOT NULL,

    titulo                VARCHAR(150) NOT NULL,
    descripcion           TEXT NULL,
    precio                DECIMAL(12,2) NULL,
    moneda                VARCHAR(10) NULL,
    estado                VARCHAR(100) NULL COMMENT 'Entidad geográfica (ej. Estado de México), no confundir con estado de publicación',
    ciudad                VARCHAR(100) NULL,
    colonia               VARCHAR(100) NULL,
    direccion             VARCHAR(255) NULL,
    codigo_postal         VARCHAR(10) NULL,
    recamaras             INT NULL,
    banos                 DECIMAL(3,1) NULL,
    estacionamientos      INT NULL,
    metros_terreno        DECIMAL(10,2) NULL,
    metros_construccion   DECIMAL(10,2) NULL,
    antiguedad            INT NULL,
    fecha_publicacion     DATETIME NULL,
    fecha_registro        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_inmueble_publicador
        FOREIGN KEY (id_usuario_publicador) REFERENCES Usuario (id_usuario)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_inmueble_categoria
        FOREIGN KEY (id_categoria) REFERENCES CategoriaInmueble (id_categoria)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_inmueble_condicion
        FOREIGN KEY (id_condicion) REFERENCES CondicionInmueble (id_condicion)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_inmueble_estadopublicacion
        FOREIGN KEY (id_estado_publicacion) REFERENCES EstadoPublicacion (id_estado_publicacion)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Propiedad publicada o en proceso de publicación. Solo titulo, id_categoria, id_estado_publicacion, id_usuario_publicador y fecha_registro son obligatorios desde el borrador; el resto se valida en PHP antes de pasar de borrador a pendiente';

CREATE TABLE FotoInmueble (
    id_foto      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_inmueble  INT UNSIGNED NOT NULL,
    url_foto     VARCHAR(255) NOT NULL,
    descripcion  VARCHAR(150) NULL,
    principal    BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT fk_fotoinmueble_inmueble
        FOREIGN KEY (id_inmueble) REFERENCES Inmueble (id_inmueble)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Fotografías asociadas a un inmueble. La unicidad de una sola foto principal por inmueble se valida en PHP';

CREATE TABLE InmuebleVendedor (
    id_inmueble      INT UNSIGNED NOT NULL,
    id_vendedor      INT UNSIGNED NOT NULL,
    activo           BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_inmueble, id_vendedor),

    CONSTRAINT fk_inmuebleVendedor_inmueble
        FOREIGN KEY (id_inmueble) REFERENCES Inmueble (id_inmueble)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_inmuebleVendedor_vendedor
        FOREIGN KEY (id_vendedor) REFERENCES Vendedor (id_vendedor)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Vendedores que pueden atender citas de un inmueble. NO concede permisos de edición: estos se validan únicamente contra Inmueble.id_usuario_publicador';

CREATE TABLE InmuebleServicio (
    id_inmueble INT UNSIGNED NOT NULL,
    id_servicio INT UNSIGNED NOT NULL,

    PRIMARY KEY (id_inmueble, id_servicio),

    CONSTRAINT fk_inmuebleServicio_inmueble
        FOREIGN KEY (id_inmueble) REFERENCES Inmueble (id_inmueble)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_inmuebleServicio_servicio
        FOREIGN KEY (id_servicio) REFERENCES Servicio (id_servicio)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Relación muchos a muchos entre inmuebles y servicios';

CREATE TABLE InmuebleAmenidad (
    id_inmueble INT UNSIGNED NOT NULL,
    id_amenidad INT UNSIGNED NOT NULL,

    PRIMARY KEY (id_inmueble, id_amenidad),

    CONSTRAINT fk_inmuebleAmenidad_inmueble
        FOREIGN KEY (id_inmueble) REFERENCES Inmueble (id_inmueble)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_inmuebleAmenidad_amenidad
        FOREIGN KEY (id_amenidad) REFERENCES Amenidad (id_amenidad)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Relación muchos a muchos entre inmuebles y amenidades';

CREATE TABLE RevisionInmueble (
    id_revision           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_inmueble           INT UNSIGNED NOT NULL,
    id_administrador      INT UNSIGNED NOT NULL,
    id_estado_publicacion INT UNSIGNED NOT NULL,
    motivo                TEXT NULL COMMENT 'Obligatorio en PHP cuando el resultado es rechazado',
    fecha_revision        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_revisionInmueble_inmueble
        FOREIGN KEY (id_inmueble) REFERENCES Inmueble (id_inmueble)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_revisionInmueble_administrador
        FOREIGN KEY (id_administrador) REFERENCES Administrador (id_administrador)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_revisionInmueble_estadopublicacion
        FOREIGN KEY (id_estado_publicacion) REFERENCES EstadoPublicacion (id_estado_publicacion)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial inmutable de decisiones administrativas sobre un inmueble. Nunca se sobrescribe: cada revisión es un registro nuevo';

-- ============================================================================
-- MÓDULO 4: CITAS
-- ============================================================================

CREATE TABLE Cita (
    id_cita              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_comprador         INT UNSIGNED NOT NULL,
    id_inmueble          INT UNSIGNED NOT NULL,
    id_vendedor          INT UNSIGNED NOT NULL,
    id_estado_cita       INT UNSIGNED NOT NULL,
    fecha_inicio         DATETIME NOT NULL,
    fecha_fin            DATETIME NOT NULL,
    comentario_solicitud TEXT NULL,
    fecha_solicitud      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_cita_combinacion (id_comprador, id_inmueble, id_vendedor, fecha_inicio),

    CONSTRAINT fk_cita_comprador
        FOREIGN KEY (id_comprador) REFERENCES Comprador (id_comprador)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_cita_inmueble
        FOREIGN KEY (id_inmueble) REFERENCES Inmueble (id_inmueble)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_cita_vendedor
        FOREIGN KEY (id_vendedor) REFERENCES Vendedor (id_vendedor)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_cita_estadocita
        FOREIGN KEY (id_estado_cita) REFERENCES EstadoCita (id_estado_cita)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Solicitud y seguimiento de una visita entre comprador, inmueble y vendedor. Validado en PHP: fecha_inicio futura, horario L-S 09:00-18:00 (último inicio 17:00), sin superposición de horario para citas pendientes/aceptadas del mismo vendedor';

-- ============================================================================
-- MÓDULO 5: COMENTARIOS Y CALIFICACIONES
-- ============================================================================

CREATE TABLE Comentario (
    id_comentario         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario            INT UNSIGNED NOT NULL,
    id_cita               INT UNSIGNED NOT NULL,
    id_vendedor           INT UNSIGNED NULL,
    id_inmueble           INT UNSIGNED NULL,
    id_estado_comentario  INT UNSIGNED NOT NULL,
    contenido             TEXT NOT NULL,
    fecha_comentario      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_comentario_usuario
        FOREIGN KEY (id_usuario) REFERENCES Usuario (id_usuario)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_comentario_cita
        FOREIGN KEY (id_cita) REFERENCES Cita (id_cita)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_comentario_vendedor
        FOREIGN KEY (id_vendedor) REFERENCES Vendedor (id_vendedor)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_comentario_inmueble
        FOREIGN KEY (id_inmueble) REFERENCES Inmueble (id_inmueble)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_comentario_estadocomentario
        FOREIGN KEY (id_estado_comentario) REFERENCES EstadoComentario (id_estado_comentario)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Comentario de un comprador sobre un vendedor o un inmueble, ligado a una cita realizada. Regla XOR (exactamente uno entre id_vendedor / id_inmueble) validada en PHP, no en SQL';

CREATE TABLE CalificacionVendedor (
    id_calificacion_vendedor INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_comprador             INT UNSIGNED NOT NULL,
    id_vendedor               INT UNSIGNED NOT NULL,
    id_cita                   INT UNSIGNED NOT NULL,
    puntuacion                 TINYINT UNSIGNED NOT NULL,
    comentario                 TEXT NULL,
    fecha_calificacion         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_calificacionvendedor_cita (id_cita),
    CONSTRAINT chk_calificacionvendedor_puntuacion
        CHECK (puntuacion BETWEEN 1 AND 5),

    CONSTRAINT fk_calificacionvendedor_comprador
        FOREIGN KEY (id_comprador) REFERENCES Comprador (id_comprador)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_calificacionvendedor_vendedor
        FOREIGN KEY (id_vendedor) REFERENCES Vendedor (id_vendedor)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_calificacionvendedor_cita
        FOREIGN KEY (id_cita) REFERENCES Cita (id_cita)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Evaluación del comprador hacia el vendedor tras una cita realizada. Cardinalidad Cita 1 a 0..1, garantizada con UNIQUE sobre id_cita';

CREATE TABLE CalificacionComprador (
    id_calificacion_comprador INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_vendedor                 INT UNSIGNED NOT NULL,
    id_comprador                 INT UNSIGNED NOT NULL,
    id_cita                       INT UNSIGNED NOT NULL,
    puntuacion                     TINYINT UNSIGNED NOT NULL,
    comentario                     TEXT NULL,
    fecha_calificacion             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_calificacioncomprador_cita (id_cita),
    CONSTRAINT chk_calificacioncomprador_puntuacion
        CHECK (puntuacion BETWEEN 1 AND 5),

    CONSTRAINT fk_calificacioncomprador_vendedor
        FOREIGN KEY (id_vendedor) REFERENCES Vendedor (id_vendedor)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_calificacioncomprador_comprador
        FOREIGN KEY (id_comprador) REFERENCES Comprador (id_comprador)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_calificacioncomprador_cita
        FOREIGN KEY (id_cita) REFERENCES Cita (id_cita)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Evaluación del vendedor hacia el comprador tras una cita realizada. Cardinalidad Cita 1 a 0..1, garantizada con UNIQUE sobre id_cita';

CREATE TABLE CalificacionInmueble (
    id_calificacion_inmueble INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_comprador               INT UNSIGNED NOT NULL,
    id_inmueble                 INT UNSIGNED NOT NULL,
    id_cita                     INT UNSIGNED NOT NULL,
    puntuacion                   TINYINT UNSIGNED NOT NULL,
    comentario                   TEXT NULL,
    fecha_calificacion           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_calificacioninmueble_cita (id_cita),
    CONSTRAINT chk_calificacioninmueble_puntuacion
        CHECK (puntuacion BETWEEN 1 AND 5),

    CONSTRAINT fk_calificacioninmueble_comprador
        FOREIGN KEY (id_comprador) REFERENCES Comprador (id_comprador)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_calificacioninmueble_inmueble
        FOREIGN KEY (id_inmueble) REFERENCES Inmueble (id_inmueble)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_calificacioninmueble_cita
        FOREIGN KEY (id_cita) REFERENCES Cita (id_cita)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Evaluación del comprador hacia el inmueble tras una cita realizada. Cardinalidad Cita 1 a 0..1, garantizada con UNIQUE sobre id_cita';

SET FOREIGN_KEY_CHECKS = 1;
