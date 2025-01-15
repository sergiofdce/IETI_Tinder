-- Eliminar la base de datos si ya existe (buena práctica para evitar errores)
DROP DATABASE IF EXISTS tinder;

-- Crear la base de datos
CREATE DATABASE tinder;
USE tinder;

-- Tabla usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    alias VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    location VARCHAR(75) NOT NULL,  -- Nueva columna location como VARCHAR(75)
    genre ENUM('home', 'dona', 'no binari'),
    sexual_preference ENUM('heterosexual', 'homosexual', 'bisexual') NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at DATE NOT NULL
);



-- Tabla fotos_Usuarios
CREATE TABLE user_images (
    id INT AUTO_INCREMENT PRIMARY KEY,       
    user_id INT NOT NULL,                 
    path VARCHAR(255) NOT NULL,             
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- Tabla solicitudes
CREATE TABLE requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    request_date DATE,
    like_date DATE NULL,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (sender_id, receiver_id)
);


-- Tabla mensajes
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT,
    sent_at DATETIME,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

