-- Actualizar correo del administrador por defecto
-- Ejecutar: mariadb -u rentas -p rentas_cdmx < sql/migration_admin_email.sql

USE rentas_cdmx;

UPDATE usuarios
SET email = 'Administrador@gmail.com'
WHERE rol = 'admin'
  AND email = 'admin@rentascdmx.mx';
