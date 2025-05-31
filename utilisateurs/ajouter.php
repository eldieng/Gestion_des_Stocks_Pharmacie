<?php
// Page d'ajout d'utilisateur (admin uniquement)
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
if (!isset($_SESSION['user_id']) || !user_has_permission($pdo, $_SESSION['user_id'], 'ajouter_utilisateur')) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$nom = '';
$prenom = '';
$email = '';
$role_id = '';

// Charger tous les rôles dynamiquement
$roles = $pdo->query('SELECT id, nom FROM roles ORDER BY nom ASC')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role_id = $_POST['role_id'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe2 = $_POST['mot_de_passe2'] ?? '';

    // Validation
    if (!$nom || !$prenom || !$email || !$mot_de_passe || !$mot_de_passe2 || !$role_id) {
        $message = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide.";
    } elseif ($mot_de_passe !== $mot_de_passe2) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier unicité email
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            $message = "Cet email existe déjà.";
        } else {
            // Hashage du mot de passe
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role_id) VALUES (?, ?, ?, ?, ?)');
            if ($stmt->execute([$nom, $prenom, $email, $hash, $role_id])) {
                // Audit : journaliser l'ajout
                require_once __DIR__.'/../includes/audit.php';
                log_action($pdo, $_SESSION['user_id'], 'ajout', 'utilisateurs', $pdo->lastInsertId(), json_encode(['nom'=>$nom,'prenom'=>$prenom,'email'=>$email,'role_id'=>$role_id]));
                header('Location: liste.php?success=1');
                exit;
            } else {
                $message = "Erreur lors de l'ajout de l'utilisateur.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f7fb; }
        .ajout-card { max-width: 430px; margin: 40px auto; border-radius: 1.2rem; }
        .ajout-icon { font-size: 2.6rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Fawsayni</a>
            <div>
                <a href="liste.php" class="btn btn-outline-primary btn-sm">Retour à la liste</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="card shadow ajout-card">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <span class="ajout-icon">👤</span>
                    <h3 class="mb-0">Ajouter un utilisateur</h3>
                </div>
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
                <form method="post" id="userForm" autocomplete="off">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" name="nom" id="nom" class="form-control" value="<?php echo htmlspecialchars($nom); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" name="prenom" id="prenom" class="form-control" value="<?php echo htmlspecialchars($prenom); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        <div id="emailHelp" class="form-text"></div>
                    </div>
                    <div class="mb-3">
    <label for="role_id" class="form-label">Rôle</label>
    <select name="role_id" id="role_id" class="form-select" required>
        <option value="">-- Sélectionner un rôle --</option>
        <?php foreach($roles as $r): ?>
            <option value="<?php echo $r['id']; ?>" <?php if ($role_id == $r['id']) echo 'selected'; ?>><?php echo htmlspecialchars($r['nom']); ?></option>
        <?php endforeach; ?>
    </select>
</div>
                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="mot_de_passe2" class="form-label">Confirmer mot de passe</label>
                        <input type="password" name="mot_de_passe2" id="mot_de_passe2" class="form-control" required>
                        <div id="mdpHelp" class="form-text"></div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validation dynamique JS (mot de passe identique, email)
    document.getElementById('userForm').addEventListener('input', function() {
        const email = document.getElementById('email');
        const emailHelp = document.getElementById('emailHelp');
        emailHelp.textContent = '';
        if (email.value && !/^\S+@\S+\.\S+$/.test(email.value)) {
            emailHelp.textContent = 'Format email invalide';
            emailHelp.className = 'form-text text-danger';
        } else {
            emailHelp.className = 'form-text';
        }
        const mdp1 = document.getElementById('mot_de_passe');
        const mdp2 = document.getElementById('mot_de_passe2');
        const mdpHelp = document.getElementById('mdpHelp');
        mdpHelp.textContent = '';
        if (mdp1.value && mdp2.value && mdp1.value !== mdp2.value) {
            mdpHelp.textContent = 'Les mots de passe ne correspondent pas';
            mdpHelp.className = 'form-text text-danger';
        } else {
            mdpHelp.className = 'form-text';
        }
    });
    </script>
</body>
</html>
