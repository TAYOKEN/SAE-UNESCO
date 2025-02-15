<?php
session_start(); // Démarre la session pour suivre l'utilisateur connecté
include 'config.php'; // Connexion à la base de données

// Vérifie si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Vérifie si le nom d'utilisateur existe déjà dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE nom_utilisateur = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Si l'utilisateur existe déjà, affiche une erreur
        $error = "Ce nom d'utilisateur est déjà pris.";
    } else {
        // Si l'utilisateur n'existe pas, hache le mot de passe
        $mot_de_passe_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insère le nouvel utilisateur dans la base de données
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe) VALUES (?, ?)");
        $stmt->execute([$username, $mot_de_passe_hash]);

        // Redirige l'utilisateur vers la page de connexion après l'inscription
        $_SESSION['username'] = $username;
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Créer un compte</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="shadow p-4 bg-light rounded">
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
    </form>

    <div class="text-center mt-3">
        <a href="login.php" class="btn btn-secondary">Déjà un compte ? Se connecter</a>
    </div>
</div>

</body>
</html>
