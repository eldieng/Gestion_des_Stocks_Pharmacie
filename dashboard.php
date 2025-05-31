<?php
// Tableau de bord avec statistiques
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Rafraîchir le nom et le rôle depuis la base à chaque chargement du dashboard
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT u.nom, r.nom as role FROM utilisateurs u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
$stmt->execute([$user_id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);
$user_nom = $info['nom'] ?? '';
$user_role = $info['role'] ?? '';
// Mettre à jour la session pour les autres pages si besoin
$_SESSION['user_nom'] = $user_nom;
$_SESSION['user_role'] = $user_role;

// Statistiques générales
// Préparer les données pour les graphiques (ventes/achats du mois par jour)
$ventes_jours_data = [];
$achats_jours_data = [];
try {
    // Nombre total de médicaments
    $total_medicaments = $pdo->query('SELECT COUNT(*) FROM medicaments')->fetchColumn();
    // Stock total
    $stock_total = $pdo->query('SELECT SUM(quantite) FROM medicaments')->fetchColumn();
    // Ventes du jour
    $ventes_jour = $pdo->prepare('SELECT SUM(quantite) FROM ventes WHERE DATE(date_vente) = CURDATE()');
    $ventes_jour->execute();
    $ventes_jour = $ventes_jour->fetchColumn() ?: 0;
    // Ventes du mois
    $ventes_mois = $pdo->prepare('SELECT SUM(quantite) FROM ventes WHERE YEAR(date_vente)=YEAR(CURDATE()) AND MONTH(date_vente)=MONTH(CURDATE())');
    $ventes_mois->execute();
    $ventes_mois = $ventes_mois->fetchColumn() ?: 0;
    // Achats du mois
    $achats_mois = $pdo->prepare('SELECT SUM(quantite) FROM achats WHERE YEAR(date_achat)=YEAR(CURDATE()) AND MONTH(date_achat)=MONTH(CURDATE())');
    $achats_mois->execute();
    $achats_mois = $achats_mois->fetchColumn() ?: 0;
    // Médicaments en stock faible
    $stock_faible = $pdo->query('SELECT COUNT(*) FROM medicaments WHERE quantite <= 5')->fetchColumn();
    // Médicaments périmés
    $perimes = $pdo->prepare('SELECT COUNT(*) FROM medicaments WHERE date_peremption IS NOT NULL AND date_peremption < CURDATE()');
    $perimes->execute();
    $perimes = $perimes->fetchColumn();

    // Ventes du mois par jour
    $ventes_jours_data = $pdo->query('SELECT DATE(date_vente) as jour, SUM(quantite) as total FROM ventes WHERE YEAR(date_vente)=YEAR(CURDATE()) AND MONTH(date_vente)=MONTH(CURDATE()) GROUP BY jour ORDER BY jour ASC')->fetchAll(PDO::FETCH_ASSOC);
    // Achats du mois par jour
    $achats_jours_data = $pdo->query('SELECT DATE(date_achat) as jour, SUM(quantite) as total FROM achats WHERE YEAR(date_achat)=YEAR(CURDATE()) AND MONTH(date_achat)=MONTH(CURDATE()) GROUP BY jour ORDER BY jour ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors du calcul des statistiques : ' . $e->getMessage());
}
// Si vous souhaitez afficher des messages de debug, faites-le après tout le code PHP qui gère les headers.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Fawsayni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(120deg, #e0f7fa 0%, #ffffff 100%); min-height: 100vh; }
        .logo-pharma-text { font-family: 'Segoe UI', 'Arial', sans-serif; font-size: 2em; font-weight: bold; background: linear-gradient(90deg, #ff9800 10%, #00bcd4 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 2px; margin-bottom: 10px; }
        .dashboard-header { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.06); padding: 24px 24px 10px 24px; margin-bottom: 24px; text-align: center; }
        .stat-card { border-radius: 18px; box-shadow: 0 4px 18px #e0e0e0; padding: 25px 18px; margin-bottom: 20px; min-width: 170px; text-align: center; background: #fff; }
        .stat-title { color:#888; font-size:1em; margin-bottom:4px; }
        .stat-value { font-size:2em; font-weight:bold; }
        .stat-icon { font-size:2em; margin-bottom: 6px; }
        .badge-alert { background: #e53935; color:#fff; font-size:0.98em; border-radius:12px; padding:3px 10px; margin-left:6px; }
        .quick-link-card { background: #f5f5f5; border-radius: 14px; box-shadow: 0 2px 8px #e0e0e0; padding: 22px 12px; text-align: center; transition: box-shadow 0.18s; }
        .quick-link-card:hover { box-shadow: 0 6px 18px #b2ebf2; }
        .quick-link-icon { font-size:2em; margin-bottom: 8px; color: #00bcd4; }
        .logout-link { color: #e53935; font-weight: 500; margin-left: 12px; }
        @media (max-width: 600px) { .dashboard-header { padding: 16px 4px 8px 4px; } }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <div class="logo-pharma-text mb-2">Fawsayni</div>
                <h2 class="fw-bold">Bienvenue, <?php echo htmlspecialchars($user_nom); ?> !</h2>
                <div class="mb-2 text-muted">Rôle : <?php echo htmlspecialchars($user_role); ?></div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>
            </div>
        </div>
        <!-- Accès rapide (icônes et intitulés, tout en haut) -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="medicaments/liste.php" class="card quick-link-card text-decoration-none flex-fill text-center shadow-sm p-3" style="max-width:200px;min-width:140px;">
                        <div class="mb-2" style="font-size:2.2em;">💊</div>
                        <div class="fw-bold">Gestion des médicaments</div>
                    </a>
                    <a href="ventes/enregistrer.php" class="card quick-link-card text-decoration-none flex-fill text-center shadow-sm p-3" style="max-width:200px;min-width:140px;">
                        <div class="mb-2" style="font-size:2.2em;">🛒</div>
                        <div class="fw-bold">Enregistrer une vente</div>
                    </a>
                    <a href="ventes/historique.php" class="card quick-link-card text-decoration-none flex-fill text-center shadow-sm p-3" style="max-width:200px;min-width:140px;">
                        <div class="mb-2" style="font-size:2.2em;">📈</div>
                        <div class="fw-bold">Historique des ventes</div>
                    </a>
                    <a href="achats/enregistrer.php" class="card quick-link-card text-decoration-none flex-fill text-center shadow-sm p-3" style="max-width:200px;min-width:140px;">
                        <div class="mb-2" style="font-size:2.2em;">🛍️</div>
                        <div class="fw-bold">Enregistrer un achat</div>
                    </a>
                    <a href="achats/historique.php" class="card quick-link-card text-decoration-none flex-fill text-center shadow-sm p-3" style="max-width:200px;min-width:140px;">
                        <div class="mb-2" style="font-size:2.2em;">📊</div>
                        <div class="fw-bold">Historique des achats</div>
                    </a>
                    <a href="utilisateurs/liste.php" class="card quick-link-card text-decoration-none flex-fill text-center shadow-sm p-3" style="max-width:200px;min-width:140px;">
                        <div class="mb-2" style="font-size:2.2em;">👥</div>
                        <div class="fw-bold">Gestion des utilisateurs</div>
                    </a>
                </div>
            </div>
        </div>
        <!-- Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">💊</div>
                    <div class="stat-title">Médicaments référencés</div>
                    <div class="stat-value"><?php echo (int)$total_medicaments; ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-title">Stock total</div>
                    <div class="stat-value"><?php echo (int)$stock_total; ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-title">Ventes aujourd'hui</div>
                    <div class="stat-value"><?php echo (int)$ventes_jour; ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-title">Ventes ce mois</div>
                    <div class="stat-value"><?php echo (int)$ventes_mois; ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">🛍️</div>
                    <div class="stat-title">Achats ce mois</div>
                    <div class="stat-value"><?php echo (int)$achats_mois; ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-title">Stock faible (&le;5)</div>
                    <div class="stat-value">
                        <?php echo (int)$stock_faible; ?>
                        <?php if ($stock_faible > 0): ?><span class="badge-alert">Alerte</span><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-title">Médicaments périmés</div>
                    <div class="stat-value">
                        <?php echo (int)$perimes; ?>
                        <?php if ($perimes > 0): ?><span class="badge-alert">Alerte</span><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Section graphiques -->
    <div class="container mb-5">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="card p-3 mb-3 shadow-sm">
                    <h6 class="mb-2 text-center" style="color:#00bcd4; font-weight:bold;">Ventes du mois (par jour)</h6>
                    <canvas id="chartVentesMois" height="180"></canvas>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card p-3 mb-3 shadow-sm">
                    <h6 class="mb-2 text-center" style="color:#ff9800; font-weight:bold;">Achats du mois (par jour)</h6>
                    <canvas id="chartAchatsMois" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    // Préparer les données PHP -> JS pour les graphiques
    window.dashboardData = {
        ventes_labels: <?php echo json_encode(array_map(function($d){return $d['jour'];}, $ventes_jours_data??[])); ?>,
        ventes_data: <?php echo json_encode(array_map(function($d){return (int)$d['total'];}, $ventes_jours_data??[])); ?>,
        achats_labels: <?php echo json_encode(array_map(function($d){return $d['jour'];}, $achats_jours_data??[])); ?>,
        achats_data: <?php echo json_encode(array_map(function($d){return (int)$d['total'];}, $achats_jours_data??[])); ?>
    };
    </script>
    <script src="assets/dashboard_charts.js"></script>
</body>
</html>
