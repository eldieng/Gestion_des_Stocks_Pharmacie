<?php
session_start();
require_once __DIR__.'/db.php';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT u.nom, r.nom as role FROM utilisateurs u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
    $stmt->execute([$user_id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_nom = $info['nom'] ?? '';
    $user_role = $info['role'] ?? '';
    $_SESSION['user_nom'] = $user_nom;
    $_SESSION['user_role'] = $user_role;
}
?>
