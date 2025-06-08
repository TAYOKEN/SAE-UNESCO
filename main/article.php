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
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
// Récupération de l'ID de l'article depuis l'URL
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Récupération de l'article avec les informations de l'anecdote et de l'utilisateur
$stmt = $pdo->prepare("
    SELECT a.*, an.nom as anecdote_nom, an.text as anecdote_text, an.date_ as anecdote_date, an.tags as anecdote_tags,
           u.nom as auteur_nom, u.role as auteur_role
    FROM articles a
    JOIN anecdote an ON a.id_ann = an.id_ann
    JOIN utilisateurs u ON a.id_u = u.id_u
    WHERE a.id_a = ?
");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'article n'existe pas, prendre le premier disponible ou créer un exemple
if (!$article) {
    // Créer un article d'exemple basé sur une anecdote existante
    $stmt = $pdo->prepare("
        SELECT an.*, u.nom as auteur_nom, u.role as auteur_role
        FROM anecdote an, utilisateurs u
        WHERE an.id_ann = 1 AND u.id_u = 1
    ");
    $stmt->execute();
    $anecdote_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($anecdote_data) {
        $article = [
            'id_a' => $article_id,
            'image_miniature' => 'https://images.unsplash.com/photo-1502602898536-47ad22581b52?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'nom' => $anecdote_data['nom'],
            'date_creation' => date('Y-m-d H:i:s'),
            'text' => "Cet article explore en détail " . strtolower($anecdote_data['nom']) . ". " . $anecdote_data['text'] . "\n\nParis regorge d'histoires fascinantes qui témoignent de son riche passé. Chaque pierre, chaque rue, chaque monument raconte une partie de l'histoire de cette ville extraordinaire.\n\nLes traditions parisiennes se perpétuent à travers les siècles, créant un lien unique entre le passé et le présent. Ces récits nous permettent de mieux comprendre l'âme de Paris et son évolution au fil du temps.",
            'tags' => $anecdote_data['tags'],
            'anecdote_nom' => $anecdote_data['nom'],
            'anecdote_text' => $anecdote_data['text'],
            'anecdote_date' => $anecdote_data['date_'],
            'anecdote_tags' => $anecdote_data['tags'],
            'auteur_nom' => $anecdote_data['auteur_nom'],
            'auteur_role' => $anecdote_data['auteur_role']
        ];
    }
}

// Fonction pour récupérer des anecdotes aléatoires
function getRandomAnecdotes($pdo, $limit = 3) {
    $stmt = $pdo->prepare("SELECT * FROM anecdote ORDER BY RANDOM() LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer des anecdotes avec des tags similaires
function getSimilarAnecdotes($pdo, $tags, $current_id = null, $limit = 3) {
    $tag_array = array_map('trim', explode(',', str_replace('#', '', $tags)));
    $tag_conditions = [];
    $params = [];
    
    foreach ($tag_array as $tag) {
        $tag_conditions[] = "tags ILIKE ?";
        $params[] = "%$tag%";
    }
    
    $where_clause = implode(' OR ', $tag_conditions);
    $exclude_clause = $current_id ? "AND id_ann != ?" : "";
    if ($current_id) $params[] = $current_id;
    $params[] = $limit;
    
    $stmt = $pdo->prepare("
        SELECT * FROM anecdote 
        WHERE ($where_clause) $exclude_clause
        ORDER BY RANDOM() 
        LIMIT ?
    ");
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des anecdotes pour les sections
$random_anecdotes = getRandomAnecdotes($pdo, 3);
$similar_anecdotes = [];
$similar_articles = [];

if ($article) {
    $similar_anecdotes = getSimilarAnecdotes($pdo, $article['anecdote_tags'], $article['id_a'], 3);
    
    // Articles similaires (simulation car la table articles est vide)
    $similar_articles = [
        [
            'nom' => 'Les Secrets des Ponts Parisiens',
            'tags' => '#architecture #histoire',
            'auteur_nom' => 'Pierre Martin',
            'image_miniature' => 'https://images.unsplash.com/photo-1549813069-f95e44d7f498?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'
        ],
        [
            'nom' => 'Les Jardins Secrets de Paris',
            'tags' => '#nature #culture',
            'auteur_nom' => 'Sophie Laurent',
            'image_miniature' => 'https://images.unsplash.com/photo-1524396309943-e03f5249f002?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article ? $article['nom'] : 'Article'); ?> - MCN</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #333333;
        }

        /* Header */
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

        .nav-links a:hover, .nav-links a.active {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
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

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }

        .mobile-menu-btn span {
            width: 25px;
            height: 3px;
            background: white;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        /* Article Header */
        .article-header {
            background: linear-gradient(135deg, #505050, #555555);
            padding: 40px 20px;
            display: flex;
            align-items: center;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }

        .article-image {
            width: 300px;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            transition: transform 0.3s ease;
        }

        .article-image:hover {
            transform: scale(1.05);
        }

        .article-info {
            flex: 1;
            color: #ecf0f1;
        }

        .article-title {
            font-size: 3rem;
            font-weight: bold;
            color: #F7AF3E;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-icon {
            color: #EA5C0D;
            font-size: 1.2rem;
        }

        .article-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tag {
            background: linear-gradient(45deg, #EA5C0D, #F7AF3E);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        /* Main Content */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .article-content {
            background: linear-gradient(135deg, #505050, #555555);
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: #ecf0f1;
            font-size: 1.2rem;
            line-height: 1.8;
        }

        .article-content p {
            margin-bottom: 20px;
            text-align: justify;
        }

        /* Anecdote Boxes */
        .anecdote-box {
            background: linear-gradient(135deg, #EA5C0D, #F7AF3E);
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
            color: white;
            box-shadow: 0 8px 25px rgba(234, 92, 13, 0.3);
            position: relative;
            overflow: hidden;
        }

        .anecdote-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            pointer-events: none;
        }

        .anecdote-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .anecdote-text {
            font-size: 1.1rem;
            line-height: 1.6;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        .anecdote-date {
            font-style: italic;
            opacity: 0.9;
            margin-top: 10px;
        }

        /* Sections */
        .section {
            margin-bottom: 50px;
        }

        .section-title {
            color: #F7AF3E;
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }

        .section-title::after {
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

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .card {
            background: linear-gradient(135deg, #505050, #555555);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            border-left: 4px solid #F7AF3E;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(247,175,62,0.2);
        }

        .card-title {
            color: #F7AF3E;
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .card-content {
            color: #ecf0f1;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(247,175,62,0.3);
        }

        .card-author {
            color: #F7AF3E;
            font-weight: 500;
        }

        .card-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .card-tag {
            background: rgba(234, 92, 13, 0.3);
            color: #F7AF3E;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            border: 1px solid rgba(247,175,62,0.5);
        }

        .article-card {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .article-card img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .article-card-content {
            flex: 1;
        }

        /* Footer */
        .footer {
            background: #505050;
            color: #ecf0f1;
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
        }

        .footer a {
            color: #F7AF3E;
            text-decoration: none;
            margin: 0 15px;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: #EA5C0D;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: 80px;
                left: -100%;
                flex-direction: column;
                background: #EA5C0D;
                width: 100%;
                padding: 20px 0;
                transition: left 0.3s ease;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            }

            .nav-links.active {
                left: 0;
            }

            .mobile-menu-btn {
                display: flex;
            }

            .search-container {
                display: none;
            }

            .article-header {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
            }

            .article-image {
                width: 100%;
                max-width: 400px;
            }

            .article-title {
                font-size: 2rem;
            }

            .main-container {
                padding: 20px;
            }

            .article-content {
                padding: 25px;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .article-card {
                flex-direction: column;
                text-align: center;
            }

            .article-card img {
                width: 100%;
                height: 150px;
            }
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .fade-in:nth-child(1) { animation-delay: 0.1s; }
        .fade-in:nth-child(2) { animation-delay: 0.2s; }
        .fade-in:nth-child(3) { animation-delay: 0.3s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Pulse animation for anecdote boxes */
        .anecdote-box {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .article-link {
    text-decoration: none;
    color: inherit;
    display: block;
    transition: all 0.3s ease;
}

.article-link:hover {
    transform: translateY(-5px);
}

.article-link .card {
    cursor: pointer;
}

.article-link:hover .card-title {
    color: #EA5C0D;
}
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <img src="../Images/logo.png" alt="MCN Logo" onerror="this.src='https://images.unsplash.com/photo-1549813069-f95e44d7f498?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80'">
                MCN
            </a>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="glossaire.html">GLOSSAIRE</a></li>
                    <li><a href="itineraires.html">ITINÉRAIRES</a></li>
                    <li><a href="histoire.html">HISTOIRE</a></li>
                </ul>
            </nav>

            <div class="search-container">
                <input type="text" class="search-box" placeholder="Rechercher..." id="searchInput">
                <button class="search-btn" onclick="performSearch()">🔍</button>
            </div>

            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <?php if ($article): ?>
    <!-- Article Header -->
    <div class="article-header">
        <img src="<?php echo htmlspecialchars($article['image_miniature']); ?>" 
             alt="<?php echo htmlspecialchars($article['nom']); ?>" 
             class="article-image"
             onerror="this.src='https://images.unsplash.com/photo-1502602898536-47ad22581b52?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'">
        
        <div class="article-info">
            <h1 class="article-title"><?php echo htmlspecialchars($article['nom']); ?></h1>
            
            <div class="article-meta">
                <div class="meta-item">
                    <span class="meta-icon">👤</span>
                    <span><?php echo htmlspecialchars($article['auteur_nom']); ?> - <?php echo htmlspecialchars($article['auteur_role']); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">📅</span>
                    <span><?php echo date('d/m/Y', strtotime($article['date_creation'])); ?></span>
                </div>
            </div>
            
            <div class="article-tags">
                <?php 
                $tags = array_filter(array_map('trim', explode(',', str_replace('#', '', $article['tags']))));
                foreach($tags as $tag): 
                ?>
                    <span class="tag">#<?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Article Content -->
        <div class="article-content fade-in">
            <?php 
            $paragraphs = explode("\n\n", $article['text']);
            $total_paragraphs = count($paragraphs);
            $anecdote_positions = [];
            
            // Déterminer les positions pour insérer les anecdotes
            if ($total_paragraphs > 2) {
                $anecdote_positions = [
                    floor($total_paragraphs * 0.3),
                    floor($total_paragraphs * 0.6),
                    $total_paragraphs - 1
                ];
            }
            
            foreach ($paragraphs as $index => $paragraph):
                echo "<p>" . nl2br(htmlspecialchars($paragraph)) . "</p>";
                
                // Insérer une anecdote aléatoire à certaines positions
                if (in_array($index, $anecdote_positions) && !empty($random_anecdotes)):
                    $random_anecdote = array_shift($random_anecdotes);
            ?>
                    <div class="anecdote-box">
                        <div class="anecdote-title">💡 Le saviez-vous ?</div>
                        <div class="anecdote-text">
                            <strong><?php echo htmlspecialchars($random_anecdote['nom']); ?></strong><br>
                            <?php echo htmlspecialchars($random_anecdote['text']); ?>
                        </div>
                        <div class="anecdote-date">
                            📅 <?php echo date('d/m/Y', strtotime($random_anecdote['date_'])); ?>
                        </div>
                    </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>

        <!-- Similar Anecdotes Section -->
        <?php if (!empty($similar_anecdotes)): ?>
        <section class="section">
            <h2 class="section-title">Anecdotes Similaires</h2>
            <div class="cards-grid">
                <?php foreach ($similar_anecdotes as $anecdote): ?>
                <div class="card fade-in">
                    <h3 class="card-title"><?php echo htmlspecialchars($anecdote['nom']); ?></h3>
                    <div class="card-content">
                        <?php echo htmlspecialchars(substr($anecdote['text'], 0, 200)) . '...'; ?>
                    </div>
                    <div class="card-meta">
                        <span class="card-author">📅 <?php echo date('d/m/Y', strtotime($anecdote['date_'])); ?></span>
                        <div class="card-tags">
                            <?php 
                            $anecdote_tags = array_filter(array_map('trim', explode(',', str_replace('#', '', $anecdote['tags']))));
                            foreach(array_slice($anecdote_tags, 0, 2) as $tag): 
                            ?>
                                <span class="card-tag">#<?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Similar Articles Section -->
        <?php if (!empty($similar_articles)): ?>
        <section class="section">
            <h2 class="section-title">Articles Connexes</h2>
            <div class="cards-grid">
                <?php foreach ($similar_articles as $related_article): ?>
                <div class="card fade-in">
    <a href="article.php?id=<?php echo ($index + 2); ?>" class="article-link">
        <div class="article-card">
            <img src="<?php echo htmlspecialchars($related_article['image_miniature']); ?>" 
                 alt="<?php echo htmlspecialchars($related_article['nom']); ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1502602898536-47ad22581b52?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80'">
            <div class="article-card-content">
                <h3 class="card-title"><?php echo htmlspecialchars($related_article['nom']); ?></h3>
                <div class="card-meta">
                    <span class="card-author">Par <?php echo htmlspecialchars($related_article['auteur_nom']); ?></span>
                    <div class="card-tags">
                        <?php 
                        $related_tags = array_filter(array_map('trim', explode(',', str_replace('#', '', $related_article['tags']))));
                        foreach(array_slice($related_tags, 0, 2) as $tag): 
                        ?>
                            <span class="card-tag">#<?php echo htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="main-container">
        <div class="article-content">
            <h1 style="color: #F7AF3E; text-align: center; font-size: 2rem; margin-bottom: 20px;">Article non trouvé</h1>
            <p style="text-align: center; font-size: 1.2rem;">L'article demandé n'existe pas ou n'est pas disponible.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2025 MCN - Mémoires et Culture Numérique</p>
        <div>
            <a href="../mentions.html">Mentions Légales</a>
        </div>
    </footer>

    <script>
// Mobile menu toggle
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    
    navLinks.classList.toggle('active');
    mobileBtn.classList.toggle('active');
}

// Search functionality
function performSearch() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    if (searchTerm) {
        alert(`Recherche pour: "${searchTerm}"`);
    }
}

// Search on Enter key
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performSearch();
    }
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    const navLinks = document.querySelector('.nav-links');
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const header = document.querySelector('.header');
    
    if (!header.contains(e.target) && navLinks.classList.contains('active')) {
        navLinks.classList.remove('active');
        mobileBtn.classList.remove('active');
    }
});

</script>