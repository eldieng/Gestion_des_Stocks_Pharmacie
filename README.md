# Fawsayni - Système de Gestion de Pharmacie

![Fawsayni Logo](https://img.shields.io/badge/Fawsayni-Gestion%20de%20Pharmacie-009688?style=for-the-badge)

Fawsayni est une application web complète pour la gestion de pharmacie, développée avec PHP et MySQL. Elle permet aux pharmaciens de gérer efficacement leur inventaire, les ventes, les achats et le personnel.

## 📋 Fonctionnalités

- **Gestion des médicaments**
  - Ajout, modification et suppression de médicaments
  - Suivi des stocks et alertes pour les produits périmés ou en rupture
  - Importation en masse via fichiers CSV

- **Gestion des ventes**
  - Enregistrement des ventes
  - Historique détaillé des transactions
  - Facturation

- **Gestion des achats**
  - Suivi des approvisionnements
  - Historique des achats
  - Optimisation des stocks

- **Gestion des utilisateurs**
  - Création de comptes avec différents niveaux d'accès
  - Sécurité et authentification
  - Contrôle des permissions

- **Statistiques et rapports**
  - Tableaux de bord avec indicateurs clés
  - Rapports sur les ventes, achats et stocks
  - Alertes automatiques

## 🚀 Installation

### Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx, etc.)
- Composer (recommandé)

### Étapes d'installation

1. Clonez ce dépôt sur votre serveur web :
   ```bash
   git clone https://github.com/votre-username/fawsayni.git
   ```

2. Importez la base de données :
   - Créez une base de données MySQL nommée `pharmacie`
   - Importez le fichier `database/pharmacie.sql`

3. Configurez la connexion à la base de données :
   - Modifiez le fichier `config/database.php` avec vos informations de connexion

4. Accédez à l'application via votre navigateur :
   ```
   http://localhost/fawsayni
   ```

5. Connectez-vous avec les identifiants par défaut :
   - Utilisateur : admin
   - Mot de passe : admin123

## 🔧 Configuration

Vous pouvez personnaliser les paramètres de l'application en modifiant le fichier `config/config.php` :

```php
// Exemple de configuration
define('APP_NAME', 'Fawsayni');
define('APP_URL', 'http://localhost/fawsayni');
define('CURRENCY', 'XOF');
```

## 🖥️ Technologies utilisées

- **Frontend** : HTML, CSS, JavaScript, Bootstrap 5
- **Backend** : PHP
- **Base de données** : MySQL
- **Bibliothèques** : Font Awesome, Chart.js, DataTables

## 📸 Captures d'écran

<details>
<summary>Voir les captures d'écran</summary>

### Page d'accueil
![Page d'accueil](screenshots/accueil.png)

### Tableau de bord
![Tableau de bord](screenshots/dashboard.png)

### Gestion des médicaments
![Gestion des médicaments](screenshots/medicaments.png)

</details>

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/amazing-feature`)
3. Committez vos changements (`git commit -m 'Add some amazing feature'`)
4. Poussez vers la branche (`git push origin feature/amazing-feature`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 📞 Contact

El Hadji Dieng - [el.elhadji.dieng@gmail.com](mailto:el.elhadji.dieng@gmail.com)

Site web : [khidmaservices.com](https://khidmaservices.com)

---

&copy; 2025 Fawsayni - Tous droits réservés
#
