<?php
// Suppression d'un médicament
// Accessible uniquement aux utilisateurs connectés

session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Récupère l'ID du médicament à supprimer
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('ID de médicament invalide.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirmer']) && $_POST['confirmer'] === 'oui') {
        // Suppression réelle
        try {
            $stmt = $pdo->prepare('DELETE FROM medicaments WHERE id = ?');
            $stmt->execute([$id]);
            header('Location: liste.php'); // Retour à la liste après suppression
            exit;
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } else {
        // Annulation
        header('Location: liste.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer un médicament</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Suppression d'un médicament</h1>
    <a href="liste.php">Annuler et revenir à la liste</a>
    <?php if ($message): ?>
        <div style="color:red;"> <?php echo htmlspecialchars($message); ?> </div>
    <?php else: ?>
        <form method="post" action="">
            <p style="color:red;">Confirmez-vous la suppression de ce médicament ? Cette action est irréversible.</p>
            <button type="submit" name="confirmer" value="oui">Oui, supprimer</button>
            <button type="submit" name="confirmer" value="non">Non, annuler</button>
        </form>
    <?php endif; ?>
    <!--
        Explications :
        - Cette page demande une confirmation avant de supprimer un médicament.
        - Si confirmé, la suppression est effectuée et l'utilisateur est redirigé vers la liste.
        - Sinon, retour à la liste sans suppression.
    -->
</body>
</html>
