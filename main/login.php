<?php
session_start();
// Configuration de la base de données
$host = 'localhost';
$port = '5432';
$dbname = 'unesco';
$username = 'postgres';
$password = '2606';
$error_message = '';
$success_message = '';

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

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $remember = isset($_POST['remember']);
   
    if (empty($email) || empty($password_input) || empty($role)) {
        $error_message = 'Tous les champs sont obligatoires';
    } else {
        try {
            $pdo = connectDB($host, $port, $dbname, $username, $password);
           
            // Requête pour vérifier les identifiants
            $stmt = $pdo->prepare("
                SELECT id_compte, mail, mdp, pseudo, role
                FROM comptes
                WHERE mail = :email AND role = :role
            ");
           
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();
           
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
           
            if ($user && password_verify($password_input, $user['mdp'])) {
                // Connexion réussie - Utilisation de noms cohérents pour les variables de session
                $_SESSION['id_compte'] = $user['id_compte'];        // Cohérent avec la vérification
                $_SESSION['user_id'] = $user['id_compte'];          // Alias pour compatibilité
                $_SESSION['username'] = $user['pseudo'];            // Pour l'affichage dans gestion.php
                $_SESSION['user_email'] = $user['mail'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
               
                // Mise à jour de la dernière connexion
                $update_stmt = $pdo->prepare("
                    UPDATE comptes
                    SET derniere_connexion = CURRENT_TIMESTAMP
                    WHERE id_compte = :id
                ");
                $update_stmt->bindParam(':id', $user['id_compte'], PDO::PARAM_INT);
                $update_stmt->execute();
               
                // Gestion du "Se souvenir de moi"
                if ($remember) {
                    setcookie('user_email', $email, time() + (30 * 24 * 60 * 60), '/'); // 30 jours
                }
               
                $success_message = 'Connexion réussie ! Redirection...';
               
                // Redirection selon le rôle
                $redirect_url = ($role === 'admin') ? 'gestion.php' : 'gestion.php';
                header("refresh:2;url=$redirect_url");
               
            } else {
                $error_message = 'Email, mot de passe ou type de compte incorrect';
            }
           
        } catch (PDOException $e) {
            $error_message = 'Erreur de connexion : ' . $e->getMessage();
        }
    }
}

// Traitement de la demande de réinitialisation du mot de passe
if (isset($_GET['forgot']) && !empty($_GET['email'])) {
    $email = trim($_GET['email']);
    // Ici vous pourriez implémenter la logique d'envoi d'email
    $success_message = "Un email de réinitialisation a été envoyé à $email";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Quais de Seine</title>
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
            background: linear-gradient(135deg, #333333, #505050);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            background: #EA5C0D;
            padding: 15px 0;
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

        /* Main Content */
        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-container {
            background: #505050;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #EA5C0D, #F7AF3E, #EA5C0D);
            background-size: 200% 100%;
            animation: shimmer 2s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { background-position: -200% 0; }
            50% { background-position: 200% 0; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h1 {
            color: #F7AF3E;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-header p {
            color: #ecf0f1;
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #F7AF3E;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .form-input {
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

        .form-input:focus {
            border-color: #F7AF3E;
            background: #4a4a4a;
            box-shadow: 0 0 0 3px rgba(247, 175, 62, 0.1);
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: #999;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #F7AF3E;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #EA5C0D;
        }

        .role-selector {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .role-option {
            flex: 1;
            position: relative;
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
        }

        .role-option input[type="radio"]:checked + label {
            background: #F7AF3E;
            border-color: #F7AF3E;
            color: #333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(247, 175, 62, 0.3);
        }

        .role-option label:hover {
            border-color: #F7AF3E;
            background: #4a4a4a;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ecf0f1;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #F7AF3E;
        }

        .forgot-password {
            color: #F7AF3E;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #EA5C0D;
            text-decoration: underline;
        }

        .login-btn {
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
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(234, 92, 13, 0.4);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .error-message {
            background: #e74c3c;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .success-message {
            background: #27ae60;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: #999;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #6a6a6a;
        }

        .divider span {
            background: #505050;
            padding: 0 20px;
            position: relative;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #F7AF3E;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #EA5C0D;
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background: #505050;
            color: #ecf0f1;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
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
                display: none;
            }

            .login-container {
                padding: 30px 25px;
                margin: 20px;
            }

            .login-header h1 {
                font-size: 2rem;
            }

            .role-selector {
                flex-direction: column;
                gap: 10px;
            }

            .remember-forgot {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .header-container {
                padding: 0 15px;
            }

            .login-container {
                padding: 25px 20px;
            }

            .login-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.html" class="logo">
                <img src="../Images/logo.png" alt="Logo" onerror="this.src='https://images.unsplash.com/photo-1549813069-f95e44d7f498?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80'">
            </a>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="glossaire.html">GLOSSAIRE</a></li>
                    <li><a href="itineraires.html">ITINÉRAIRES</a></li>
                    <li><a href="histoire.html">HISTOIRE</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <div class="login-container">
            <div class="login-header">
                <h1>Connexion</h1>
                <p>Accédez à votre espace de gestion</p>
            </div>

            <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="votre@email.com" 
                           value="<?php echo isset($_COOKIE['user_email']) ? htmlspecialchars($_COOKIE['user_email']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Type de compte</label>
                    <div class="role-selector">
                        <div class="role-option">
                            <input type="radio" id="gestionnaire" name="role" value="gestionnaire" checked>
                            <label for="gestionnaire">Gestionnaire</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="admin" name="role" value="admin">
                            <label for="admin">Administrateur</label>
                        </div>
                    </div>
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="forgot-password" onclick="forgotPassword()">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="login-btn">
                    Se connecter
                </button>
            </form>

            <div class="divider">
                <span>ou</span>
            </div>

            <div class="register-link">
                <p>Pas encore de compte ? <a href="#" onclick="requestAccount()">Demander un accès</a></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2025 Quais de Seine - Projet UNESCO</p>
        <div>
            <a href="mentions.html">Mentions Légales</a>
            <a href="contact.html">Contact</a>
        </div>
    </footer>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = '👁️';
            }
        }

        // Forgot password function
        function forgotPassword() {
            const email = document.getElementById('email').value;
            
            if (!email) {
                alert('Veuillez saisir votre adresse email avant de cliquer sur "Mot de passe oublié"');
                document.getElementById('email').focus();
                return;
            }
            
            window.location.href = `?forgot=1&email=${encodeURIComponent(email)}`;
        }

        // Request account function
        function requestAccount() {
            alert('Votre demande d\'accès sera traitée sous 24h. Vous recevrez un email de confirmation.');
        }

        // Enhanced form validation
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '#6a6a6a';
            }
        });

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            if (password.length > 0 && password.length < 6) {
                this.style.borderColor = '#f39c12';
            } else if (password.length >= 6) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#6a6a6a';
            }
        });
    </script>
</body>
</html>