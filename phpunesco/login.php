<?php
session_start(); // Démarre la session pour garder une trace de l'utilisateur connecté
include 'config.php'; // Connexion à la base de données

// Vérifie si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Rechercher l'utilisateur dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE nom_utilisateur = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Vérifie si le mot de passe entré correspond au mot de passe haché dans la base de données
        if (password_verify($password, $user['mot_de_passe'])) {
            // Le mot de passe est correct, l'utilisateur peut se connecter
            $_SESSION['username'] = $username; // Sauvegarde le nom d'utilisateur dans la session
            header("Location: ajouter.php"); // Redirige vers la page de création d'articles
            exit();
        } else {
            // Le mot de passe est incorrect
            $error = "Mot de passe incorrect.";
        }
    } else {
        // Si l'utilisateur n'existe pas
        $error = "Utilisateur non trouvé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
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
    <h2 class="text-center">Se connecter</h2>

    <!-- Affichage des erreurs de connexion -->
    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <!-- Formulaire de connexion -->
    <form method="post" class="shadow p-4 bg-light rounded">
        <div class="mb-3">
            <label class="form-label" for="username">Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
    </form>

    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-secondary">Retour à l'accueil</a>
    </div>
</div>

</body>
</html>
