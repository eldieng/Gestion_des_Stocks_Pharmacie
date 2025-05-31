<?php
// Page de liste des médicaments avec alertes et recherche
// Affiche tous les médicaments présents en base, possibilité de filtrer

session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$seuil_stock_faible = 5;
$date_auj = date('Y-m-d');

// Récupération des critères de recherche
$recherche = trim($_GET['recherche'] ?? '');
$categorie = trim($_GET['categorie'] ?? '');
$laboratoire = trim($_GET['laboratoire'] ?? '');

// Construction dynamique de la requête avec filtres
$sql = 'SELECT * FROM medicaments WHERE 1';
$params = [];
if ($recherche) {
    $sql .= ' AND nom LIKE ?';
    $params[] = "%$recherche%";
}
if ($categorie) {
    $sql .= ' AND categorie LIKE ?';
    $params[] = "%$categorie%";
}
if ($laboratoire) {
    $sql .= ' AND laboratoire LIKE ?';
    $params[] = "%$laboratoire%";
}
$sql .= ' ORDER BY nom ASC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors de la récupération des médicaments : ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des médicaments - Fawsayni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(120deg, #e0f7fa 0%, #ffffff 100%); min-height: 100vh; }
        .logo-pharma-text { font-family: 'Segoe UI', 'Arial', sans-serif; font-size: 2em; font-weight: bold; background: linear-gradient(90deg, #ff9800 10%, #00bcd4 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 2px; margin-bottom: 10px; }
        .header-bar { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.06); padding: 18px 18px 8px 18px; margin-bottom: 22px; text-align: center; }
        .filtre-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e0e0e0; padding: 18px 18px 8px 18px; margin-bottom: 18px; }
        .badge-stock { background:#ffe082; color:#b30000; font-weight:500; }
        .badge-peremption { background:#ef9a9a; color:#b30000; font-weight:500; }
        .table-responsive { box-shadow: 0 2px 10px #e0e0e0; border-radius: 14px; background: #fff; }
        .btn-action { border-radius: 18px; font-size: 0.98em; margin: 2px; }
        @media (max-width: 600px) { .header-bar { padding: 10px 4px 5px 4px; } }
    </style>
</head>
<body>
    <div class="container py-3">
        <?php
        // Notification stock faible/péremption
        $nb_stock_faible = 0;
        $nb_perimes = 0;
        foreach ($medicaments as $med) {
            if ((int)$med['quantite'] <= $seuil_stock_faible) $nb_stock_faible++;
            if (!empty($med['date_peremption']) && $med['date_peremption'] < $date_auj) $nb_perimes++;
        }
        if ($nb_stock_faible > 0 || $nb_perimes > 0): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" role="alert">
                <span class="fw-bold">Attention :</span>
                <?php if ($nb_stock_faible > 0): ?>
                    <span><?= $nb_stock_faible ?> médicament(s) en <span class="badge badge-stock">stock faible</span></span>
                <?php endif; ?>
                <?php if ($nb_perimes > 0): ?>
                    <span><?= $nb_perimes ?> médicament(s) <span class="badge badge-peremption">périmé(s)</span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="header-bar mb-3">
            <div class="logo-pharma-text mb-2">Fawsayni</div>
            <div>
                <a href="ajouter.php" class="btn btn-success btn-sm me-2">+ Ajouter un médicament</a>
                <a href="importer.php" class="btn btn-info btn-sm me-2">Importer</a>
                <a href="../dashboard.php" class="btn btn-outline-primary btn-sm">Retour au tableau de bord</a>
                <button id="exportExcel" class="btn btn-warning btn-sm ms-2">Exporter Excel</button>
                <button id="exportPDF" class="btn btn-danger btn-sm ms-2"><span class="me-1">&#128462;</span>Exporter PDF</button>
            </div>
        </div>
        <div class="filtre-card mb-4">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="recherche" value="<?php echo htmlspecialchars($recherche); ?>" class="form-control" placeholder="Nom du médicament">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Catégorie</label>
                    <input type="text" name="categorie" value="<?php echo htmlspecialchars($categorie); ?>" class="form-control" placeholder="Catégorie">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Laboratoire</label>
                    <input type="text" name="laboratoire" value="<?php echo htmlspecialchars($laboratoire); ?>" class="form-control" placeholder="Laboratoire">
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    <a href="liste.php" class="btn btn-outline-secondary w-100">Réinitialiser</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="sortable">Nom <span class="sort-indicator"></span></th>
                        <th class="sortable">Catégorie <span class="sort-indicator"></span></th>
                        <th class="sortable">Laboratoire <span class="sort-indicator"></span></th>
                        <th class="sortable">Quantité <span class="sort-indicator"></span></th>
                        <th class="sortable">Date de péremption <span class="sort-indicator"></span></th>
                        <th class="sortable">Prix achat <span class="sort-indicator"></span></th>
                        <th class="sortable">Prix vente <span class="sort-indicator"></span></th>
                        <th>Alertes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($medicaments)): ?>
                    <tr><td colspan="9" class="text-center">Aucun médicament trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($medicaments as $med):
                        $alerte = '';
                        $badges = '';
                        if ((int)$med['quantite'] <= $seuil_stock_faible) {
                            $alerte .= 'Stock faible. ';
                            $badges .= '<span class="badge badge-stock me-1">Stock faible</span>';
                        }
                        if (!empty($med['date_peremption']) && $med['date_peremption'] < $date_auj) {
                            $alerte .= 'Périmé !';
                            $badges .= '<span class="badge badge-peremption">Périmé</span>';
                        }
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($med['nom']); ?></td>
                            <td><?php echo htmlspecialchars($med['categorie']); ?></td>
                            <td><?php echo htmlspecialchars($med['laboratoire']); ?></td>
                            <td><?php echo (int)$med['quantite']; ?></td>
                            <td><?php echo htmlspecialchars($med['date_peremption']); ?></td>
                            <td><?php echo number_format($med['prix_achat'], 2, ',', ' '); ?> FCFA</td>
                            <td><?php echo number_format($med['prix_vente'], 2, ',', ' '); ?> FCFA</td>
                            <td><?php echo $badges; ?></td>
                            <td>
                                <a href="modifier.php?id=<?php echo $med['id']; ?>" class="btn btn-primary btn-sm btn-action">Modifier</a>
                                <a href="supprimer.php?id=<?php echo $med['id']; ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Supprimer ce médicament ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-muted" style="font-size:0.95em;">
            Liste des médicaments - Fawsayni
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/pharmacie/assets/js/export_libs.js"></script>
    <!-- Toast Bootstrap -->
    <style>
    th.sortable { cursor:pointer; user-select:none; }
    th.sortable .sort-indicator { font-size:0.9em; margin-left:2px; }
    </style>
    <div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
        <!-- Les toasts JS seront injectés ici -->
    </div>
    <script>
    // Pagination simple
    const rowsPerPage = 20;
    let currentPage = 1;
    const tableBody = document.querySelector('.table-responsive tbody');
    const allRows = Array.from(tableBody.querySelectorAll('tr'));
    function showPage(page) {
        allRows.forEach((row, idx) => {
            row.style.display = (idx >= (page-1)*rowsPerPage && idx < page*rowsPerPage) ? '' : 'none';
        });
        document.getElementById('paginationInfo').textContent = `Page ${page} / ${Math.ceil(allRows.length/rowsPerPage)}`;
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
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control mb-3';
    searchInput.placeholder = 'Recherche rapide (nom, catégorie, labo, etc)';
    const table = document.querySelector('.table-responsive');
    table.parentNode.insertBefore(searchInput, table);
    searchInput.addEventListener('input', function() {
        const val = this.value.toLowerCase();
        allRows.forEach(row => {
            row.style.display = Array.from(row.cells).some(cell => cell.textContent.toLowerCase().includes(val)) ? '' : 'none';
        });
        showPage(1);
    });
    // Toast utilitaire
    function showToast(message, type = 'success', position = 'bottom') {
        const icons = {
            success: '<span style="font-size:1.6em;vertical-align:middle;">✅</span>',
            danger: '<span style="font-size:1.6em;vertical-align:middle;">⛔</span>',
            warning: '<span style="font-size:1.6em;vertical-align:middle;">⚠️</span>',
            info: '<span style="font-size:1.6em;vertical-align:middle;">ℹ️</span>'
        };
        const bg = {
            success: 'bg-success text-white',
            danger: 'bg-danger text-white',
            warning: 'bg-warning text-dark',
            info: 'bg-info text-white'
        };
        let delay = 3500;
        if(type==='success') delay=2500;
        if(type==='danger') delay=5000;
        if(type==='info') delay=3500;
        if(type==='warning') delay=4000;
        const toast = document.createElement('div');
        toast.className = `toast align-items-center shadow-lg toast-animate ${bg[type]||bg.success}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `<div class='d-flex'>
            <div class='toast-body fw-semibold'>${icons[type]||''} <span style='margin-left:6px;'>${message}</span></div>
            <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Fermer'></button>
        </div>`;
        let container = document.getElementById('toastContainer');
        if(position==='top') {
            container.style.top = '1.5rem';
            container.style.bottom = '';
        } else {
            container.style.bottom = '1.5rem';
            container.style.top = '';
        }
        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, {delay});
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
    // Tri dynamique du tableau
    const table = document.querySelector('.table-responsive table');
    const ths = table.querySelectorAll('th.sortable');
    let sortCol = -1, sortAsc = true;
    ths.forEach((th, idx) => {
        th.onclick = function() {
            if (sortCol === idx) sortAsc = !sortAsc; else { sortCol = idx; sortAsc = true; }
            ths.forEach((t, i) => t.querySelector('.sort-indicator').textContent = (i===sortCol ? (sortAsc?'▲':'▼') : ''));
            const rows = Array.from(table.querySelectorAll('tbody tr')).filter(r => r.style.display !== 'none');
            rows.sort((a, b) => {
                let v1 = a.children[idx].innerText.trim();
                let v2 = b.children[idx].innerText.trim();
                // Numérique ?
                if (!isNaN(v1.replace(/\s/g,'')) && !isNaN(v2.replace(/\s/g,''))) {
                    v1 = parseFloat(v1.replace(/\s/g,'').replace(',','.'));
                    v2 = parseFloat(v2.replace(/\s/g,'').replace(',','.'));
                }
                return (v1 > v2 ? 1 : v1 < v2 ? -1 : 0) * (sortAsc ? 1 : -1);
            });
            rows.forEach(r => table.querySelector('tbody').appendChild(r));
            showToast('Tri appliqué sur la colonne : ' + th.textContent.trim().replace(/▲|▼/g,''), 'info');
            showPage(1);
        };
    });
    // Export Excel
    document.getElementById('exportExcel').onclick = function() {
        if (typeof XLSX === 'undefined' || !XLSX.utils) {
            showToast('Erreur : librairie XLSX non chargée.', 'danger');
            return;
        }
        const rows = Array.from(document.querySelectorAll('.table-responsive table tr'));
        const data = rows.map(r => Array.from(r.children).map(td => td.innerText));
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Médicaments');
        XLSX.writeFile(wb, 'medicaments_fawsayni.xlsx');
        showToast('Export Excel réussi !', 'success');
    };
    // Export PDF
    document.getElementById('exportPDF').onclick = function() {
        if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined' || typeof window.jspdf.autoTable === 'undefined') {
            showToast('Erreur : librairie jsPDF ou autoTable non chargée.', 'danger');
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.setFontSize(16);
        doc.text('Liste des médicaments - Fawsayni', 14, 15);
        doc.setFontSize(10);
        doc.text('Exporté le : ' + new Date().toLocaleString('fr-FR'), 14, 22);
        const table = document.querySelector('.table-responsive table');
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText);
        const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
        const data = rows.map(row => Array.from(row.children).map(td => td.innerText));
        doc.autoTable({
            head: [headers],
            body: data,
            startY: 28,
            styles: { fontSize: 9 },
            headStyles: { fillColor: [41,128,185], textColor: 255 },
            margin: { left: 10, right: 10 }
        });
        doc.save('medicaments_fawsayni.pdf');
        showToast('Export PDF réussi !', 'success');
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
