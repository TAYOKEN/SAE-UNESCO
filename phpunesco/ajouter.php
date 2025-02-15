<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];

    if (!empty($titre) && !empty($contenu)) {
        $stmt = $pdo->prepare("INSERT INTO articles (titre, contenu) VALUES (?, ?)");
        $stmt->execute([$titre, $contenu]);
        header("Location: index.php");
        exit();
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un article</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<header>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
        </ul>
    </nav>
</header>

<div class="container mt-5">
    <h2 class="text-center">Ajouter un nouvel article</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="post" class="shadow p-4 bg-light rounded">
        <div class="mb-3">
            <label class="form-label">Titre</label>
            <input type="text" name="titre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contenu</label>
            <textarea name="contenu" class="form-control" rows="6" required></textarea>
            <small class="text-muted">
                🏷️ **Ajoutez des éléments dans votre contenu** : <br>
                🎥 **Vidéo YouTube** : `[youtube]https://www.youtube.com/embed/VIDEO_ID[/youtube]`<br>
                🖼️ **Image** : `[image]URL_DE_L_IMAGE[/image]`<br>
                🔹 **Titre** : `[titre]Mon titre[/titre]`
            </small>
        </div>

        <button type="submit" class="btn btn-primary w-100">Publier</button>
    </form>

    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-secondary">Retour</a>
    </div>
</div>

</body>
</html>
