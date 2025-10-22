-- Script para crear la tabla citas
-- Ejecutar este script en la base de datos veterinaria_reino_animal

CREATE TABLE IF NOT EXISTS `citas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `fecha_cita` datetime NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `estado` enum('Pendiente','Procesada','Cancelada') NOT NULL DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_citas_codigo` (`codigo`),
  KEY `fk_citas_mascota` (`id_mascota`),
  CONSTRAINT `fk_citas_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
