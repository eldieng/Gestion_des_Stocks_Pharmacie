<?php
// Page d'historique des ventes avec filtres
// Accessible uniquement aux utilisateurs connectés

session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Récupération des critères de filtre
$medicament = trim($_GET['medicament'] ?? '');
$utilisateur = trim($_GET['utilisateur'] ?? '');
$date_debut = trim($_GET['date_debut'] ?? '');
$date_fin = trim($_GET['date_fin'] ?? '');

// Construction dynamique de la requête avec filtres
$sql = 'SELECT v.id, v.quantite, v.date_vente, m.nom AS nom_medicament, u.nom AS nom_utilisateur, u.prenom AS prenom_utilisateur FROM ventes v JOIN medicaments m ON v.id_medicament = m.id LEFT JOIN utilisateurs u ON v.utilisateur_id = u.id WHERE 1';
$params = [];
if ($medicament) {
    $sql .= ' AND m.nom LIKE ?';
    $params[] = "%$medicament%";
}
if ($utilisateur) {
    $sql .= ' AND (u.nom LIKE ? OR u.prenom LIKE ?)';
    $params[] = "%$utilisateur%";
    $params[] = "%$utilisateur%";
}
if ($date_debut) {
    $sql .= ' AND v.date_vente >= ?';
    $params[] = $date_debut;
}
if ($date_fin) {
    $sql .= ' AND v.date_vente <= ?';
    $params[] = $date_fin;
}
$sql .= ' ORDER BY v.date_vente DESC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors de la récupération de l\'historique : ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des ventes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-filtre { margin-bottom: 24px; background: #f8f9fa; padding: 18px; border-radius: 1rem; box-shadow:0 2px 8px #0001; }
        .badge-quantite { font-size: 1em; }
        .avatar-initials { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#e0e7ef;color:#37517e;font-weight:600;margin-right:7px; }
        .table-responsive { border-radius: 1rem; overflow: hidden; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Fawsayni</a>
            <div>
                <a href="enregistrer.php" class="btn btn-outline-primary btn-sm me-2">🛒 Enregistrer une vente</a>
                <a href="../dashboard.php" class="btn btn-outline-secondary btn-sm">🏠 Tableau de bord</a>
                <button id="exportExcel" class="btn btn-warning btn-sm ms-2">Exporter Excel</button>
            </div>
        </div>
    </nav>
    <div class="container mb-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end form-filtre">
                    <div class="col-md-3">
                        <label class="form-label">Médicament</label>
                        <input type="text" name="medicament" value="<?php echo htmlspecialchars($medicament); ?>" class="form-control" placeholder="Nom du médicament">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Utilisateur</label>
                        <input type="text" name="utilisateur" value="<?php echo htmlspecialchars($utilisateur); ?>" class="form-control" placeholder="Nom ou prénom">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Du</label>
                        <input type="date" name="date_debut" value="<?php echo htmlspecialchars($date_debut); ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Au</label>
                        <input type="date" name="date_fin" value="<?php echo htmlspecialchars($date_fin); ?>" class="form-control">
                    </div>
                    <div class="col-md-2 d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <a href="historique.php" class="btn btn-outline-secondary">Réinitialiser</a>
                    </div>
                </form>
                <input type="text" id="searchTable" class="form-control mt-2" placeholder="Recherche rapide dans le tableau...">
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle mb-0" id="ventesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Médicament</th>
                            <th>Quantité</th>
                            <th>Vendu par</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($ventes)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Aucune vente enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ventes as $vente): ?>
                            <tr>
                                <td><span class="badge bg-info-subtle text-dark"><?php echo date('d/m/Y', strtotime($vente['date_vente'])); ?></span></td>
                                <td><?php echo htmlspecialchars($vente['nom_medicament']); ?></td>
                                <td>
                                    <span class="badge badge-quantite <?php
                                        $q = (int)$vente['quantite'];
                                        echo ($q >= 20) ? 'bg-success' : (($q >= 10) ? 'bg-warning text-dark' : 'bg-danger');
                                    ?>"><?php echo $q; ?></span>
                                </td>
                                <td>
                                    <span class="avatar-initials">
                                        <?php
                                            $prenom = $vente['prenom_utilisateur'] ?? '';
                                            $nom = $vente['nom_utilisateur'] ?? '';
                                            $ini = strtoupper(mb_substr($prenom,0,1).mb_substr($nom,0,1));
                                            echo $ini;
                                        ?>
                                    </span>
                                    <?php echo htmlspecialchars(trim($prenom.' '.$nom)); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
    // Pagination simple
    const rowsPerPage = 20;
    let currentPage = 1;
    const tableBody = document.querySelector('#ventesTable tbody');
    const allRows = Array.from(tableBody.querySelectorAll('tr'));
    function showPage(page) {
        allRows.forEach((row, idx) => {
            row.style.display = (idx >= (page-1)*rowsPerPage && idx < page*rowsPerPage) ? '' : 'none';
        });
        document.getElementById('paginationInfo').textContent = `Page ${page} / ${Math.max(1, Math.ceil(allRows.length/rowsPerPage))}`;
    }
    function createPagination() {
        if (allRows.length <= rowsPerPage) return;
        const pagDiv = document.createElement('div');
        pagDiv.className = 'd-flex justify-content-center align-items-center gap-2 my-2';
        pagDiv.innerHTML = `<button class='btn btn-outline-secondary btn-sm' id='prevPage'>&lt;</button>
            <span id='paginationInfo'></span>
            <button class='btn btn-outline-secondary btn-sm' id='nextPage'>&gt;</button>`;
        tableBody.parentNode.parentNode.appendChild(pagDiv);
        document.getElementById('prevPage').onclick = () => { if (currentPage > 1) { currentPage--; showPage(currentPage); } };
        document.getElementById('nextPage').onclick = () => { if (currentPage < Math.ceil(allRows.length/rowsPerPage)) { currentPage++; showPage(currentPage); } };
        showPage(1);
    }
    createPagination();
    // Recherche rapide JS sur le tableau
    document.getElementById('searchTable').addEventListener('input', function() {
        const val = this.value.toLowerCase();
        allRows.forEach(row => {
            row.style.display = Array.from(row.cells).some(cell => cell.textContent.toLowerCase().includes(val)) ? '' : 'none';
        });
        showPage(1);
    });
    // Export Excel
    document.getElementById('exportExcel').onclick = function() {
        const rows = Array.from(document.querySelectorAll('#ventesTable tr'));
        const data = rows.map(r => Array.from(r.children).map(td => td.innerText));
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Ventes');
        XLSX.writeFile(wb, 'ventes_fawsayni.xlsx');
    };
    // Feedback visuel de chargement (spinner)
    window.addEventListener('DOMContentLoaded', () => {
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border text-info position-fixed top-50 start-50 translate-middle';
        spinner.style.zIndex = 9999;
        spinner.id = 'globalSpinner';
        document.body.appendChild(spinner);
        setTimeout(() => { spinner.remove(); }, 600);
    });
    </script>
</body>
</html>
