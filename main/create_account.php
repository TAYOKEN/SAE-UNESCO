<?php


// Configuration de la base de données
$host = 'localhost';
$port = '5432';
$dbname = 'unesco'; 
$username = 'postgres'; 
$password = '2606'; 

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

// Fonction pour créer un compte
function createAccount($pdo, $email, $password, $pseudo, $role) {
    try {
        // Vérifier si l'email existe déjà
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE mail = :email");
        $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $checkStmt->execute();
        
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception("Un compte avec cet email existe déjà.");
        }
        
        // Vérifier si le pseudo existe déjà
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE pseudo = :pseudo");
        $checkStmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $checkStmt->execute();
        
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception("Un compte avec ce pseudo existe déjà.");
        }
        
        // Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insérer le nouveau compte
        $stmt = $pdo->prepare("
            INSERT INTO comptes (mail, mdp, pseudo, role) 
            VALUES (:email, :password, :pseudo, :role)
        ");
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        
        $stmt->execute();
        
        return "Compte créé avec succès pour $email";
        
    } catch (Exception $e) {
        throw new Exception("Erreur lors de la création du compte : " . $e->getMessage());
    }
}

// Fonction pour mettre à jour les mots de passe existants
function updateExistingPasswords($pdo) {
    try {
        $accounts = [
            ['email' => 'admin@quaisdeseine.fr', 'password' => 'admin123'],
            ['email' => 'gestionnaire1@quaisdeseine.fr', 'password' => 'gest123'],
            ['email' => 'gestionnaire2@quaisdeseine.fr', 'password' => 'gest456']
        ];
        
        foreach ($accounts as $account) {
            $hashedPassword = password_hash($account['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE comptes SET mdp = :password WHERE mail = :email");
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':email', $account['email'], PDO::PARAM_STR);
            $stmt->execute();
            
            echo "Mot de passe mis à jour pour : " . $account['email'] . "\n";
        }
        
        return "Tous les mots de passe ont été mis à jour avec succès.";
        
    } catch (Exception $e) {
        throw new Exception("Erreur lors de la mise à jour : " . $e->getMessage());
    }
}

// Interface en ligne de commande
function showMenu() {
    echo "\n=== GESTION DES COMPTES QUAIS DE SEINE ===\n";
    echo "1. Créer un nouveau compte\n";
    echo "2. Mettre à jour les mots de passe existants\n";
    echo "3. Afficher tous les comptes\n";
    echo "4. Générer un hash pour un mot de passe\n";
    echo "5. Quitter\n";
    echo "Choisissez une option (1-5) : ";
}

function listAccounts($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_compte, mail, pseudo, role, date_creation, derniere_connexion FROM comptes ORDER BY id_compte");
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n=== LISTE DES COMPTES ===\n";
        printf("%-5s %-30s %-20s %-12s %-20s %-20s\n", 
               "ID", "Email", "Pseudo", "Rôle", "Créé le", "Dernière connexion");
        echo str_repeat("-", 110) . "\n";
        
        foreach ($accounts as $account) {
            printf("%-5s %-30s %-20s %-12s %-20s %-20s\n",
                   $account['id_compte'],
                   $account['mail'],
                   $account['pseudo'],
                   $account['role'],
                   $account['date_creation'] ? date('d/m/Y H:i', strtotime($account['date_creation'])) : 'N/A',
                   $account['derniere_connexion'] ? date('d/m/Y H:i', strtotime($account['derniere_connexion'])) : 'Jamais'
            );
        }
        echo "\n";
        
    } catch (Exception $e) {
        echo "Erreur lors de l'affichage des comptes : " . $e->getMessage() . "\n";
    }
}

function generatePasswordHash() {
    echo "Entrez le mot de passe à hasher : ";
    $password = trim(fgets(STDIN));
    
    if (empty($password)) {
        echo "Mot de passe vide !\n";
        return;
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Hash généré : $hash\n";
    echo "Vous pouvez utiliser ce hash dans une requête SQL :\n";
    echo "UPDATE comptes SET mdp = '$hash' WHERE mail = 'email@example.com';\n\n";
}

// Script principal
try {
    $pdo = connectDB($host, $port, $dbname, $username, $password);
    echo "Connexion à la base de données réussie !\n";
    
    while (true) {
        showMenu();
        $choice = trim(fgets(STDIN));
        
        switch ($choice) {
            case '1':
                echo "\n=== CRÉATION D'UN NOUVEAU COMPTE ===\n";
                
                echo "Email : ";
                $email = trim(fgets(STDIN));
                
                echo "Mot de passe : ";
                $password = trim(fgets(STDIN));
                
                echo "Pseudo : ";
                $pseudo = trim(fgets(STDIN));
                
                echo "Rôle (gestionnaire/admin) : ";
                $role = trim(fgets(STDIN));
                
                // Validation
                if (empty($email) || empty($password) || empty($pseudo) || empty($role)) {
                    echo "Tous les champs sont obligatoires !\n";
                    break;
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo "Format d'email invalide !\n";
                    break;
                }
                
                if (!in_array($role, ['gestionnaire', 'admin'])) {
                    echo "Rôle invalide ! Utilisez 'gestionnaire' ou 'admin'\n";
                    break;
                }
                
                try {
                    echo createAccount($pdo, $email, $password, $pseudo, $role) . "\n";
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }
                break;
                
            case '2':
                echo "\n=== MISE À JOUR DES MOTS DE PASSE EXISTANTS ===\n";
                echo "Attention : Cette action va mettre à jour les mots de passe des comptes de démonstration.\n";
                echo "Continuer ? (o/n) : ";
                $confirm = trim(fgets(STDIN));
                
                if (strtolower($confirm) === 'o' || strtolower($confirm) === 'oui') {
                    try {
                        echo updateExistingPasswords($pdo) . "\n";
                    } catch (Exception $e) {
                        echo $e->getMessage() . "\n";
                    }
                } else {
                    echo "Opération annulée.\n";
                }
                break;
                
            case '3':
                listAccounts($pdo);
                break;
                
            case '4':
                echo "\n=== GÉNÉRATION DE HASH ===\n";
                generatePasswordHash();
                break;
                
            case '5':
                echo "Au revoir !\n";
                exit(0);
                
            default:
                echo "Option invalide. Veuillez choisir entre 1 et 5.\n";
        }
        
        echo "\nAppuyez sur Entrée pour continuer...";
        fgets(STDIN);
    }
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
?>