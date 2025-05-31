-- Script SQL pour gestion des rôles et permissions personnalisables

-- 1. Table des rôles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

-- 2. Table des permissions
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) UNIQUE NOT NULL,
    description TEXT
);

-- 3. Table de liaison rôles <-> permissions
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- 4. Modification de la table utilisateurs
ALTER TABLE utilisateurs
    ADD COLUMN role_id INT NULL,
    ADD CONSTRAINT fk_role_id FOREIGN KEY (role_id) REFERENCES roles(id);

-- 5. Migration des anciens rôles texte (optionnel, à adapter selon tes besoins)
-- UPDATE utilisateurs SET role_id = (SELECT id FROM roles WHERE nom = utilisateurs.role) WHERE role_id IS NULL;
-- ALTER TABLE utilisateurs DROP COLUMN role;

-- 6. Exemples d’insertion de rôles et permissions de base (optionnel)
INSERT INTO roles (nom, description) VALUES ('admin', 'Administrateur principal'), ('assistant', 'Assistant pharmacie');
INSERT INTO permissions (nom, description) VALUES
  ('voir_ventes', 'Peut voir les ventes'),
  ('ajouter_utilisateur', 'Peut ajouter des utilisateurs'),
  ('supprimer_utilisateur', 'Peut supprimer des utilisateurs'),
  ('modifier_medicament', 'Peut modifier les médicaments'),
  ('supprimer_medicament', 'Peut supprimer les médicaments'),
  ('voir_rapports', 'Peut voir les rapports');

-- Exemple d’association rôle <-> permissions (admin = tout, assistant = restreint)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.nom = 'admin';
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.nom = 'assistant' AND p.nom IN ('voir_ventes', 'modifier_medicament');

-- Ce script est à exécuter dans phpMyAdmin ou via un outil SQL pour mettre à jour la base.
