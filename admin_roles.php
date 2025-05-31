<?php
// admin_roles.php : Gestion des rôles et des permissions personnalisées
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Sécurité : vérifier la permission 'admin_roles'
function user_has_permission($pdo, $user_id, $perm_name) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM utilisateurs u
        JOIN roles r ON u.role_id = r.id
        JOIN role_permissions rp ON r.id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.id
        WHERE u.id = ? AND p.nom = ?');
    $stmt->execute([$user_id, $perm_name]);
    return $stmt->fetchColumn() > 0;
}
// Ajouter la permission 'admin_roles' si elle n'existe pas
$pdo->exec("INSERT IGNORE INTO permissions (nom, description) VALUES ('admin_roles', 'Accès à la gestion des rôles et permissions')");
if (!user_has_permission($pdo, $_SESSION['user_id'], 'admin_roles')) {
    echo '<div style="color:red;text-align:center;margin-top:40px;font-size:1.2em;">Accès refusé : vous n\'avez pas la permission d\'administrer les rôles.</div>';
    exit;
}

$tab = $_GET['tab'] ?? 'roles'; // tab: roles | permissions | assign
$message = '';

// --- AJOUT/MODIF/SUPPRESSION ROLES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter_role') {
        $nom = trim($_POST['nom'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($nom) {
            $stmt = $pdo->prepare('INSERT INTO roles (nom, description) VALUES (?, ?)');
            if ($stmt->execute([$nom, $desc])) {
                $message = 'Rôle ajouté !';
            } else {
                $message = 'Erreur lors de l\'ajout.';
            }
        }
    } elseif ($_POST['action'] === 'supprimer_role' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM roles WHERE id = ?');
        if ($stmt->execute([$id])) {
            $message = 'Rôle supprimé.';
        }
    }
    // Ajout/suppression permissions similaire plus bas
}

// --- LISTE DES RÔLES ---
$roles = $pdo->query('SELECT * FROM roles ORDER BY nom ASC')->fetchAll(PDO::FETCH_ASSOC);
$permissions = $pdo->query('SELECT * FROM permissions ORDER BY nom ASC')->fetchAll(PDO::FETCH_ASSOC);

// --- ASSIGNATION DES PERMISSIONS A UN ROLE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_permissions' && isset($_POST['role_id'])) {
    $role_id = (int)$_POST['role_id'];
    $perms = $_POST['permissions'] ?? [];
    $pdo->prepare('DELETE FROM role_permissions WHERE role_id=?')->execute([$role_id]);
    foreach ($perms as $pid) {
        $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)')->execute([$role_id, (int)$pid]);
    }
    $message = 'Permissions mises à jour.';
}

// --- PERMISSIONS D'UN ROLE (pour affichage assignation) ---
$role_permissions = [];
foreach ($roles as $r) {
    $stmt = $pdo->prepare('SELECT permission_id FROM role_permissions WHERE role_id = ?');
    $stmt->execute([$r['id']]);
    $role_permissions[$r['id']] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_id');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration des rôles & permissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f6f7fb;}</style>
</head>
<body>
<nav class="navbar navbar-light bg-white shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">Fawsayni</a>
    <a href="dashboard.php" class="btn btn-outline-primary btn-sm">Tableau de bord</a>
  </div>
</nav>
<div class="container mb-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-3">Gestion des rôles & permissions</h3>
            <?php if ($message): ?>
                <div class="alert alert-info"> <?php echo htmlspecialchars($message); ?> </div>
            <?php endif; ?>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link <?php if($tab==='roles')echo'active'; ?>" href="?tab=roles">Rôles</a></li>
                <li class="nav-item"><a class="nav-link <?php if($tab==='permissions')echo'active'; ?>" href="?tab=permissions">Permissions</a></li>
                <li class="nav-item"><a class="nav-link <?php if($tab==='assign')echo'active'; ?>" href="?tab=assign">Assignation</a></li>
            </ul>

            <?php if ($tab==='roles'): ?>
            <!-- CRUD ROLES -->
            <form method="post" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="action" value="ajouter_role">
                <div class="col-md-4"><input type="text" name="nom" class="form-control" placeholder="Nom du rôle" required></div>
                <div class="col-md-5"><input type="text" name="description" class="form-control" placeholder="Description"></div>
                <div class="col-md-3"><button type="submit" class="btn btn-success">Ajouter un rôle</button></div>
            </form>
            <table class="table table-bordered align-middle">
                <thead><tr><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($roles as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['nom']); ?></td>
                        <td><?php echo htmlspecialchars($r['description']); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="supprimer_role">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce rôle ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php elseif ($tab==='permissions'): ?>
            <!-- CRUD PERMISSIONS -->
            <form method="post" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="action" value="ajouter_permission">
                <div class="col-md-6"><input type="text" name="nom_permission" class="form-control" placeholder="Nom de la permission (ex: supprimer_utilisateur)" required></div>
                <div class="col-md-4"><input type="text" name="desc_permission" class="form-control" placeholder="Description"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-success">Ajouter</button></div>
            </form>
            <table class="table table-bordered align-middle">
                <thead><tr><th>Nom</th><th>Description</th></tr></thead>
                <tbody>
                <?php foreach ($permissions as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['nom']); ?></td>
                        <td><?php echo htmlspecialchars($p['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php elseif ($tab==='assign'): ?>
            <!-- ASSIGNATION PERMISSIONS -->
            <form method="post" class="mb-4">
                <input type="hidden" name="action" value="update_permissions">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Choisir un rôle</label>
                        <select name="role_id" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Sélectionner --</option>
                            <?php foreach($roles as $r): ?>
                                <option value="<?php echo $r['id']; ?>" <?php if(isset($_POST['role_id']) && $_POST['role_id']==$r['id']) echo 'selected'; ?>><?php echo htmlspecialchars($r['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Permissions</label>
                        <div class="d-flex flex-wrap gap-2">
                        <?php 
                        $selected_role = $_POST['role_id'] ?? null;
                        foreach($permissions as $p): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $p['id']; ?>" id="perm_<?php echo $p['id']; ?>"
                                <?php if($selected_role && in_array($p['id'], $role_permissions[$selected_role] ?? [])) echo 'checked'; ?>
                                >
                                <label class="form-check-label" for="perm_<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nom']); ?></label>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php if($selected_role): ?><div class="mt-3"><button type="submit" class="btn btn-primary">Enregistrer les permissions</button></div><?php endif; ?>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
