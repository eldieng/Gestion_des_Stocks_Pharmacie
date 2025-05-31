<?php
// Page d'enregistrement d'un achat (entrée de stock)
// Accessible uniquement aux utilisateurs connectés

session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';
// Récupérer la liste des médicaments
try {
    $stmt = $pdo->query('SELECT * FROM medicaments ORDER BY nom ASC');
    $medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors de la récupération des médicaments : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medicament = (int)($_POST['id_medicament'] ?? 0);
    $quantite = (int)($_POST['quantite'] ?? 0);
    $utilisateur_id = $_SESSION['user_id'];

    if ($id_medicament > 0 && $quantite > 0) {
        try {
            $pdo->beginTransaction();
            // Incrémenter le stock
            $stmt = $pdo->prepare('UPDATE medicaments SET quantite = quantite + ? WHERE id = ?');
            $stmt->execute([$quantite, $id_medicament]);
            // Enregistrer l'achat
            $stmt = $pdo->prepare('INSERT INTO achats (id_medicament, quantite, utilisateur_id) VALUES (?, ?, ?)');
            $stmt->execute([$id_medicament, $quantite, $utilisateur_id]);
            $pdo->commit();
            $message = "Achat enregistré avec succès !";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez sélectionner un médicament et une quantité valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrer un achat (entrée de stock)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f6f7fb; }
        .achat-card { max-width: 420px; margin: 40px auto; border-radius: 1.2rem; }
        .achat-icon { font-size: 2.6rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Fawsayni</a>
            <div>
                <a href="../dashboard.php" class="btn btn-outline-primary btn-sm me-2">🏠 Tableau de bord</a>
                <a href="historique.php" class="btn btn-outline-secondary btn-sm">📊 Historique des achats</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="card shadow achat-card">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <span class="achat-icon">🛍️</span>
                    <h3 class="mb-0">Enregistrer un achat</h3>
                </div>
                <?php if ($message): ?>
                    <div class="alert <?php echo (strpos($message, 'succès') !== false) ? 'alert-success' : 'alert-danger'; ?> text-center" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="" autocomplete="off">
                    <div class="mb-3">
                        <label for="search_medicament" class="form-label">Recherche rapide</label>
                        <input type="text" id="search_medicament" class="form-control mb-2" placeholder="Rechercher un médicament..." autocomplete="off">
                        <label for="id_medicament" class="form-label">Médicament</label>
                        <select name="id_medicament" id="id_medicament" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($medicaments as $med): ?>
                                <option value="<?php echo $med['id']; ?>" data-stock="<?php echo (int)$med['quantite']; ?>" data-prix="<?php echo isset($med['prix_unitaire']) ? htmlspecialchars($med['prix_unitaire']) : ''; ?>" data-nom="<?php echo htmlspecialchars($med['nom']); ?>" <?php if(isset($med['image']) && $med['image']){ ?>data-image="<?php echo htmlspecialchars($med['image']); ?>"<?php } ?>>
                                    <?php echo htmlspecialchars($med['nom']) . " (Stock : " . (int)$med['quantite'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="stock_info" class="form-text mt-1 text-primary"></div>
                        <div id="prix_info" class="form-text mt-1"></div>
                        <div id="image_medicament" class="text-center mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="quantite" class="form-label">Quantité</label>
                        <input type="number" name="quantite" id="quantite" class="form-control" min="1" required placeholder="Quantité à ajouter">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><span style="font-size:1.3em;">🛍️</span> Enregistrer l'achat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Recherche rapide sur la liste
        const searchInput = document.getElementById('search_medicament');
        const select = document.getElementById('id_medicament');
        const stockInfo = document.getElementById('stock_info');
        const prixInfo = document.getElementById('prix_info');
        const imageMed = document.getElementById('image_medicament');
        const quantiteInput = document.getElementById('quantite');
        const submitBtn = document.querySelector('button[type="submit"]');
        const originalOptions = Array.from(select.options);
        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            select.innerHTML = '';
            const first = document.createElement('option');
            first.value = '';
            first.textContent = '-- Choisir --';
            select.appendChild(first);
            originalOptions.slice(1).forEach(opt => {
                if(opt.textContent.toLowerCase().includes(val)) {
                    select.appendChild(opt.cloneNode(true));
                }
            });
            stockInfo.textContent = '';
            prixInfo.textContent = '';
            imageMed.innerHTML = '';
            quantiteInput.value = '';
            submitBtn.disabled = true;
        });
        select.addEventListener('change', function() {
            const selected = select.options[select.selectedIndex];
            const stock = selected.getAttribute('data-stock');
            const prix = selected.getAttribute('data-prix');
            const nom = selected.getAttribute('data-nom');
            const image = selected.getAttribute('data-image');
            if (stock !== null && selected.value) {
                stockInfo.textContent = 'Stock actuel : ' + stock;
                prixInfo.textContent = prix ? 'Prix unitaire : ' + prix + ' DA' : '';
                quantiteInput.value = '';
                submitBtn.disabled = true;
                if (image) {
                    imageMed.innerHTML = '<img src="../uploads/' + image + '" alt="' + nom + '" style="max-width:90px;max-height:90px;border-radius:10px;">';
                } else {
                    imageMed.innerHTML = '';
                }
            } else {
                stockInfo.textContent = '';
                prixInfo.textContent = '';
                imageMed.innerHTML = '';
                quantiteInput.value = '';
                submitBtn.disabled = true;
            }
        });
        quantiteInput.addEventListener('input', function() {
            const selected = select.options[select.selectedIndex];
            const prix = selected.getAttribute('data-prix');
            let qte = parseInt(this.value, 10);
            if (!selected.value || !qte || qte < 1) {
                submitBtn.disabled = true;
                prixInfo.textContent = prix ? 'Prix unitaire : ' + prix + ' DA' : '';
                return;
            }
            submitBtn.disabled = false;
            if (prix && !isNaN(qte)) {
                prixInfo.textContent = 'Prix unitaire : ' + prix + ' DA | Total : ' + (qte * parseFloat(prix)).toFixed(2) + ' DA';
            } else if (prix) {
                prixInfo.textContent = 'Prix unitaire : ' + prix + ' DA';
            } else {
                prixInfo.textContent = '';
            }
        });
        window.addEventListener('DOMContentLoaded', function() {
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
