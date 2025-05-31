<?php
// mot_de_passe_oublie.php
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
    } else {
        require_once 'includes/db.php';
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // Générer un token unique (valable 1h)
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $pdo->prepare('UPDATE utilisateurs SET reset_token = ?, reset_expires = ? WHERE id = ?')
                ->execute([$token, $expires, $user['id']]);
            // Lien de réinitialisation (à adapter pour prod)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token";
            // Envoyer l'email (ici, juste affiché pour la démo)
            $message = "Un lien de réinitialisation a été envoyé à votre adresse email.<br><small>Lien de test : <a href='$reset_link'>$reset_link</a></small>";
            // Pour prod : utiliser mail($email, ...) à la place
        } else {
            $message = "Aucun compte trouvé avec cet email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Fawsayni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(120deg, #e0f7fa 0%, #ffffff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .forgot-container { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); padding: 36px 28px; max-width: 400px; width: 100%; text-align: center; }
        .logo-pharma-text { font-family: 'Segoe UI', 'Arial', sans-serif; font-size: 2em; font-weight: bold; background: linear-gradient(90deg, #ff9800 10%, #00bcd4 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 2px; margin-bottom: 10px; }
        .form-control:focus { border-color: #00bcd4; box-shadow: 0 0 0 2px #00bcd433; }
        .btn-custom { background: #009688; color: #fff; border-radius: 30px; font-weight: bold; letter-spacing: 1px; transition: background 0.2s; }
        .btn-custom:hover { background: #00796b; }
        .msg { margin-bottom: 18px; color: #00897b; font-weight: 500; }
        .msg.error { color: #e53935; background: #ffebee; border-radius: 8px; padding: 8px 0; }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="logo-pharma-text mb-2">Fawsayni</div>
        <h4 class="mb-3" style="color:#009688; font-weight:bold;">Mot de passe oublié</h4>
        <?php if ($message): ?>
            <div class="msg<?= strpos($message, 'envoyé') === false ? ' error' : '' ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3 text-start">
                <label for="email" class="form-label">Votre adresse email</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-custom w-100">Envoyer le lien</button>
        </form>
        <div class="mt-4 text-start">
            <a href="login.php" class="link-secondary" style="text-decoration:none; font-size:0.97em;">
                <span style="font-size:1.1em; vertical-align:middle;">&#8592;</span> Retour à la connexion
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
