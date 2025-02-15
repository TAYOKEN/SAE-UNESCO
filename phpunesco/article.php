<?php
include 'config.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

function parseMedia($text) {
    // Remplacement des vidéos YouTube
    $text = preg_replace(
        '/\[youtube\](.*?)\[\/youtube\]/i',
        '<div class="video-container"><iframe width="560" height="315" src="$1" frameborder="0" allowfullscreen></iframe></div>',
        htmlspecialchars($text)
    );

    // Remplacement des images
    $text = preg_replace(
        '/\[image\](.*?)\[\/image\]/i',
        '<div class="image-container"><img src="$1" alt="Image de l\'article" class="content-image"></div>',
        $text
    );

    // Remplacement des titres
    $text = preg_replace(
        '/\[titre\](.*?)\[\/titre\]/i',
        '<h2 class="article-title">$1</h2>',
        $text
    );

    return nl2br($text);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $article ? htmlspecialchars($article['titre']) : 'Article non trouvé'; ?></title>
    <link rel="stylesheet" href="css/stylesheet.css">
</head>
<body>

<header>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
        </ul>
    </nav>
</header>

<section class="description-container">
    <?php if ($article) : ?>
        <h1><?= htmlspecialchars($article['titre']); ?></h1>
        
        <p><?= parseMedia($article['contenu']); ?></p>

    <?php else : ?>
        <p>Article introuvable.</p>
    <?php endif; ?>
</section>

<footer>
    <p>© 2025 Quais de Seine. Tous droits réservés.</p>
</footer>

</body>
</html>
