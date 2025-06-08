<?php
session_start();

// Configuration de la base de données
$host = 'localhost';
$port = '5432';
$dbname = 'unesco';
$username = 'postgres';
$password = '2606';

$message = '';
$error = '';

// Fonction de connexion à la base de données
function connectDB($host, $port, $dbname, $username, $password) {
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

if (!isset($_SESSION['id_compte']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $pseudo = trim($_POST['pseudo'] ?? '');
    $role = $_POST['role'] ?? '';
    
    // Validation
    if (empty($email) || empty($password_input) || empty($confirm_password) || empty($pseudo) || empty($role)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format d\'email invalide';
    } elseif ($password_input !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password_input) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } elseif (!in_array($role, ['gestionnaire', 'admin'])) {
        $error = 'Rôle invalide';
    } else {
        try {
            $pdo = connectDB($host, $port, $dbname, $username, $password);
            
            // Vérifier si l'email existe déjà
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE mail = :email");
            $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = 'Un compte avec cet email existe déjà';
            } else {
                // Vérifier si le pseudo existe déjà
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE pseudo = :pseudo");
                $checkStmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
                $checkStmt->execute();
                
                if ($checkStmt->fetchColumn() > 0) {
                    $error = 'Un compte avec ce pseudo existe déjà';
                } else {
                    // Hacher le mot de passe et créer le compte
                    $hashedPassword = password_hash($password_input, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO comptes (mail, mdp, pseudo, role) 
                        VALUES (:email, :password, :pseudo, :role)
                    ");
                    
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                    $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
                    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                    
                    $stmt->execute();
                    
                    $message = 'Compte créé avec succès !';
                    
                    // Réinitialiser le formulaire
                    $_POST = [];
                }
            }
            
        } catch (PDOException $e) {
            $error = 'Erreur lors de la création du compte : ' . $e->getMessage();
        }
    }
}

// Récupérer la liste des comptes existants
$accounts = [];
try {
    $pdo = connectDB($host, $port, $dbname, $username, $password);
    $stmt = $pdo->query("SELECT id_compte, mail, pseudo, role, date_creation FROM comptes ORDER BY id_compte DESC");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Ignorer les erreurs pour l'affichage
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de Compte - Quais de Seine</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #333333, #505050);
            min-height: 100vh;
            color: #333;
        }

        .header {
            background: #EA5C0D;
            padding: 20px 0;
            text-align: center;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .form-section, .accounts-section {
            background: #505050;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .section-title {
            color: #F7AF3E;
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #F7AF3E;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #6a6a6a;
            border-radius: 10px;
            background: #3a3a3a;
            color: #ecf0f1;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus, .form-select:focus {
            border-color: #F7AF3E;
            background: #4a4a4a;
            box-shadow: 0 0 0 3px rgba(247, 175, 62, 0.1);
        }

        .form-input::placeholder {
            color: #999;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            color: #999;
        }

        .password-strength.weak { color: #e74c3c; }
        .password-strength.medium { color: #f39c12; }
        .password-strength.strong { color: #27ae60; }

        .role-selector {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .role-option {
            flex: 1;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option label {
            display: block;
            padding: 12px 20px;
            background: #3a3a3a;
            border: 2px solid #6a6a6a;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #ecf0f1;
            margin-bottom: 0;
        }

        .role-option input[type="radio"]:checked + label {
            background: #F7AF3E;
            border-color: #F7AF3E;
            color: #333;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #EA5C0D, #F7AF3E);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(234, 92, 13, 0.4);
        }

        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .error {
            background: #e74c3c;
            color: white;
        }

        .success {
            background: #27ae60;
            color: white;
        }

        .accounts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .accounts-table th,
        .accounts-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #6a6a6a;
            color: #ecf0f1;
        }

        .accounts-table th {
            background: #3a3a3a;
            color: #F7AF3E;
            font-weight: 600;
        }

        .accounts-table tr:hover {
            background: #4a4a4a;
        }

        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-admin {
            background: #e74c3c;
            color: white;
        }

        .role-gestionnaire {
            background: #3498db;
            color: white;
        }

        .back-link {
            display: inline-block;
            margin: 20px;
            padding: 10px 20px;
            background: #F7AF3E;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: #EA5C0D;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }

            .form-section, .accounts-section {
                padding: 25px;
            }

            .role-selector {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gestion des Comptes - Quais de Seine</h1>
        <p>Interface d'administration</p>
    </div>

    <a href="login.php" class="back-link">← Retour à la connexion</a>

    <div class="container">
        <!-- Formulaire de création -->
        <div class="form-section">
            <h2 class="section-title">Créer un Compte</h2>

            <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="utilisateur@quaisdeseine.fr" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="pseudo">Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" class="form-input" 
                           placeholder="nom_utilisateur" 
                           value="<?php echo htmlspecialchars($_POST['pseudo'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Au moins 6 caractères" required>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                           placeholder="Répétez le mot de passe" required>
                </div>

                <div class="form-group">
                    <label>Rôle</label>
                    <div class="role-selector">
                        <div class="role-option">
                            <input type="radio" id="gestionnaire" name="role" value="gestionnaire" 
                                   <?php echo (($_POST['role'] ?? '') === 'gestionnaire' || empty($_POST['role'])) ? 'checked' : ''; ?>>
                            <label for="gestionnaire">Gestionnaire</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="admin" name="role" value="admin"
                                   <?php echo (($_POST['role'] ?? '') === 'admin') ? 'checked' : ''; ?>>
                            <label for="admin">Administrateur</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn">Créer le Compte</button>
            </form>
        </div>

        <!-- Liste des comptes -->
        <div class="accounts-section">
            <h2 class="section-title">Comptes Existants</h2>
            
            <?php if (empty($accounts)): ?>
                <p style="color: #ecf0f1; text-align: center;">Aucun compte trouvé</p>
            <?php else: ?>
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Pseudo</th>
                            <th>Rôle</th>
                            <th>Créé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['id_compte']); ?></td>
                            <td><?php echo htmlspecialchars($account['mail']); ?></td>
                            <td><?php echo htmlspecialchars($account['pseudo']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo htmlspecialchars($account['role']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($account['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($account['date_creation'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Vérification de la force du mot de passe
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                strengthDiv.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            const checks = [
                password.length >= 6,
                /[a-z]/.test(password),
                /[A-Z]/.test(password),
                /[0-9]/.test(password),
                /[^A-Za-z0-9]/.test(password)
            ];
            
            strength = checks.filter(Boolean).length;
            
            if (strength < 3) {
                strengthDiv.textContent = 'Mot de passe faible';
                strengthDiv.className = 'password-strength weak';
            } else if (strength < 4) {
                strengthDiv.textContent = 'Mot de passe moyen';
                strengthDiv.className = 'password-strength medium';
            } else {
                strengthDiv.textContent = 'Mot de passe fort';
                strengthDiv.className = 'password-strength strong';
            }
        });

        // Vérification de la correspondance des mots de passe
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '#6a6a6a';
            }
        });

        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas !');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères !');
                return false;
            }
        });
    </script>
</body>
</html>