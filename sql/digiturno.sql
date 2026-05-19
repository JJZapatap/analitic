CREATE DATABASE IF NOT EXISTS digiturno CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE digiturno;

DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 username VARCHAR(50) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('administrador','asesor','pantalla') NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE services (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 prefix VARCHAR(5) NOT NULL,
 active TINYINT(1) DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tickets (
 id INT AUTO_INCREMENT PRIMARY KEY,
 service_id INT NOT NULL,
 advisor_id INT NULL,
 sequence INT NOT NULL,
 code VARCHAR(20) NOT NULL,
 status ENUM('espera','llamado','finalizado','ausente') DEFAULT 'espera',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 called_at DATETIME NULL,
 finished_at DATETIME NULL,
 FOREIGN KEY (service_id) REFERENCES services(id),
 FOREIGN KEY (advisor_id) REFERENCES users(id)
);

INSERT INTO users(name,username,password,role) VALUES
('Administrador','admin','$2y$12$mScJR071c16eMz4KeMiiheReozwGDFMt8ys7Wky8cz5t3ZITtSY2G','administrador'),
('Asesor 1','asesor1','$2y$12$mScJR071c16eMz4KeMiiheReozwGDFMt8ys7Wky8cz5t3ZITtSY2G','asesor'),
('Pantalla','tv1','$2y$12$mScJR071c16eMz4KeMiiheReozwGDFMt8ys7Wky8cz5t3ZITtSY2G','pantalla');

INSERT INTO services(name,prefix) VALUES ('Caja','C'),('Información','I'),('Créditos','CR');
