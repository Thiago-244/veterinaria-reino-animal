-- Seeds mínimos
INSERT INTO especies (nombre) VALUES ('Canino') ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO especies (nombre) VALUES ('Felino') ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

INSERT INTO razas (id_especie, nombre)
SELECT e.id, 'Mestizo' FROM especies e WHERE e.nombre='Canino'
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

INSERT INTO usuarios (nombre,email,password,rol,estado)
VALUES ('Admin','admin@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'Administrador', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);


