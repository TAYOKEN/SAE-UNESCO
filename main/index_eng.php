<?php
// Configuration de la base de données PostgreSQL
$serveur = 'localhost';
$titre_eng_base = 'unesco';
$titre_eng_utilisateur = 'postgres';
$mot_de_passe = '2606';
$port = '5432';

try {
    $pdo = new PDO("pgsql:host=$serveur;port=$port;dbname=$titre_eng_base", $titre_eng_utilisateur, $mot_de_passe);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
$requete = $pdo->prepare("
    SELECT a.*, an.titre_eng as anecdote, u.nom as auteur 
    FROM articles a 
    LEFT JOIN anecdote an ON a.id_ann = an.id_ann 
    LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
    ORDER BY a.date_creation DESC 
    LIMIT 5
");
$requete->execute();
$articles_carrousel = $requete->fetchAll(PDO::FETCH_ASSOC);

// Récupération des tags_eng les plus populaires
$requete_tags_eng = $pdo->prepare("
    SELECT tags_eng, COUNT(*) as count 
    FROM articles 
    WHERE tags_eng IS NOT NULL AND tags_eng != '' 
    GROUP BY tags_eng
    ORDER BY count DESC 
    LIMIT 10
");
$requete_tags_eng->execute();
$tags_eng_populaires = $requete_tags_eng->fetchAll(PDO::FETCH_ASSOC);

// Récupération des articles par tags_eng populaires
$articles_par_tag = [];
$anecdotes_affichees = [];

foreach ($tags_eng_populaires as $tag_info) {
    $tag = $tag_info['tags_eng'];
    
    // Récupérer les articles pour ce tag
$requete = $pdo->prepare("
    SELECT a.*, an.titre_eng as titre_eng_anecdote, an.text_eng as text_enge_anecdote, u.nom as titre_eng_auteur 
    FROM articles a 
    LEFT JOIN anecdote an ON a.id_ann = an.id_ann 
    LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
    WHERE a.tags_eng ILIKE ? 
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
    WHERE an.tags_eng ILIKE ? 
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
    SELECT a.*, an.titre_eng as titre_eng_anecdote, u.nom as titre_eng_auteur 
    FROM articles a 
    LEFT JOIN anecdote an ON a.id_ann = an.id_ann 
    LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
    WHERE a.titre_eng ILIKE ? OR a.text_eng ILIKE ? OR a.tags_eng ILIKE ?
    ORDER BY a.date_creation DESC
");
    $requete->execute(['%' . $terme_recherche . '%', '%' . $terme_recherche . '%', '%' . $terme_recherche . '%']);
    $resultats_recherche = $requete->fetchAll(PDO::FETCH_ASSOC);
}

function getImageLink($image_name) {
    if (empty($image_name)) {
        return "https://via.placeholder.com/400x300/EA5C0D/FFFFFF?text_eng=UNESCO+Image";
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
    return "https://via.placeholder.com/400x300/EA5C0D/FFFFFF?text_eng=UNESCO+Image";
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

html, body {
    margin: 0;
    padding: 0;
    height: 100%;
}

body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #333333;
}

        .header {
            background: #EA5C0D;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.5em;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
            border-radius: 8px;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 20px;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        /* Language Selector */
        .language-selector {
            position: relative;
            margin-left: 20px;
        }

        .lang-toggle {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 25px;
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            font-weight: 500;
            text-decoration: none;
        }

        .lang-toggle:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.4);
            transform: scale(1.05);
        }

        .lang-flag {
            width: 20px;
            height: 15px;
            margin-right: 8px;
            border-radius: 2px;
            background-size: cover;
            background-position: center;
        }

        .flag-fr {
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjEiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMSAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMTUiIGZpbGw9IiMwMDI2NTQiLz4KPHJlY3QgeD0iNyIgd2lkdGg9IjciIGhlaWdodD0iMTUiIGZpbGw9IndoaXRlIi8+CjxyZWN0IHg9IjE0IiB3aWR0aD0iNyIgaGVpZ2h0PSIxNSIgZmlsbD0iI0VGMTkyMCIvPgo8L3N2Zz4K');
        }

        .flag-en {
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjEiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMSAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIxIiBoZWlnaHQ9IjE1IiBmaWxsPSIjMDEyMTY5Ii8+CjxwYXRoIGQ9Ik0wIDBoMjF2MUgweiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTAgMmgyMXYxSDB6IiBmaWxsPSIjQ0UxMTI0Ii8+CjxwYXRoIGQ9Ik0wIDRoMjF2MUgweiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTAgNmgyMXYxSDB6IiBmaWxsPSIjQ0UxMTI0Ii8+CjxwYXRoIGQ9Ik0wIDhoMjF2MUgweiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTAgMTBoMjF2MUgweiIgZmlsbD0iI0NFMTEyNCIvPgo8cGF0aCBkPSJNMCAxMmgyMXYxSDB6IiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMCAxNGgyMXYxSDB6IiBmaWxsPSIjQ0UxMTI0Ii8+CjxyZWN0IHdpZHRoPSI5IiBoZWlnaHQ9IjgiIGZpbGw9IiMwMTIxNjkiLz4KPC9zdmc+Cg==');
        }


        .search-container {
            position: relative;
        }

        .search-box {
            padding: 10px 40px 10px 15px;
            border: none;
            border-radius: 25px;
            width: 250px;
            font-size: 14px;
            outline: none;
            transition: width 0.3s ease;
        }

        .search-box:focus {
            width: 300px;
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #EA5C0D;
        }

/* Hero Section avec carrousel */
.hero {
    height: 500px;
    overflow: hidden;
    margin-bottom: 0;
}

.conteneur_carrousel {
    position: relative;
    height: 100%;
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
    display: flex;
    align-items: center;
    justify-content: center;
}

.diapositive_carrousel.active {
    opacity: 1;
}

.diapositive_carrousel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(234,92,13,0.6), rgba(247,175,62,0.4));
}

.superposition_carrousel {
    position: absolute;
    bottom: 40px;
    left: 40px;
    background: rgba(0,0,0,0.8);
    padding: 30px;
    border-radius: 15px;
    max-width: 500px;
    z-index: 1;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
}

.titre_carrousel {
    color: #F7AF3E;
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 10px;
    text_eng-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.description_carrousel {
    color: #ecf0f1;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 15px;
}

.auteur_carrousel {
    color: #F7AF3E;
    font-size: 0.9rem;
    font-style: italic;
}

/* Main Content */
.main-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* Section Titles */
.titre_section {
    color: #F7AF3E;
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 30px;
    position: relative;
    text_eng-align: center;
}

.titre_section::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, #EA5C0D, #F7AF3E);
    border-radius: 2px;
}

/* Articles Grid */
.section {
    background: linear-gradient(135deg, #505050, #555555);
    padding: 40px;
    border-radius: 15px;
    margin-bottom: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.grille_articles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
}

.carte_article {
    background: linear-gradient(135deg, #404040, #454545);
    border-radius: 15px;
    overflow: hidden;
    display: flex;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    border-left: 4px solid #F7AF3E;
}

.carte_article:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(247,175,62,0.2);
}

.image_article {
    width: 150px;
    height: 150px;
    background-size: cover;
    background-position: center;
    flex-shrink: 0;
    position: relative;
}

.image_article::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(234,92,13,0.2), rgba(247,175,62,0.2));
}

.contenu_article {
    padding: 25px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.titre_article {
    color: #F7AF3E;
    font-size: 1.3rem;
    font-weight: bold;
    margin-bottom: 12px;
    text_eng-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.description_article {
    color: #ecf0f1;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 15px;
    flex-grow: 1;
}

.meta_article {
    color: #bdc3c7;
    font-size: 0.85rem;
    margin-bottom: 15px;
}

.lien_article {
    color: #EA5C0D;
    text_eng-decoration: none;
    font-size: 0.95rem;
    font-weight: bold;
    padding: 8px 16px;
    border: 2px solid #EA5C0D;
    border-radius: 25px;
    transition: all 0.3s ease;
    align-self: flex-start;
}

.lien_article:hover {
    background: #EA5C0D;
    color: white;
    transform: translateX(5px);
}

/* Anecdotes */
.anecdote {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    padding: 30px;
    border-radius: 15px;
    margin: 40px 0;
    border-left: 5px solid #F7AF3E;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.titre_anecdote {
    color: #F7AF3E;
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.titre_anecdote::before {
    content: "💡";
    margin-right: 10px;
    font-size: 1.2rem;
}

.text_enge_anecdote {
    color: #ecf0f1;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 10px;
}

.date_anecdote {
    color: #bdc3c7;
    font-size: 0.9rem;
    font-style: italic;
}

/* Search Results */
.resultats_recherche {
    background: linear-gradient(135deg, #505050, #555555);
    padding: 40px;
    border-radius: 15px;
    margin-bottom: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.resultats_recherche h3 {
    color: #F7AF3E;
    font-size: 2rem;
    margin-bottom: 25px;
    text_eng-align: center;
}

.resultats_recherche h3::after {
    content: '';
    display: block;
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #EA5C0D, #F7AF3E);
    margin: 10px auto 0;
    border-radius: 2px;
}

/* Footer */
.footer {
    background: #505050;
    color: #ecf0f1;
    text_eng-align: center;
    padding: 30px 0;
    margin-top: 50px;
}

.footer a {
    color: #F7AF3E;
    text_eng-decoration: none;
    margin: 0 15px;
    transition: color 0.3s ease;
}

.footer a:hover {
    color: #EA5C0D;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        gap: 15px;
        padding: 15px;
    }

    .nav-links {
        gap: 20px;
    }

    .search-container {
        width: 100%;
    }

    .search-box {
        width: 100%;
    }

    .hero {
        height: 300px;
    }

    .superposition_carrousel {
        left: 20px;
        right: 20px;
        bottom: 20px;
        max-width: none;
        padding: 20px;
    }

    .main-container {
        padding: 20px;
    }

    .grille_articles {
        grid-template-columns: 1fr;
    }

    .carte_article {
        flex-direction: column;
    }

    .image_article {
        width: 100%;
        height: 200px;
    }

    .section, .resultats_recherche {
        padding: 20px;
    }

    .titre_section {
        font-size: 2rem;
    }
}

html {
    scroll-behavior: smooth;
}
    </style>
</head>
<body>
        <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index_eng.php" class="logo">
                <img src="../Images/logo.png" alt="Logo" onerror="this.src='https://images.unsplash.com/photo-1549813069-f95e44d7f498?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80'">
            </a>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="../glossaire_eng.html">GLOSSARY</a></li>
                    <li><a href="../itineraires_eng.html">ITINERARIES</a></li>
                    <li><a href="../histoire_eng.html" class="active">HISTORY</a></li>
                </ul>
            </nav>
        
           <div class="search-container">
                <input type="text" class="search-box" placeholder="Search..." id="searchInput">
                <button class="search-btn" onclick="performSearch()">🔍</button>
            </div>

            <!-- Language Selector -->
            <div class="language-selector">
                <a href="glossaire.html" class="lang-toggle" title="Basculer vers le français">
                    <div class="lang-flag flag-fr"></div>
                    <span>FR</span>
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
                            <div class="titre_carrousel"><?= htmlspecialchars($article['titre_eng']) ?></div>
                            <div class="description_carrousel">
                                <?= htmlspecialchars(substr($article['text_eng'], 0, 200)) ?>...
                            </div>
                            <?php if (!empty($article['titre_eng_auteur'])): ?>
                                <div class="auteur_carrousel">Par <?= htmlspecialchars($article['titre_eng_auteur']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="diapositive_carrousel active" style="background-image: url('https://via.placeholder.com/1200x500/EA5C0D/FFFFFF?text_eng=Aucun+Article+Disponible');">
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
                                <div class="titre_article"><?= htmlspecialchars($article['titre_eng']) ?></div>
                                <div class="meta_article">
                                    <?php if (!empty($article['titre_eng_auteur'])): ?>
                                        Par <?= htmlspecialchars($article['titre_eng_auteur']) ?> • 
                                    <?php endif; ?>
                                    <?= date('d/m/Y', strtotime($article['date_creation'])) ?>
                                    <?php if (!empty($article['tags_eng'])): ?>
                                        • <span style="color: #F7AF3E;"><?= htmlspecialchars($article['tags_eng']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="description_article">
                                    <?= htmlspecialchars(substr($article['text_eng'], 0, 150)) ?>...
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
                                        <div class="titre_article"><?= htmlspecialchars($article['titre_eng']) ?></div>
                                        <div class="meta_article">
                                            <?php if (!empty($article['titre_eng_auteur'])): ?>
                                                Par <?= htmlspecialchars($article['titre_eng_auteur']) ?> • 
                                            <?php endif; ?>
                                            <?= date('d/m/Y', strtotime($article['date_creation'])) ?>
                                        </div>
                                        <div class="description_article">
                                            <?= htmlspecialchars(substr($article['text_eng'], 0, 120)) ?>...
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
                        <div class="titre_anecdote"><?= htmlspecialchars($data['anecdote']['titre_eng']) ?></div>
                        <div class="text_enge_anecdote"><?= nl2br(htmlspecialchars($data['anecdote']['text_eng'])) ?></div>
                        <div class="date_anecdote">
                            <?= date('d/m/Y', strtotime($data['anecdote']['date_'])) ?>
                            <?php if (!empty($data['anecdote']['tags_eng'])): ?>
                                • <?= htmlspecialchars($data['anecdote']['tags_eng']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="section">
                <h2 class="titre_section">Aucun contenu disponible</h2>
                <p style="color: #ecf0f1; text_eng-align: center; font-size: 1.1rem;">
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
        const titre_engbre_diapositives = diapositives.length;

        function afficher_diapositive(index) {
            diapositives.forEach(diapositive => diapositive.classList.remove('active'));
            if (diapositives[index]) {
                diapositives[index].classList.add('active');
            }
        }

        function diapositive_suivante() {
            diapositive_actuelle = (diapositive_actuelle + 1) % titre_engbre_diapositives;
            afficher_diapositive(diapositive_actuelle);
        }

        // Avancement automatique du carrousel toutes les 5 secondes
        if (titre_engbre_diapositives > 1) {
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