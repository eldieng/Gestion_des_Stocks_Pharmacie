-- Script SQL pour la base de données du système de gestion de pharmacie
-- À exécuter dans phpMyAdmin ou MySQL Workbench

CREATE DATABASE IF NOT EXISTS pharmacie_db;
USE pharmacie_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('pharmacien','assistant') DEFAULT 'assistant',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des médicaments
CREATE TABLE IF NOT EXISTS medicaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    categorie VARCHAR(100),
    laboratoire VARCHAR(100),
    quantite INT DEFAULT 0,
    date_peremption DATE,
    prix_achat DECIMAL(10,2),
    prix_vente DECIMAL(10,2),
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des ventes
CREATE TABLE IF NOT EXISTS ventes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_medicament INT NOT NULL,
    quantite INT NOT NULL,
    date_vente DATETIME DEFAULT CURRENT_TIMESTAMP,
    utilisateur_id INT,
    FOREIGN KEY (id_medicament) REFERENCES medicaments(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Table des achats (entrées de stock)
CREATE TABLE IF NOT EXISTS achats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_medicament INT NOT NULL,
    quantite INT NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    utilisateur_id INT,
    FOREIGN KEY (id_medicament) REFERENCES medicaments(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);
