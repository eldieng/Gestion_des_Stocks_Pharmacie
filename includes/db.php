<?php
// Fichier de connexion à la base de données avec PDO
// À inclure dans tous les fichiers nécessitant un accès à la base

$host = 'localhost'; // Adresse du serveur MySQL
$dbname = 'pharmacie_db'; // Nom de la base de données
$user = 'root'; // Nom d'utilisateur MySQL (à adapter)
$pass = ''; // Mot de passe MySQL (à adapter)

try {
    // Création de la connexion PDO (gestion des exceptions activée)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Affichage d'un message d'erreur en cas d'échec de connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
