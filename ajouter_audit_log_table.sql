-- Création de la table d'audit pour le suivi des actions
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    action VARCHAR(50) NOT NULL,
    table_cible VARCHAR(50) NOT NULL,
    element_id VARCHAR(50),
    details TEXT,
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Index pour accélérer les recherches sur utilisateur_id et date_action
CREATE INDEX idx_audit_utilisateur_id ON audit_log(utilisateur_id);
CREATE INDEX idx_audit_date_action ON audit_log(date_action);
