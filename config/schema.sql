CREATE DATABASE IF NOT EXISTS bibliotheque_virtuel;

USE bibliotheque_virtuel;

CREATE TABLE auteur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE livre (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_publication DATE,
    fichier VARCHAR(255)
);

CREATE TABLE livre_auteur (
    livre_id INT NOT NULL,
    auteur_id INT NOT NULL,

    PRIMARY KEY (livre_id, auteur_id),

    FOREIGN KEY (livre_id)
        REFERENCES livre(id)
        ON DELETE CASCADE,

    FOREIGN KEY (auteur_id)
        REFERENCES auteur(id)
        ON DELETE CASCADE
);

CREATE TABLE utilisateur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE lecture (
    id INT PRIMARY KEY AUTO_INCREMENT,
    livre_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_lecture DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (livre_id)
        REFERENCES livre(id)
        ON DELETE CASCADE,

    FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur(id)
        ON DELETE CASCADE
);