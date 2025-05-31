<?php
// Script pour créer un utilisateur administrateur dans la base de données
// À exécuter UNE SEULE FOIS pour initialiser le système
require_once 'includes/db.php';

// Définir les informations de l'administrateur
$nom = 'Admin';
$prenom = 'Principal';
$email = 'admin@pharmacie.sn';
$mot_de_passe = 'admin123'; // À changer après la première connexion
$role = 'pharmacien';

// Hacher le mot de passe pour la sécurité
$mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

try {
    // Préparer la requête d'insertion
    $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$nom, $prenom, $email, $mot_de_passe_hash, $role]);
    echo "Utilisateur administrateur créé avec succès !<br>Login : $email<br>Mot de passe : $mot_de_passe";
} catch (PDOException $e) {
    echo "Erreur lors de la création de l'utilisateur : " . $e->getMessage();
}
// Après exécution, SUPPRIME ce fichier pour la sécurité !
?>
