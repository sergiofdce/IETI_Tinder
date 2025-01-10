-- Eliminar la base de datos si ya existe (buena práctica para evitar errores)
DROP DATABASE IF EXISTS tinder;

-- Crear la base de datos
CREATE DATABASE tinder;
USE tinder;

-- Tabla users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    apellidos VARCHAR(255),
    alias VARCHAR(100),
    ubicacion VARCHAR(255),
    sexo ENUM('masculino', 'femenino', 'no binario'),
    orientacion_sexual ENUM('heterosexual', 'homosexual', 'bisexual'),
    password VARCHAR(255),
    mail VARCHAR(255) UNIQUE,
    data_Int DATE
);

-- Tabla fotos_Usuarios
CREATE TABLE fotos_Usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,       
    id_usuario INT NOT NULL,                 
    path VARCHAR(255) NOT NULL,             
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP, -- Fecha en la que se sube la foto
    FOREIGN KEY (id_usuario) REFERENCES users(id) ON DELETE CASCADE -- Relación con la tabla users
);

-- Tabla solicitudes
CREATE TABLE solicitudes (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario1 INT NOT NULL, -- Usuario que envía la solicitud
    id_usuario2 INT NOT NULL, -- Usuario que recibe la solicitud
    estado ENUM('pendiente', 'aceptado', 'rechazado') DEFAULT 'pendiente',
    fecha_solicitud DATE,
    fecha_Like DATE null,
    FOREIGN KEY (id_usuario1) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario2) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (id_usuario1, id_usuario2) 
);

-- Tabla mensajes
CREATE TABLE mensajes (
    id_Mensajes INT AUTO_INCREMENT PRIMARY KEY,
    id_origen INT NOT NULL,
    id_destino INT NOT NULL,
    mensaje TEXT,
    fechaMensaje DATETIME,
    FOREIGN KEY (id_origen) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES users(id) ON DELETE CASCADE
);
