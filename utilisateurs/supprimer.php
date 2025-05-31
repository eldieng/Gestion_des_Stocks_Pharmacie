<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/audit.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: liste.php?message=' . urlencode('⛔ ID utilisateur invalide.'));
    exit;
}
// Empêcher la suppression de soi-même
if ($id == $_SESSION['user_id']) {
    header('Location: liste.php?message=' . urlencode('⛔ Vous ne pouvez pas supprimer votre propre compte.'));
    exit;
}
// Vérification existence utilisateur
$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    header('Location: liste.php?message=' . urlencode('⛔ Utilisateur introuvable.'));
    exit;
}
// Confirmation via GET ?confirm=1
if (!isset($_GET['confirm'])) {
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Confirmer suppression</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
    <div class="container py-5"><div class="card p-4 shadow-sm"><h3 class="mb-3">Supprimer l’utilisateur</h3><p>Voulez-vous vraiment supprimer l’utilisateur <strong>' . htmlspecialchars($user['nom'] . ' ' . $user['prenom']) . '</strong> ?</p>
    <a href="supprimer.php?id=' . $id . '&confirm=1" class="btn btn-danger">Oui, supprimer</a> <a href="liste.php" class="btn btn-secondary ms-2">Annuler</a></div></div></body></html>';
    exit;
}
// Suppression
try {
    $stmt = $pdo->prepare('DELETE FROM utilisateurs WHERE id = ?');
    $stmt->execute([$id]);
    log_action($pdo, $_SESSION['user_id'], 'suppression', 'utilisateurs', $id, json_encode($user));
    header('Location: liste.php?message=' . urlencode('✅ Utilisateur supprimé avec succès.'));
    exit;
} catch (PDOException $e) {
    header('Location: liste.php?message=' . urlencode('⛔ Erreur lors de la suppression.'));
    exit;
}
