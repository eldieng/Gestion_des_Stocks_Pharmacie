<?php
// Page d'ajout d'un nouveau médicament
// Accessible uniquement aux utilisateurs connectés

session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données du formulaire
    $nom = trim($_POST['nom'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $laboratoire = trim($_POST['laboratoire'] ?? '');
    $quantite = (int)($_POST['quantite'] ?? 0);
    $date_peremption = trim($_POST['date_peremption'] ?? '');
    $prix_achat = (float)($_POST['prix_achat'] ?? 0);
    $prix_vente = (float)($_POST['prix_vente'] ?? 0);

    // Vérification des champs obligatoires
    if ($nom && $quantite >= 0 && $prix_achat >= 0 && $prix_vente >= 0) {
        try {
            // Préparation de la requête d'insertion
            $stmt = $pdo->prepare('INSERT INTO medicaments (nom, categorie, laboratoire, quantite, date_peremption, prix_achat, prix_vente) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nom, $categorie, $laboratoire, $quantite, $date_peremption ?: null, $prix_achat, $prix_vente]);
            $message = "Médicament ajouté avec succès !";
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires (nom, quantité, prix).";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un médicament - Fawsayni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(120deg, #e0f7fa 0%, #ffffff 100%); min-height: 100vh; }
        .logo-pharma-text { font-family: 'Segoe UI', 'Arial', sans-serif; font-size: 2em; font-weight: bold; background: linear-gradient(90deg, #ff9800 10%, #00bcd4 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 2px; margin-bottom: 10px; }
        .header-bar { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.06); padding: 18px 18px 8px 18px; margin-bottom: 22px; text-align: center; }
        .form-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 10px #e0e0e0; padding: 32px 22px 18px 22px; max-width: 520px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container py-3">
        <div class="header-bar mb-3">
            <div class="logo-pharma-text mb-2">Fawsayni</div>
            <a href="liste.php" class="btn btn-outline-primary btn-sm">&larr; Retour à la liste</a>
        </div>
        <div class="form-card">
            <h3 class="mb-3">Ajouter un médicament</h3>
            <div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;"></div>
            <?php if ($message): ?>
                <script>
                window.addEventListener('DOMContentLoaded', function() {
                    const toast = document.createElement('div');
                    toast.className = 'toast align-items-center <?php echo (strpos($message, "succès")!==false)?'bg-success text-white':'bg-danger text-white'; ?>';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.innerHTML = `<div class='d-flex'>
                        <div class='toast-body'><?php echo (strpos($message, "succès")!==false)?'✅':'⛔'; ?> <?php echo htmlspecialchars($message); ?></div>
                        <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Fermer'></button>
                    </div>`;
                    document.getElementById('toastContainer').appendChild(toast);
                    const bsToast = new bootstrap.Toast(toast, {delay: 3500});
                    bsToast.show();
                    toast.addEventListener('hidden.bs.toast', () => toast.remove());
                });
                </script>
            <?php endif; ?>
            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label">Nom* :</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catégorie :</label>
                    <input type="text" name="categorie" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Laboratoire :</label>
                    <input type="text" name="laboratoire" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantité* :</label>
                    <input type="number" name="quantite" min="0" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de péremption :</label>
                    <input type="date" name="date_peremption" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Prix d'achat* (FCFA):</label>
                    <input type="number" name="prix_achat" min="0" step="0.01" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prix de vente* (FCFA):</label>
                    <input type="number" name="prix_vente" min="0" step="0.01" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Ajouter</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
