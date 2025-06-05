<?php
// Configuration de la base de données
$serveur = 'localhost';
$nom_base = 'unesco';
$nom_utilisateur = 'postgres';
$mot_de_passe = '2606';

try {
    $pdo = new PDO("mysql:host=$serveur;dbname=$nom_base;charset=utf8", $nom_utilisateur, $mot_de_passe);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des articles les plus récents pour le carrousel
$requete = $pdo->prepare("
    SELECT a.*, an.Nom as nom_anecdote, u.Nom as nom_auteur 
    FROM Articles a 
    LEFT JOIN Annecdote an ON a.ID_Ann = an.ID_Ann 
    LEFT JOIN Utilisateurs u ON a.ID_U = u.ID_U 
    ORDER BY a.Date_creation DESC 
    LIMIT 5
");
$requete->execute();
$articles_carrousel = $requete->fetchAll(PDO::FETCH_ASSOC);

// Récupération des articles par catégorie/tags
$categories = ['Découvertes', 'Histoire', 'Activités'];
$articles_par_categorie = [];

foreach ($categories as $categorie) {
    $requete = $pdo->prepare("
        SELECT a.*, an.Nom as nom_anecdote, u.Nom as nom_auteur 
        FROM Articles a 
        LEFT JOIN Annecdote an ON a.ID_Ann = an.ID_Ann 
        LEFT JOIN Utilisateurs u ON a.ID_U = u.ID_U 
        WHERE a.tags LIKE ? 
        ORDER BY a.Date_creation DESC 
        LIMIT 2
    ");
    $requete->execute(['%' . $categorie . '%']);
    $articles_par_categorie[$categorie] = $requete->fetchAll(PDO::FETCH_ASSOC);
}

// Gestion de la recherche
$resultats_recherche = [];
if (isset($_GET['recherche']) && !empty($_GET['recherche'])) {
    $terme_recherche = $_GET['recherche'];
    $requete = $pdo->prepare("
        SELECT a.*, an.Nom as nom_anecdote, u.Nom as nom_auteur 
        FROM Articles a 
        LEFT JOIN Annecdote an ON a.ID_Ann = an.ID_Ann 
        LEFT JOIN Utilisateurs u ON a.ID_U = u.ID_U 
        WHERE a.Nom LIKE ? 
        ORDER BY a.Date_creation DESC
    ");
    $requete->execute(['%' . $terme_recherche . '%']);
    $resultats_recherche = $requete->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M.C.N. - Quais de Seine</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #2c2c2c;
            color: white;
        }

        /* En-tête */
        .en_tete {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 400"><rect fill="%23e8f4f8" width="1200" height="400"/></svg>');
            background-size: cover;
            background-position: center;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 50px;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
        }

        .cercle_logo {
            width: 40px;
            height: 40px;
            background-color: #ff6b35;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }

        .liens_navigation {
            display: flex;
            gap: 30px;
        }

        .liens_navigation a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
        }

        .liens_navigation a:hover {
            color: #ffad8a;
        }

        .boite_recherche {
            position: relative;
        }

        .boite_recherche input {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            background-color: rgba(255,255,255,0.9);
            width: 200px;
        }

        .boite_recherche button {
            position: absolute;
            right: 5px;
            top: 2px;
            background: none;
            border: none;
            padding: 6px;
            cursor: pointer;
        }

        /* Carrousel */
        .conteneur_carrousel {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .diapositive_carrousel {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }

        .diapositive_carrousel.active {
            opacity: 1;
        }

        .superposition_carrousel {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.7);
            padding: 15px;
            border-radius: 5px;
            max-width: 300px;
        }

        .titre_carrousel {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .description_carrousel {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Contenu principal */
        .contenu_principal {
            padding: 40px 50px;
        }

        .section {
            margin-bottom: 50px;
        }

        .titre_section {
            color: #ff6b35;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            border-left: 4px solid #ff6b35;
            padding-left: 15px;
        }

        .grille_articles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .carte_article {
            background-color: #404040;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            transition: transform 0.3s ease;
        }

        .carte_article:hover {
            transform: translateY(-5px);
        }

        .image_article {
            width: 120px;
            height: 120px;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }

        .contenu_article {
            padding: 20px;
            flex: 1;
        }

        .titre_article {
            color: #ff6b35;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .description_article {
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .lien_article {
            color: #ff6b35;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        .lien_article:hover {
            color: #ffad8a;
        }

        /* Pied de page */
        .pied_page {
            background-color: #1a1a1a;
            text-align: center;
            padding: 30px;
            margin-top: 50px;
        }

        .texte_pied_page {
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .liens_pied_page {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .liens_pied_page a {
            color: #ff6b35;
            text-decoration: none;
        }

        .liens_pied_page a:hover {
            color: #ffad8a;
        }

        /* Résultats de recherche */
        .resultats_recherche {
            background-color: #404040;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .resultats_recherche h3 {
            color: #ff6b35;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header class="en_tete">
        <a href="index.php" class="logo">
            <div class="cercle_logo">M.C.N.</div>
        </a>
        
        <nav class="liens_navigation">
            <a href="glossaire.php">GLOSSAIRE</a>
            <a href="itineraires.php">ITINÉRAIRES</a>
        </nav>
        
        <form class="boite_recherche" method="GET">
            <input type="text" name="recherche" placeholder="Rechercher un article..." value="<?= htmlspecialchars($_GET['recherche'] ?? '') ?>">
            <button type="submit">🔍</button>
        </form>
    </header>

    <!-- Carrousel -->
    <div class="conteneur_carrousel">
        <?php foreach ($articles_carrousel as $index => $article): ?>
            <div class="diapositive_carrousel <?= $index === 0 ? 'active' : '' ?>" 
                 style="background-image: url('images/<?= htmlspecialchars($article['image_miniature']) ?>');">
                <div class="superposition_carrousel">
                    <div class="titre_carrousel"><?= htmlspecialchars($article['Nom']) ?></div>
                    <div class="description_carrousel">
                        <?= htmlspecialchars(substr($article['text'], 0, 100)) ?>...
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <main class="contenu_principal">
        <?php if (!empty($resultats_recherche)): ?>
            <div class="resultats_recherche">
                <h3>Résultats de recherche pour "<?= htmlspecialchars($_GET['recherche']) ?>"</h3>
                <div class="grille_articles">
                    <?php foreach ($resultats_recherche as $article): ?>
                        <div class="carte_article">
                            <div class="image_article" style="background-image: url('images/<?= htmlspecialchars($article['image_miniature']) ?>');"></div>
                            <div class="contenu_article">
                                <div class="titre_article"><?= htmlspecialchars($article['Nom']) ?></div>
                                <div class="description_article">
                                    <?= htmlspecialchars(substr($article['text'], 0, 100)) ?>...
                                </div>
                                <a href="article.php?id=<?= $article['ID_A'] ?>" class="lien_article">Lire plus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($categories as $categorie): ?>
            <?php if (!empty($articles_par_categorie[$categorie])): ?>
                <section class="section">
                    <h2 class="titre_section"><?= $categorie ?></h2>
                    <div class="grille_articles">
                        <?php foreach ($articles_par_categorie[$categorie] as $article): ?>
                            <div class="carte_article">
                                <div class="image_article" style="background-image: url('images/<?= htmlspecialchars($article['image_miniature']) ?>');"></div>
                                <div class="contenu_article">
                                    <div class="titre_article"><?= htmlspecialchars($article['Nom']) ?></div>
                                    <div class="description_article">
                                        <?= htmlspecialchars(substr($article['text'], 0, 100)) ?>...
                                    </div>
                                    <a href="article.php?id=<?= $article['ID_A'] ?>" class="lien_article">Lire plus</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    </main>

    <footer class="pied_page">
        <div class="texte_pied_page">© 2025 Quais de Seine - Projet UNESCO</div>
        <div class="liens_pied_page">
            <a href="mentions.php">Mentions</a>
            <a href="contact.php">Contact</a>
        </div>
    </footer>

    <script>
        // Fonctionnalité du carrousel
        let diapositive_actuelle = 0;
        const diapositives = document.querySelectorAll('.diapositive_carrousel');
        const nombre_diapositives = diapositives.length;

        function afficher_diapositive(index) {
            diapositives.forEach(diapositive => diapositive.classList.remove('active'));
            diapositives[index].classList.add('active');
        }

        function diapositive_suivante() {
            diapositive_actuelle = (diapositive_actuelle + 1) % nombre_diapositives;
            afficher_diapositive(diapositive_actuelle);
        }

        // Avancement automatique du carrousel toutes les 5 secondes
        if (nombre_diapositives > 1) {
            setInterval(diapositive_suivante, 5000);
        }

        // Fonctionnalité de recherche
        document.querySelector('.boite_recherche input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    </script>
</body>
</html>