-- Esquema base para Veterinaria Reino Animal (MySQL 8+)
-- Ejecutar en un schema existente (use veterinaria_reino_animal;)

-- EMPRESA
CREATE TABLE IF NOT EXISTS empresa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  ruc VARCHAR(11) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  telefono VARCHAR(15) NOT NULL,
  email VARCHAR(100) NOT NULL,
  logo VARCHAR(255) NULL,
  UNIQUE KEY uq_empresa_ruc (ruc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- USUARIOS
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('Administrador','Editor','Consultor') NOT NULL DEFAULT 'Consultor',
  estado TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ESPECIES
CREATE TABLE IF NOT EXISTS especies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  UNIQUE KEY uq_especies_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- RAZAS
CREATE TABLE IF NOT EXISTS razas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_especie INT NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  CONSTRAINT fk_razas_especie FOREIGN KEY (id_especie) REFERENCES especies(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  UNIQUE KEY uq_raza_por_especie (id_especie, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- CLIENTES
CREATE TABLE IF NOT EXISTS clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dni VARCHAR(8) NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  apellido VARCHAR(50) NOT NULL,
  telefono VARCHAR(15) NOT NULL,
  direccion VARCHAR(255) NULL,
  email VARCHAR(100) NULL,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_clientes_dni (dni),
  UNIQUE KEY uq_clientes_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- MASCOTAS
CREATE TABLE IF NOT EXISTS mascotas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  id_cliente INT NOT NULL,
  id_raza INT NOT NULL,
  fecha_nacimiento DATE NULL,
  sexo ENUM('Macho','Hembra') NULL,
  CONSTRAINT fk_mascotas_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_mascotas_raza FOREIGN KEY (id_raza) REFERENCES razas(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  UNIQUE KEY uq_mascotas_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- PRODUCTOS/SERVICIOS
CREATE TABLE IF NOT EXISTS productoservicio (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('Producto','Servicio') NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DETALLE TEMPORAL (carrito)
CREATE TABLE IF NOT EXISTS detalle_temp (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  token_usuario VARCHAR(50) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_detalle_temp_producto FOREIGN KEY (id_producto) REFERENCES productoservicio(id) ON DELETE CASCADE ON UPDATE CASCADE,
  KEY idx_token_usuario (token_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- VENTAS
CREATE TABLE IF NOT EXISTS venta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_cliente INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_venta_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_venta_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DETALLE DE VENTA
CREATE TABLE IF NOT EXISTS detalle_venta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_detalle_venta_venta FOREIGN KEY (id_venta) REFERENCES venta(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_detalle_venta_producto FOREIGN KEY (id_producto) REFERENCES productoservicio(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


