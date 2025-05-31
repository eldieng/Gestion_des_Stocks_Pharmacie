<?php
// reset_password.php
$message = '';
$show_form = true;
require_once 'includes/db.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    $message = "Lien invalide.";
    $show_form = false;
} else {
    // Vérifier le token
    $stmt = $pdo->prepare('SELECT id, reset_expires FROM utilisateurs WHERE reset_token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if (!$user) {
        $message = "Lien de réinitialisation invalide.";
        $show_form = false;
    } elseif (strtotime($user['reset_expires']) < time()) {
        $message = "Ce lien a expiré. Merci de refaire une demande.";
        $show_form = false;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pass = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if (strlen($pass) < 6) {
            $message = "Le mot de passe doit contenir au moins 6 caractères.";
        } elseif ($pass !== $pass2) {
            $message = "Les mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE utilisateurs SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?')
                ->execute([$hash, $user['id']]);
            $message = "Votre mot de passe a été réinitialisé avec succès. <a href='login.php'>Se connecter</a>";
            $show_form = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - Fawsayni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(120deg, #e0f7fa 0%, #ffffff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-container { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); padding: 36px 28px; max-width: 400px; width: 100%; text-align: center; }
        .logo-pharma-text { font-family: 'Segoe UI', 'Arial', sans-serif; font-size: 2em; font-weight: bold; background: linear-gradient(90deg, #ff9800 10%, #00bcd4 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 2px; margin-bottom: 10px; }
        .form-control:focus { border-color: #00bcd4; box-shadow: 0 0 0 2px #00bcd433; }
        .btn-custom { background: #009688; color: #fff; border-radius: 30px; font-weight: bold; letter-spacing: 1px; transition: background 0.2s; }
        .btn-custom:hover { background: #00796b; }
        .msg { margin-bottom: 18px; color: #00897b; font-weight: 500; }
        .msg.error { color: #e53935; background: #ffebee; border-radius: 8px; padding: 8px 0; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo-pharma-text mb-2">Fawsayni</div>
        <h4 class="mb-3" style="color:#009688; font-weight:bold;">Réinitialiser le mot de passe</h4>
        <?php if ($message): ?>
            <div class="msg<?= ($show_form ? ' error' : '') ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($show_form): ?>
        <form method="post" action="">
            <div class="mb-3 text-start">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3 text-start">
                <label for="password2" class="form-label">Confirmer le mot de passe</label>
                <input type="password" id="password2" name="password2" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-custom w-100">Réinitialiser</button>
        </form>
        <?php endif; ?>
        <div class="mt-4 text-start">
            <a href="login.php" class="link-secondary" style="text-decoration:none; font-size:0.97em;">
                <span style="font-size:1.1em; vertical-align:middle;">&#8592;</span> Retour à la connexion
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
