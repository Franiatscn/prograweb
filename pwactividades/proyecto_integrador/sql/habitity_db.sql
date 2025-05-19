CREATE DATABASE habitity_db;
USE habitity_db;


CREATE TABLE estatus_usuario (
  id_estatus INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descripcion VARCHAR(40)
) ENGINE = InnoDB;

-- Insertar estatus de usuario
INSERT INTO estatus_usuario (descripcion) VALUES 
('Activo'),
('Inactivo'); 

CREATE TABLE rol (
  id_rol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(40) NOT NULL
) ENGINE = InnoDB;

-- Insertar roles
INSERT INTO rol (nombre) VALUES 
('Administrador'),
('Usuario');

CREATE TABLE categoria (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE = InnoDB;

INSERT INTO categoria (nombre) VALUES 
('Salud'),
('Estudio'),
('Trabajo'),
('Hogar'),
('Hobby'),
('Social');

CREATE TABLE frecuencia (
    id_frecuencia INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    periodo VARCHAR(30) NOT NULL
) ENGINE = InnoDB;

-- Frecuencias diarias
INSERT INTO frecuencia (periodo) VALUES
('día'),
('mes'),
('semana');

CREATE TABLE usuario (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellido_p VARCHAR(50) NOT NULL,
    apellido_m VARCHAR(50),
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_rol INT UNSIGNED NOT NULL,
    id_estatus INT UNSIGNED NOT NULL,
    fec_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fec_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol),
    FOREIGN KEY (id_estatus) REFERENCES estatus_usuario(id_estatus)
) ENGINE = InnoDB;


CREATE TABLE habito (
    id_habito INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_frecuencia INT UNSIGNED,
    id_categoria INT UNSIGNED,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(100),
    FOREIGN KEY (id_frecuencia) REFERENCES frecuencia(id_frecuencia),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
) ENGINE = InnoDB;

INSERT INTO habito(id_categoria,nombre,descripcion) VALUES
(1,'Dormir','Asegurarse de descansar lo suficiente'),
(1,'Caminar','Salir a caminar para activar el cuerpo'), 
(1,'Beber agua','Mantenerse hidratado'),
(1,'Comer saludable','Consumir alimentos nutritivos'),
(2,'Leer','Lectura diaria de un libro'),
(2,'Repasar conceptos','Revisar problemas o tareas'),
(2,'Practicar ejercicios','Resolver problemas o tareas'),
(2,'Tomar notas','Apuntar ideas clave'),
(3,'Revisar correos','Leer y responder correos pendientes'),
(3,'Evitar distracciones','No usar redes sociales o correo electrónico'),
(3,'Priorizar actividades','Hacer primero las actividades importantes'),
(3,'Planificar día','Organizar tareas antes de comenzar'),
(4,'Tender la cama', 'Dejar la cama lista al levantarse'),
(4,'Lavar platos', 'Lavar los utensilios después de comer'),
(4,'Regar plantas', 'Cuidar las plantas del hogar'),
(4,'Limpiar el baño','Ordenar los productos de baño'),
(5,'Dibujar', 'Expresar creatividad visual'),
(5,'Tocar instrumento', 'Practicar guitarra o piano'),
(5,'Cocinar', 'Intentar preparar algo nuevo'),
(5,'Armar rompecabezas', 'Resolver juegos mentales'),
(6,'Llamar a un amigo', 'Hablar con alguien cercano'),
(6,'Escribir mensaje', 'Enviar un saludo o agradecimiento'),
(6,'Pasar tiempo familiar', 'Compartir al menos 30 minutos'),
(6,'Escuchar activamente', 'Prestar atención sin interrumpir'),
(6,'Ayudar a alguien', 'Ofrecer apoyo o consejo');

CREATE TABLE meta (
    id_meta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_habito INT UNSIGNED NOT NULL,
    descripcion VARCHAR(100),
    objetivo VARCHAR(100),
    estado ENUM('pendiente', 'en proceso', 'completada') DEFAULT 'pendiente',
    frec_meta INT UNSIGNED,
    periodo ENUM('dia', 'semana', 'mes'),
    cumplida BOOLEAN DEFAULT FALSE,
    fec_inicio DATETIME,
    fec_fin DATETIME,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_habito) REFERENCES habito(id_habito)
) ENGINE = InnoDB;

CREATE TABLE registro_habito (
    id_registro INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_habito INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    id_meta INT UNSIGNED NULL, 
    id_frecuencia INT UNSIGNED NOT NULL,
    progreso INT UNSIGNED DEFAULT 0,
    objetivo INT UNSIGNED NOT NULL,
    fec_inicio DATETIME NOT NULL,
    fec_fin DATETIME,
    completado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_habito) REFERENCES habito(id_habito),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_meta) REFERENCES meta(id_meta),
    FOREIGN KEY (id_frecuencia) REFERENCES frecuencia(id_frecuencia)
) ENGINE = InnoDB;