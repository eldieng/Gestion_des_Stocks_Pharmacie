<?php
// Page de connexion utilisateur (pharmacien ou assistant)
// Permet d'accéder au système de gestion de pharmacie

session_start(); // Démarre la session PHP
require_once 'includes/db.php'; // Inclusion de la connexion à la base

// Initialisation des variables
$message = '';

// Vérifie si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Vérifie que les champs sont remplis
    if ($email && $password) {
        // Prépare et exécute la requête pour trouver l'utilisateur
        $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Authentification réussie : on stocke les infos en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nom'] = $user['nom'];
            // Redirige vers le tableau de bord
            header('Location: dashboard.php');
            exit;
        } else {
            $message = "Email ou mot de passe incorrect.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Fawsayni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #e0f7fa 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 40px 30px 30px 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .logo-pharma-text {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            font-size: 2.2em;
            font-weight: bold;
            background: linear-gradient(90deg, #ff9800 10%, #00bcd4 80%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .form-control:focus {
            border-color: #00bcd4;
            box-shadow: 0 0 0 2px #00bcd433;
        }
        .btn-custom {
            background: #009688;
            color: #fff;
            border-radius: 30px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: background 0.2s;
        }
        .btn-custom:hover {
            background: #00796b;
        }
        .error-message {
            color: #e53935;
            background: #ffebee;
            border-radius: 8px;
            padding: 8px 0;
            margin-bottom: 18px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-pharma-text mb-2">Fawsayni</div>
        <h3 class="mb-3" style="color:#009688; font-weight:bold;">Connexion</h3>
        <?php if ($message): ?>
            <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3 text-start">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-3 text-start">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Se connecter</button>
        </form>
        <div class="mt-3 mb-2 text-end">
            <a href="mot_de_passe_oublie.php" class="link-primary" style="text-decoration:underline; font-size:0.97em;">Mot de passe oublié&nbsp;?</a>
        </div>
        <div class="mb-4 text-start">
            <a href="index.php" class="link-secondary" style="text-decoration:none; font-size:0.97em;">
                <span style="font-size:1.1em; vertical-align:middle;">&#8592;</span> Retour à l’accueil
            </a>
        </div>
        <div class="text-muted" style="font-size:0.95em;">
            Fawsayni - Gestion de Pharmacie
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
