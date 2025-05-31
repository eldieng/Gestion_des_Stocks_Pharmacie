-- Nettoyage de la base : suppression de l'ancien champ texte 'role' de la table utilisateurs
ALTER TABLE utilisateurs DROP COLUMN role;
-- (Optionnel) Vérification que toutes les lignes ont bien un role_id :
-- UPDATE utilisateurs SET role_id = (SELECT id FROM roles WHERE nom = 'assistant') WHERE role_id IS NULL;
