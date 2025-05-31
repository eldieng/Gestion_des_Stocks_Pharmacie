<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');
    if ($handle) {
        $header = fgetcsv($handle, 1000, ',');
        // Vérifier l'en-tête
        $expected = ['nom','categorie','laboratoire','quantite','date_peremption','prix_achat','prix_vente'];
        $header = array_map('trim', $header);
        if (array_map('strtolower', $header) === $expected) {
            $rowCount = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) !== 7) continue;
                // Nettoyage
                list($nom, $categorie, $laboratoire, $quantite, $date_peremption, $prix_achat, $prix_vente) = array_map('trim', $data);
                $date_peremption = $date_peremption ?: null;
                $stmt = $pdo->prepare('INSERT INTO medicaments (nom, categorie, laboratoire, quantite, date_peremption, prix_achat, prix_vente) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$nom, $categorie, $laboratoire, (int)$quantite, $date_peremption, (float)$prix_achat, (float)$prix_vente]);
                $rowCount++;
            }
            $message = "✅ $rowCount médicament(s)/matériel(s) importé(s) avec succès.";
        } else {
            $message = '⛔ Format de fichier invalide. Vérifiez l\'ordre et les noms des colonnes.';
        }
        fclose($handle);
    } else {
        $message = '⛔ Impossible de lire le fichier.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Importer des médicaments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Importer des médicaments ou matériels</h2>
    <a href="liste.php" class="btn btn-secondary mb-3">← Retour à la liste</a>
    <div class="card p-4">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Fichier CSV à importer</label>
                <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                <div class="form-text">Colonnes attendues : nom, categorie, laboratoire, quantite, date_peremption, prix_achat, prix_vente</div>
            </div>
            <button type="submit" class="btn btn-success">Importer</button>
            <a href="modele_import_medicaments.csv" class="btn btn-link">Télécharger un modèle CSV</a>
        </form>
        <?php if ($message): ?>
            <div class="alert <?= strpos($message, '✅')!==false ? 'alert-success' : 'alert-danger' ?> mt-3"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
