-- Ajustes para el módulo de gestión de vendedores
-- Se agrega la columna estatus para el control de aprobación en el panel de administrador
ALTER TABLE Vendedor ADD COLUMN estatus VARCHAR(20) DEFAULT 'Pendiente';