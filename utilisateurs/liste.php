<?php
// Liste des utilisateurs (administration)
// Accessible uniquement à l'administrateur

session_start();
require_once '../includes/db.php';
// Vérification dynamique de la permission
function user_has_permission($pdo, $user_id, $perm_name) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM utilisateurs u
        JOIN roles r ON u.role_id = r.id
        JOIN role_permissions rp ON r.id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.id
        WHERE u.id = ? AND p.nom = ?');
    $stmt->execute([$user_id, $perm_name]);
    return $stmt->fetchColumn() > 0;
}
if (!isset($_SESSION['user_id']) || !user_has_permission($pdo, $_SESSION['user_id'], 'voir_utilisateurs')) {
    header('Location: ../login.php');
    exit;
}

// Récupérer tous les utilisateurs
try {
    $stmt = $pdo->query('SELECT u.id, u.nom, u.prenom, u.email, r.nom AS role_nom FROM utilisateurs u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.nom ASC');
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors de la récupération des utilisateurs : ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar-initials { display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:#e0e7ef;color:#37517e;font-weight:600;margin-right:7px; font-size:1.1em; }
        .badge-role-admin { background:#b30000; color:#fff; font-weight:500; }
        .badge-role-assistant { background:#007bff; color:#fff; font-weight:500; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Fawsayni</a>
            <div>
                <a href="ajouter.php" class="btn btn-success btn-sm me-2">+ Ajouter un utilisateur</a>
                <a href="../dashboard.php" class="btn btn-outline-primary btn-sm">Retour au tableau de bord</a>
                <div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000; transition: top 0.4s, bottom 0.4s;"></div>
                <button id="exportExcel" class="btn btn-warning btn-sm ms-2">Exporter Excel</button>
            </div>
        </div>
    </nav>
    <div class="container mb-5">
        <div class="card shadow-sm">
            <div class="card-body table-responsive p-0">
                <input type="text" id="searchTable" class="form-control mb-3" placeholder="Recherche rapide (nom, email, rôle...)">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($utilisateurs)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($utilisateurs as $u): ?>
                            <tr>
                                <td>
                                    <span class="avatar-initials">
                                        <?php
                                            $ini = strtoupper(mb_substr($u['prenom'],0,1).mb_substr($u['nom'],0,1));
                                            echo $ini;
                                        ?>
                                    </span>
                                    <?php echo htmlspecialchars($u['prenom'].' '.$u['nom']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if (!empty($u['role_nom'])): ?>
                                        <span class="badge bg-primary"> <?php echo htmlspecialchars($u['role_nom']); ?> </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non défini</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="modifier.php?id=<?php echo $u['id']; ?>" class="btn btn-primary btn-sm">Modifier</a>
                                    <?php if (!empty($u['role_nom']) && $u['role_nom'] !== 'admin'): ?>
                                        <a href="supprimer.php?id=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                                    <?php endif; ?>
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
    const tableBody = document.querySelector('#usersTable tbody');
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
    document.getElementById('searchTable').addEventListener('input', function() {
        const val = this.value.toLowerCase();
        allRows.forEach(row => {
            row.style.display = Array.from(row.cells).some(cell => cell.textContent.toLowerCase().includes(val)) ? '' : 'none';
        });
        showPage(1);
    });
    // Export Excel
    document.getElementById('exportExcel').onclick = function() {
        const rows = Array.from(document.querySelectorAll('#usersTable tr'));
        const data = rows.map(r => Array.from(r.children).map(td => td.innerText));
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Utilisateurs');
        XLSX.writeFile(wb, 'utilisateurs_fawsayni.xlsx');
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
