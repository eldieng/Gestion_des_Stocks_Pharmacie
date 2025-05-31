-- Ajout des permissions nécessaires à la gestion des utilisateurs
INSERT IGNORE INTO permissions (nom) VALUES
  ('voir_utilisateurs'),
  ('ajouter_utilisateur'),
  ('modifier_utilisateur'),
  ('supprimer_utilisateur');

-- Attribution de toutes ces permissions au rôle admin (id = 1)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, p.id FROM permissions p WHERE p.nom IN ('voir_utilisateurs','ajouter_utilisateur','modifier_utilisateur','supprimer_utilisateur');
