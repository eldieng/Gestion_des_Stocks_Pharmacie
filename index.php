<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacie - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #e0f7fa 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .home-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 40px 30px;
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .logo-pharma {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
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
        .feature-list {
            text-align: left;
            margin: 25px 0 0 0;
        }
        .feature-list li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="home-container">
        <div class="logo-pharma-text mb-2" style="font-family: 'Segoe UI', 'Arial', sans-serif; font-size: 2.7em; font-weight: bold; background: linear-gradient(90deg, #ff9800 10%, #00bcd4 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 2px;">Fawsayni</div>
        <h2 class="mb-2">Bienvenue sur <span style="color:#ff9800; font-weight:bold;">Fawsayni</span></h2>
        <p class="text-muted">Votre solution moderne pour la gestion de pharmacie</p>
        <div class="row mt-4 g-3">
            <div class="col-12">
                <a href="login.php" class="btn btn-custom btn-lg w-100">Se connecter</a>
            </div>
            <div class="col-12">
                <a href="#features" class="btn btn-outline-primary btn-lg w-100" style="border-radius:30px; font-weight:bold;">Découvrir les fonctionnalités</a>
            </div>
            <div class="col-12">
                <a href="#contact" class="btn btn-outline-secondary btn-lg w-100" style="border-radius:30px; font-weight:bold;">Contact</a>
            </div>
        </div>
        <section id="features" class="mt-5">
            <h4 class="mb-4" style="color:#00bcd4; font-weight:bold;">Découvrir nos fonctionnalités</h4>
            <div id="carouselFeatures" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" style="max-width:480px; margin:auto;">
                <div class="carousel-inner">
                    <div class="carousel-item active text-center p-4">
                        <div style="font-size:3em; color:#009688;">💊</div>
                        <h5 class="mt-3 mb-2" style="font-weight:bold;">Gestion des médicaments</h5>
                        <p>Ajoutez, modifiez, suivez les stocks et recevez des alertes sur les produits périmés ou en rupture.</p>
                    </div>
                    <div class="carousel-item text-center p-4">
                        <div style="font-size:3em; color:#ff9800;">🛒</div>
                        <h5 class="mt-3 mb-2" style="font-weight:bold;">Gestion des ventes</h5>
                        <p>Enregistrez chaque vente simplement et consultez l’historique détaillé pour un meilleur suivi.</p>
                    </div>
                    <div class="carousel-item text-center p-4">
                        <div style="font-size:3em; color:#00bcd4;">📦</div>
                        <h5 class="mt-3 mb-2" style="font-weight:bold;">Gestion des achats</h5>
                        <p>Gérez vos approvisionnements, gardez l’historique de chaque achat et optimisez vos stocks.</p>
                    </div>
                    <div class="carousel-item text-center p-4">
                        <div style="font-size:3em; color:#673ab7;">👥</div>
                        <h5 class="mt-3 mb-2" style="font-weight:bold;">Gestion des utilisateurs</h5>
                        <p>Créez des comptes, attribuez des rôles (admin, assistant) et contrôlez les accès en toute sécurité.</p>
                    </div>
                    <div class="carousel-item text-center p-4">
                        <div style="font-size:3em; color:#e91e63;">📊</div>
                        <h5 class="mt-3 mb-2" style="font-weight:bold;">Statistiques & alertes</h5>
                        <p>Visualisez vos ventes, achats, stocks et recevez des alertes automatiques pour une gestion proactive.</p>
                    </div>
                    <div class="carousel-item text-center p-4">
                        <div style="font-size:3em; color:#009688;">🔒</div>
                        <h5 class="mt-3 mb-2" style="font-weight:bold;">Sécurité & simplicité</h5>
                        <p>Connexion sécurisée, interface intuitive et accès rapide à toutes vos données.</p>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselFeatures" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Précédent</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselFeatures" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Suivant</span>
                </button>
                <div class="carousel-indicators mt-3">
                    <button type="button" data-bs-target="#carouselFeatures" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselFeatures" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselFeatures" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#carouselFeatures" data-bs-slide-to="3" aria-label="Slide 4"></button>
                    <button type="button" data-bs-target="#carouselFeatures" data-bs-slide-to="4" aria-label="Slide 5"></button>
                    <button type="button" data-bs-target="#carouselFeatures" data-bs-slide-to="5" aria-label="Slide 6"></button>
                </div>
            </div>
        </section>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <section id="contact" class="mt-5">
            <h4 class="mb-2" style="color:#ff9800; font-weight:bold;">Contact</h4>
            <div class="card p-3 shadow-sm border-0" style="max-width:400px; margin:auto;">
                <div class="mb-2"><strong>Nom :</strong> El Hadji Dieng</div>
                <div class="mb-2"><strong>Email :</strong> <a href="mailto:el.elhadji.dieng@gmail.com" style="color:#00bcd4; text-decoration:underline;">el.elhadji.dieng@gmail.com</a></div>
                <div class="mb-2"><strong>Téléphone :</strong> <a href="tel:+221774548661" style="color:#009688; text-decoration:underline;">+221 77 454 86 61</a></div>
                <div><strong>Site web :</strong> <a href="https://khidmaservices.com" target="_blank" rel="noopener" style="color:#ff9800; text-decoration:underline;">khidmaservices.com</a></div>
            </div>
        </section>
    </div>
</body>
</html>
