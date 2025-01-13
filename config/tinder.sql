CREATE DATABASE tinder;
USE tinder;

-- Tabla users actualizada
CREATE TABLE users (
    id_autoinc INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    apellidos VARCHAR(255),
    alias VARCHAR(100),
    ubicacion VARCHAR(255),
    sexo ENUM('masculino', 'femenino', 'no binario'),
    orientacion_sexual ENUM('heterosexual', 'homosexual', 'bisexual'),
    password VARCHAR(255),
    mail VARCHAR(255) UNIQUE,
    data_Int DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla fotos_Usuarios actualizada
CREATE TABLE fotos_Usuarios (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    path VARCHAR(255) NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP, -- Fecha en la que se sube la foto
    FOREIGN KEY (id_usuario) REFERENCES users(id_autoinc) ON DELETE CASCADE -- Relación con la tabla users
);

-- Tabla solicitudes
CREATE TABLE solicitudes (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario1 INT NOT NULL, -- Usuario que envía la solicitud
    id_usuario2 INT NOT NULL, -- Usuario que recibe la solicitud
    estado ENUM('pendiente', 'aceptado', 'rechazado') DEFAULT 'pendiente',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_Like DATE null,
    FOREIGN KEY (id_usuario1) REFERENCES users(id_autoinc) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario2) REFERENCES users(id_autoinc) ON DELETE CASCADE,
    UNIQUE (id_usuario1, id_usuario2) 
);

-- Tabla mensajes
CREATE TABLE mensajes (
    id_Mensajes INT AUTO_INCREMENT PRIMARY KEY,
    id_origen INT NOT NULL,
    id_destino INT NOT NULL,
    mensaje TEXT,
    fechaMensaje DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_origen) REFERENCES users(id_autoinc) ON DELETE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES users(id_autoinc) ON DELETE CASCADE
);