<?php
include 'config.php';

$sql = "SELECT id, titre, contenu FROM articles ORDER BY date_publication DESC";
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quais de Seine</title>
    <link rel="stylesheet" href="css/stylesheet.css">
</head>
<body>

<header>
    <div class="header-images">
        <img src="Images/logo.png" alt="Logo">
    </div>
    <nav>
    <ul>
        <li><a href="index.html">Accueil</a></li>
        <li><a href="contact.html">Contact</a></li>
        <li class="dropdown">
          <a href="#">Activités <span class="arrow">&#9660;</span></a>
          <ul class="sub-menu">
            <li><a href="#">Musées à Paris</a></li>
            <li><a href="#">Promenades sur la Seine</a></li>
            <li><a href="#">Événements à Paris</a></li>
            <li><a href="#">Balades à vélo</a></li>
          </ul>
        </li>
        </ul>
    </nav>
    <div class="search-bar">
        <input type="text" placeholder="Recherche...">
    </div>
</header>

<section>
    <h1>Derniers articles</h1>
    <?php while ($article = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
        <div class="description-container">
            <h2><?= htmlspecialchars($article['titre']); ?></h2>
            <?php if (!empty($article['image'])) : ?>
                <img src="<?= htmlspecialchars($article['image']); ?>" alt="Image de l'article" class="description-image">
            <?php endif; ?>
            <p><?= nl2br(htmlspecialchars(substr($article['contenu'], 0, 200))) . '...'; ?></p>
            <a href="article.php?id=<?= $article['id']; ?>">Lire la suite</a>
        </div>
    <?php endwhile; ?>
</section>

<footer>
    <p>Yanis BOUKAYOUH <br>
    Issam BEN HAMOUDA<br>
  David LE<br>
  Piyush BHATT<br>
  Charles SUSINI
</p>
    <img src="Images/Unesco.png" alt="Description" style="float: right; ; height: 50px; width: auto; margin-right: 10px">
    <img src="Images/iut.png" alt="Description" style="float: right; ; height: 50px; width: auto; margin-right: 10px">

  </footer>
</body>
</html>

</body>
</html>
