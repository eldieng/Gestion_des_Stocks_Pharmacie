<?php
// Page d'affichage de l'historique des actions (audit log)
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

// Vérifier que l'utilisateur a la permission de voir l'audit (à adapter si besoin)
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Récupérer l'audit log (limité aux 200 dernières actions)
$stmt = $pdo->query('SELECT a.*, u.nom AS user_nom, u.prenom AS user_prenom FROM audit_log a LEFT JOIN utilisateurs u ON a.user_id = u.id ORDER BY a.date_action DESC LIMIT 200');
$audit = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des actions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Historique des actions (Audit Log)</h2>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Cible</th>
                    <th>ID cible</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($audit as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['date_action']) ?></td>
                    <td><?= htmlspecialchars($row['user_prenom'].' '.$row['user_nom']) ?></td>
                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['action']) ?></span></td>
                    <td><?= htmlspecialchars($row['cible']) ?></td>
                    <td><?= htmlspecialchars($row['cible_id']) ?></td>
                    <td><code style="font-size:0.95em;white-space:pre-wrap;word-break:break-all;max-width:350px;display:inline-block;">
                        <?= htmlspecialchars($row['details']) ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="dashboard.php" class="btn btn-outline-primary mt-3">Retour au tableau de bord</a>
</div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>
