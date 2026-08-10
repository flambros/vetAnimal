-- ============================================================
-- Veterinaria VetAnimal - Esquema de Base de Datos
-- ============================================================
CREATE DATABASE IF NOT EXISTS vetanimal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vetanimal;

-- ------------------------------------------------------------
-- Usuarios (clientes y veterinarios)
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('cliente','veterinario') NOT NULL DEFAULT 'cliente',
    telefono VARCHAR(30) DEFAULT NULL,
    especialidad VARCHAR(120) DEFAULT NULL, -- solo veterinarios
    foto VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expira DATETIME DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Mascotas
-- ------------------------------------------------------------
CREATE TABLE mascotas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    especie VARCHAR(40) NOT NULL,
    raza VARCHAR(80) DEFAULT NULL,
    edad INT DEFAULT NULL,
    peso DECIMAL(5,2) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    estado_salud VARCHAR(40) DEFAULT 'Estable',
    alergias VARCHAR(255) DEFAULT NULL,
    condiciones_cronicas VARCHAR(255) DEFAULT 'Ninguna diagnosticada a la fecha.',
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Servicios ofrecidos por la clínica
-- ------------------------------------------------------------
CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    duracion_min INT NOT NULL DEFAULT 30,
    precio DECIMAL(8,2) DEFAULT NULL,
    icono VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Turnos (citas)
-- ------------------------------------------------------------
CREATE TABLE turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id INT NOT NULL,
    servicio_id INT NOT NULL,
    veterinario_id INT DEFAULT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    estado ENUM('pendiente','confirmado','cancelado','completado') NOT NULL DEFAULT 'confirmado',
    notas VARCHAR(255) DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id),
    FOREIGN KEY (veterinario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    UNIQUE KEY unico_horario (veterinario_id, fecha, hora)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Historial clínico / consultas
-- ------------------------------------------------------------
CREATE TABLE consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id INT NOT NULL,
    veterinario_id INT DEFAULT NULL,
    fecha DATE NOT NULL,
    tipo ENUM('CONTROL','EMERGENCIA','VACUNA','CIRUGIA','DIAGNOSTICO') NOT NULL DEFAULT 'CONTROL',
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE,
    FOREIGN KEY (veterinario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Vacunación
-- ------------------------------------------------------------
CREATE TABLE vacunas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id INT NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    fecha_aplicacion DATE DEFAULT NULL,
    fecha_refuerzo DATE DEFAULT NULL,
    estado ENUM('AL_DIA','VENCIDA','PENDIENTE') NOT NULL DEFAULT 'AL_DIA',
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Estudios y laboratorio
-- ------------------------------------------------------------
CREATE TABLE estudios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    tipo VARCHAR(60) DEFAULT NULL,
    fecha DATE NOT NULL,
    resultado_url VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Productos de la tienda
-- ------------------------------------------------------------
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    categoria ENUM('Medicamentos','Bienestar y Estética','Nutrición y Alimento','Pulgas y Garrapatas') NOT NULL,
    etiqueta VARCHAR(30) DEFAULT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    precio DECIMAL(8,2) NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    requiere_receta TINYINT(1) DEFAULT 0,
    stock INT DEFAULT 50
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Pedidos de la tienda
-- ------------------------------------------------------------
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(9,2) NOT NULL DEFAULT 0,
    estado ENUM('pendiente','pagado','enviado','entregado') DEFAULT 'pendiente',
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- ============================================================
-- DATOS DE PRUEBA (SEED)
-- ============================================================

-- Contraseña para todos los usuarios de prueba: "123456"
-- Hash generado con password_hash('123456', PASSWORD_DEFAULT)
INSERT INTO usuarios (nombre, email, password_hash, rol, telefono, especialidad) VALUES
('Dr. Santiago Méndez', 'santiago.mendez@vetanimal.com', '$2y$10$/MHdPwMFL5PnQpCWAIFWZu314oPmUUijB3Yigxp2MtAKSaJENH3Da', 'veterinario', '11-4000-1000', 'Medicina General'),
('Dra. Martina Paz', 'martina.paz@vetanimal.com', '$2y$10$/MHdPwMFL5PnQpCWAIFWZu314oPmUUijB3Yigxp2MtAKSaJENH3Da', 'veterinario', '11-4000-1001', 'Emergencias'),
('Agustina Gómez', 'agustina.gomez@example.com', '$2y$10$/MHdPwMFL5PnQpCWAIFWZu314oPmUUijB3Yigxp2MtAKSaJENH3Da', 'cliente', '11-5555-2222', NULL);

INSERT INTO mascotas (usuario_id, nombre, especie, raza, edad, peso, estado_salud, alergias, condiciones_cronicas) VALUES
(3, 'Bella', 'Perro', 'Golden Retriever', 4, 28.5, 'Estable', 'Picaduras de Pulga, Polen', 'Ninguna diagnosticada a la fecha.');

INSERT INTO servicios (nombre, descripcion, duracion_min, precio, icono) VALUES
('Chequeo General', 'Consulta estándar de 30 minutos', 30, 25.00, 'heart'),
('Cirugía Avanzada', 'Salas quirúrgicas de última generación', 90, 250.00, 'scalpel'),
('Diagnósticos', 'Laboratorio propio e imágenes digitales', 45, 60.00, 'flask'),
('Vacunación', 'Aplicación de vacunas y refuerzos', 20, 18.00, 'syringe');

INSERT INTO consultas (mascota_id, veterinario_id, fecha, tipo, titulo, descripcion) VALUES
(1, 1, '2024-05-15', 'CONTROL', 'Control Anual Preventivo', 'Paciente presenta excelente condición física. Se realizó examen físico completo, limpieza dental superficial y actualización de peso.'),
(1, 2, '2024-03-02', 'EMERGENCIA', 'Urgencia: Dermatitis Aguda', 'Reacción alérgica en zona abdominal. Se administró antihistamínico vía oral y se recetó pomada calmante por 7 días.'),
(1, 1, '2023-11-20', 'VACUNA', 'Vacunación Cuádruple', 'Aplicación de refuerzo anual sin complicaciones posteriores.');

INSERT INTO vacunas (mascota_id, nombre, fecha_aplicacion, fecha_refuerzo, estado) VALUES
(1, 'Antirrábica', '2025-05-15', '2025-05-15', 'AL_DIA'),
(1, 'DHPP (Quíntuple)', '2024-11-20', '2024-11-20', 'AL_DIA'),
(1, 'Giardia', '2023-04-10', '2024-04-10', 'VENCIDA');

INSERT INTO estudios (mascota_id, nombre, tipo, fecha) VALUES
(1, 'Hemograma Completo', 'Laboratorio', '2024-05-15'),
(1, 'Radiografía Cadera', 'Imágenes', '2024-01-05'),
(1, 'Ecocardiograma', 'Cardiología', '2023-11-20');

INSERT INTO productos (nombre, categoria, etiqueta, descripcion, precio, requiere_receta, stock) VALUES
('Preventivo de Dirofilaria Canina', 'Medicamentos', 'RECETADO', 'Tableta masticable mensual para perros de 11 a 23 kg. Requiere receta válida.', 45.00, 1, 40),
('Champú Suave de Avena', 'Bienestar y Estética', 'BIENESTAR', 'Fórmula hipoalergénica para pieles sensibles. Libre de parabenos.', 22.50, 0, 60),
('Dieta de Soporte de Movilidad Avanzada', 'Nutrición y Alimento', 'ALIMENTO', 'Bolsa de 6.8 kg. Clínicamente probado para mejorar la salud articular en 21 días.', 78.00, 0, 25),
('Multivitamínico Diario Masticable', 'Bienestar y Estética', 'BIENESTAR', '60 bocaditos blandos. Vitaminas y minerales esenciales para perros adultos.', 34.99, 0, 55);
