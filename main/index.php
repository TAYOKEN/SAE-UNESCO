<?php
// Configuration de la base de données PostgreSQL
$serveur = 'localhost';
$nom_base = 'unesco';
$nom_utilisateur = 'postgres';
$mot_de_passe = '2606';
$port = '5432';

try {
    $pdo = new PDO("pgsql:host=$serveur;port=$port;dbname=$nom_base", $nom_utilisateur, $mot_de_passe);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
$requete = $pdo->prepare("
    SELECT a.*, an.nom as nom_anecdote, u.nom as nom_auteur 
    FROM articles a 
    LEFT JOIN anecdote an ON a.id_ann = an.id_ann 
    LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
    ORDER BY a.date_creation DESC 
    LIMIT 5
");
$requete->execute();
$articles_carrousel = $requete->fetchAll(PDO::FETCH_ASSOC);

// Récupération des tags les plus populaires
$requete_tags = $pdo->prepare("
    SELECT tags, COUNT(*) as count 
    FROM articles 
    WHERE tags IS NOT NULL AND tags != '' 
    GROUP BY tags 
    ORDER BY count DESC 
    LIMIT 10
");
$requete_tags->execute();
$tags_populaires = $requete_tags->fetchAll(PDO::FETCH_ASSOC);

// Récupération des articles par tags populaires
$articles_par_tag = [];
$anecdotes_affichees = [];

foreach ($tags_populaires as $tag_info) {
    $tag = $tag_info['tags'];
    
    // Récupérer les articles pour ce tag
$requete = $pdo->prepare("
    SELECT a.*, an.nom as nom_anecdote, an.text as texte_anecdote, u.nom as nom_auteur 
    FROM articles a 
    LEFT JOIN anecdote an ON a.id_ann = an.id_ann 
    LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
    WHERE a.tags ILIKE ? 
    ORDER BY a.date_creation DESC 
    LIMIT 3
");
    $requete->execute(['%' . $tag . '%']);
    $articles = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($articles)) {
        $articles_par_tag[$tag] = $articles;
        
        // Récupérer une anecdote aléatoire pour ce tag (1 chance sur 3)
        if (rand(1, 3) === 1) {
            $requete_anecdote = $pdo->prepare("
    SELECT an.* 
    FROM anecdote an 
    WHERE an.tags ILIKE ? 
    AND an.id_ann NOT IN (" . implode(',', array_merge([0], $anecdotes_affichees)) . ")
    ORDER BY RANDOM() 
    LIMIT 1
");

if ($anecdote) {
    $articles_par_tag[$tag]['anecdote'] = $anecdote;
    $anecdotes_affichees[] = $anecdote['id_ann']; // Correction de ID_Ann vers id_ann
}
        }
    }
}

// Gestion de la recherche
$resultats_recherche = [];
$terme_recherche = '';
if (isset($_GET['recherche']) && !empty($_GET['recherche'])) {
    $terme_recherche = $_GET['recherche'];
$requete = $pdo->prepare("
    SELECT a.*, an.nom as nom_anecdote, u.nom as nom_auteur 
    FROM articles a 
    LEFT JOIN anecdote an ON a.id_ann = an.id_ann 
    LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
    WHERE a.nom ILIKE ? OR a.text ILIKE ? OR a.tags ILIKE ?
    ORDER BY a.date_creation DESC
");
    $requete->execute(['%' . $terme_recherche . '%', '%' . $terme_recherche . '%', '%' . $terme_recherche . '%']);
    $resultats_recherche = $requete->fetchAll(PDO::FETCH_ASSOC);
}

function getImageLink($image_name) {
    if (empty($image_name)) {
        return "https://via.placeholder.com/400x300/EA5C0D/FFFFFF?text=UNESCO+Image";
    }
    
    // Si URL
    if (filter_var($image_name, FILTER_VALIDATE_URL)) {
        return $image_name;
    }
    
    // Si chemin local
    if (file_exists("images/" . $image_name)) {
        return "images/" . $image_name;
    }
    
    // Si rien
    return "https://via.placeholder.com/400x300/EA5C0D/FFFFFF?text=UNESCO+Image";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M.C.N. - Quais de Seine</title>
    <link rel="stylesheet" href="indexcss.css" />
</head>
<body>
       <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <img src="../Images/logo.png" alt="Logo" onerror="this.src='https://images.unsplash.com/photo-1549813069-f95e44d7f498?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80'">
            </a>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="../glossaire.html">GLOSSAIRE</a></li>
                    <li><a href="../itineraires.html">ITINERAIRES</a></li>
                    <li><a href="../histoire.html" class="active">HISTOIRE</a></li>
                </ul>
            </nav>
        
           <div class="search-container">
                <input type="text" class="search-box" placeholder="Chercher..." id="searchInput">
                <button class="search-btn" onclick="performSearch()">🔍</button>
            </div>

            <!-- Language Selector -->
            <div class="language-selector">
                <a href="index_eng.php" class="lang-toggle" title="English Mode">
                    <div class="lang-flag flag-en"></div>
                    <span>EN</span>
                </a>
            </div>

            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Hero Section avec Carrousel -->
    <section class="hero">
        <div class="conteneur_carrousel">
            <?php if (!empty($articles_carrousel)): ?>
                <?php foreach ($articles_carrousel as $index => $article): ?>
                    <div class="diapositive_carrousel <?= $index === 0 ? 'active' : '' ?>" 
                         style="background-image: url('<?= getImageLink($article['image_miniature']) ?>');">
                        <div class="superposition_carrousel">
                            <div class="titre_carrousel"><?= htmlspecialchars($article['nom']) ?></div>
                            <div class="description_carrousel">
                                <?= htmlspecialchars(substr($article['text'], 0, 200)) ?>...
                            </div>
                            <?php if (!empty($article['nom_auteur'])): ?>
                                <div class="auteur_carrousel">Par <?= htmlspecialchars($article['nom_auteur']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="diapositive_carrousel active" style="background-image: url('https://via.placeholder.com/1200x500/EA5C0D/FFFFFF?text=Aucun+Article+Disponible');">
                    <div class="superposition_carrousel">
                        <div class="titre_carrousel">Bienvenue sur M.C.N.</div>
                        <div class="description_carrousel">Découvrez bientôt nos articles sur les Quais de Seine et le patrimoine UNESCO.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-container">
        <?php if (!empty($resultats_recherche)): ?>
            <div class="resultats_recherche">
                <h3>Résultats de recherche pour "<?= htmlspecialchars($terme_recherche) ?>" (<?= count($resultats_recherche) ?> résultat<?= count($resultats_recherche) > 1 ? 's' : '' ?>)</h3>
                <div class="grille_articles">
                    <?php foreach ($resultats_recherche as $article): ?>
                        <div class="carte_article">
                            <div class="image_article" style="background-image: url('<?= getImageLink($article['image_miniature']) ?>');"></div>
                            <div class="contenu_article">
                                <div class="titre_article"><?= htmlspecialchars($article['nom']) ?></div>
                                <div class="meta_article">
                                    <?php if (!empty($article['nom_auteur'])): ?>
                                        Par <?= htmlspecialchars($article['nom_auteur']) ?> • 
                                    <?php endif; ?>
                                    <?= date('d/m/Y', strtotime($article['date_creation'])) ?>
                                    <?php if (!empty($article['tags'])): ?>
                                        • <span style="color: #F7AF3E;"><?= htmlspecialchars($article['tags']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="description_article">
                                    <?= htmlspecialchars(substr($article['text'], 0, 150)) ?>...
                                </div>
                                <a href="article.php?id=<?= $article['id_a'] ?>" class="lien_article">Lire plus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($articles_par_tag)): ?>
            <?php $section_count = 0; ?>
            <?php foreach ($articles_par_tag as $tag => $data): ?>
                <?php $section_count++; ?>
                
                <section class="section">
                    <h2 class="titre_section"><?= htmlspecialchars($tag) ?></h2>
                    <div class="grille_articles">
                        <?php 
                        $articles = is_array($data) && isset($data[0]) ? $data : (isset($data['anecdote']) ? array_filter($data, function($item) { return !isset($item['ID_Ann']) || isset($item['ID_A']); }) : $data);
                        if (isset($data['anecdote'])) {
                            $articles = array_filter($data, function($key) { return $key !== 'anecdote'; }, ARRAY_FILTER_USE_KEY);
                        }
                        ?>
                        <?php foreach ($articles as $article): ?>
                            <?php if (isset($article['id_a'])): ?>
                                <div class="carte_article">
                                    <div class="image_article" style="background-image: url('<?= getImageLink($article['image_miniature']) ?>');"></div>
                                    <div class="contenu_article">
                                        <div class="titre_article"><?= htmlspecialchars($article['nom']) ?></div>
                                        <div class="meta_article">
                                            <?php if (!empty($article['nom_auteur'])): ?>
                                                Par <?= htmlspecialchars($article['nom_auteur']) ?> • 
                                            <?php endif; ?>
                                            <?= date('d/m/Y', strtotime($article['date_creation'])) ?>
                                        </div>
                                        <div class="description_article">
                                            <?= htmlspecialchars(substr($article['text'], 0, 120)) ?>...
                                        </div>
                                        <a href="article.php?id=<?= $article['id_a'] ?>" class="lien_article">Lire plus</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Affichage d'une anecdote après certaines sections -->
                <?php if (isset($data['anecdote'])): ?>
                    <div class="anecdote">
                        <div class="titre_anecdote"><?= htmlspecialchars($data['anecdote']['nom']) ?></div>
                        <div class="texte_anecdote"><?= nl2br(htmlspecialchars($data['anecdote']['text'])) ?></div>
                        <div class="date_anecdote">
                            <?= date('d/m/Y', strtotime($data['anecdote']['date_'])) ?>
                            <?php if (!empty($data['anecdote']['tags'])): ?>
                                • <?= htmlspecialchars($data['anecdote']['tags']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="section">
                <h2 class="titre_section">Aucun contenu disponible</h2>
                <p style="color: #ecf0f1; text-align: center; font-size: 1.1rem;">
                    Les articles et anecdotes seront bientôt disponibles. Revenez nous voir !
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2025 Quais de Seine - Projet UNESCO</p>
        <div>
            <a href="mentions.html">Mentions Légales</a>
        </div>
    </footer>

    <script>
        // Fonctionnalité du carrousel
        let diapositive_actuelle = 0;
        const diapositives = document.querySelectorAll('.diapositive_carrousel');
        const nombre_diapositives = diapositives.length;

        function afficher_diapositive(index) {
            diapositives.forEach(diapositive => diapositive.classList.remove('active'));
            if (diapositives[index]) {
                diapositives[index].classList.add('active');
            }
        }

        function diapositive_suivante() {
            diapositive_actuelle = (diapositive_actuelle + 1) % nombre_diapositives;
            afficher_diapositive(diapositive_actuelle);
        }

        // Avancement automatique du carrousel toutes les 5 secondes
        if (nombre_diapositives > 1) {
            setInterval(diapositive_suivante, 5000);
        }

        // Animation au scroll
        function animateOnScroll() {
    const sections = document.querySelectorAll('.section, .resultats_recherche, .anecdote');
    const windowHeight = window.innerHeight;
    
    sections.forEach((section, index) => {
        const sectionTop = section.getBoundingClientRect().top;
        
        if (sectionTop < windowHeight - 100) {
            section.style.opacity = '1';
            // Suppression des transformations qui causaient les problèmes
        }
    });
}


        // Event listeners
        window.addEventListener('scroll', animateOnScroll);
        document.addEventListener('DOMContentLoaded', animateOnScroll);

        // Parallax effect pour le hero

        // Hover effects pour les cartes
        document.querySelectorAll('.carte_article').forEach(carte => {
            carte.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            carte.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>