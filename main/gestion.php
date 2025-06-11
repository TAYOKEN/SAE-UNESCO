<?php
include 'configgestion/config.php';
include 'configgestion/function.php';
session_start();

$search = $_GET['search'] ?? '';
$anecdotes = getAnecdotes($pdo, $search);
$articles = getArticles($pdo, $search);
$utilisateurs = getUtilisateurs($pdo);

// Vérifier l'authentification
if (!isset($_SESSION['id_compte']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Traitement des actions AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'get_item':
                if ($_POST['type'] === 'article') {
                    $stmt = $pdo->prepare("SELECT a.*, u.nom as utilisateur_nom, u.role as utilisateur_role 
                         FROM articles a 
                         LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
                         WHERE a.id_a = ?");
                    $stmt->execute([$_POST['id']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM anecdote WHERE id_ann = ?");
                    $stmt->execute([$_POST['id']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                echo json_encode($result ?: []);
                break;

            case 'create_article':
    $stmt = $pdo->prepare("INSERT INTO articles (nom, text, tags, image_miniature, id_u, id_ann, date_creation, titre_eng, text_eng, tags_eng) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
    $result = $stmt->execute([
        $_POST['nom'],
        $_POST['text'],
        $_POST['tags'] ?: '',
        $_POST['image_miniature'] ?: '',
        $_POST['id_u'] ?: null,
        $_POST['id_ann'] ?: null,
        $_POST['titre_eng'] ?: '',
        $_POST['text_eng'] ?: '',
        $_POST['tags_eng'] ?: ''
    ]);
    echo json_encode(['success' => $result]);
    break;

case 'update_article':
    $stmt = $pdo->prepare("UPDATE articles SET nom = ?, text = ?, tags = ?, image_miniature = ?, id_u = ?, id_ann = ?, titre_eng = ?, text_eng = ?, tags_eng = ? WHERE id_a = ?");
    $result = $stmt->execute([
        $_POST['nom'],
        $_POST['text'],
        $_POST['tags'] ?: '',
        $_POST['image_miniature'] ?: '',
        $_POST['id_u'] ?: null,
        $_POST['id_ann'] ?: null,
        $_POST['titre_eng'] ?: '',
        $_POST['text_eng'] ?: '',
        $_POST['tags_eng'] ?: '',
        $_POST['id']
    ]);
    echo json_encode(['success' => $result]);
    break;

            case 'delete_article':
                $stmt = $pdo->prepare("DELETE FROM articles WHERE id_a = ?");
                $result = $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => $result]);
                break;
                
            case 'create_anecdote':
    $stmt = $pdo->prepare("INSERT INTO anecdote (nom, text, tags, date_, titre_eng, text_eng, tags_eng) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $_POST['nom'],
        $_POST['text'],
        $_POST['tags'],
        $_POST['date'] ?: date('Y-m-d'),
        $_POST['titre_eng'] ?: '',
        $_POST['text_eng'] ?: '',
        $_POST['tags_eng'] ?: ''
    ]);
    echo json_encode(['success' => $result]);
    break;

case 'update_anecdote':
    $stmt = $pdo->prepare("UPDATE anecdote SET nom = ?, text = ?, tags = ?, date_ = ?, titre_eng = ?, text_eng = ?, tags_eng = ? WHERE id_ann = ?");
    $result = $stmt->execute([
        $_POST['nom'],
        $_POST['text'],
        $_POST['tags'],
        $_POST['date'],
        $_POST['titre_eng'] ?: '',
        $_POST['text_eng'] ?: '',
        $_POST['tags_eng'] ?: '',
        $_POST['id']
    ]);

            case 'get_anecdotes_list':
                $stmt = $pdo->prepare("SELECT id_ann, nom FROM anecdote ORDER BY nom");
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($result);
                break;

            case 'delete_anecdote':
                $stmt = $pdo->prepare("DELETE FROM anecdote WHERE id_ann = ?");
                $result = $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => $result]);
                break;
                
            // Traitement de l'upload d'image
            case 'upload_image':
                // Vérifier si le dossier thumb existe, sinon le créer
                $thumbDir = '../thumb/';
                if (!is_dir($thumbDir)) {
                    mkdir($thumbDir, 0755, true);
                }
                
                // Vérifier si un fichier a été uploadé
                if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Erreur lors du téléchargement du fichier');
                }
                
                $file = $_FILES['image'];
                
                // Vérifier le type de fichier
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mimeType, $allowedTypes)) {
                    throw new Exception('Type de fichier non autorisé');
                }
                
                // Vérifier la taille (5MB max)
                if ($file['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Fichier trop volumineux (max 5MB)');
                }
                
                // Générer un nom unique pour le fichier
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (empty($extension)) {
                    // Déterminer l'extension basée sur le type MIME
                    $extensions = [
                        'image/jpeg' => 'jpg',
                        'image/jpg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp'
                    ];
                    $extension = $extensions[$mimeType] ?? 'jpg';
                }
                
                $filename = uniqid('img_') . '_' . time() . '.' . strtolower($extension);
                $filepath = $thumbDir . $filename;
                
                // Déplacer le fichier
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    throw new Exception('Erreur lors de la sauvegarde du fichier');
                }
                
                echo json_encode([
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath
                ]);
                break;

            case 'log_error':
                $logFile = 'debug.log';
                $timestamp = date('Y-m-d H:i:s');
                $message = $_POST['message'] ?? 'Message vide';
                $error = $_POST['error'] ?? '';
                
                $logContent = "[{$timestamp}] {$message}";
                if (!empty($error)) {
                    $logContent .= " | Erreur: {$error}";
                }
                $logContent .= "\n";
                
                file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
                echo json_encode(['success' => true]);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erreur de base de données: ' . $e->getMessage()
        ]);
    }
    
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion - Articles & Anecdotes</title>
    <link rel="stylesheet" href="configgestion/gestionstyle.css" />
    
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <img src="../Images/logo.png" alt="MCN Logo" onerror="this.src='https://images.unsplash.com/photo-1549813069-f95e44d7f498?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80'">
            </a>
            
            <nav>
    <ul class="nav-links">
        <li><a href="../glossaire.html">GLOSSAIRE</a></li>
        <li><a href="../itineraires.html">ITINÉRAIRES</a></li>
        <li><a href="../histoire.html">HISTOIRE</a></li>
        <li><a href="gestion.html" class="active">GESTION</a></li>
        <li>
            <span class="user-info">Connecté: <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn-logout">Déconnexion</a>
        </li>
    </ul>
</nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Gestion de Contenu</h1>
        <p>Gérez vos articles et anecdotes en toute simplicité</p>
    </section>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($articles) ?></div>
                <div class="stat-label">Articles</div>
            </div>
            <div class="stat-card">
            <div class="stat-number"><?= count($anecdotes) ?></div>
            <div class="stat-label">Anecdotes</div>
            </div>
        </div>

        

        <!-- Control Panel -->
        <div class="control-panel">
            <div class="search-section">
                <form method="GET" class="search-section">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
           placeholder="Rechercher des articles ou anecdotes..." class="search-input">
    <button type="submit" class="btn btn-secondary">Rechercher</button>
    <a href="?" class="btn btn-secondary">Effacer</a>
</form>
            </div>
            <div style="display: flex; gap: 15px;">
    <button class="btn btn-primary" id="btnNewArticle">
        ✚ Nouvel Article
    </button>
    <button class="btn btn-primary" id="btnNewAnecdote">
        ✚ Nouvelle Anecdote
    </button>
</div>
        </div>
<!-- Content Grid -->
        <div class="content-grid">
            <!-- Articles Section -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">Articles</h2>
                    <span class="btn btn-secondary" id="articlesTotal"><?= count($articles) ?> articles</span>
                </div>
                <div class="item-list" id="articlesList">
                    <?php if (empty($articles)): ?>
                        <div class="empty-state">
                            <div style="font-size: 3rem; margin-bottom: 20px;">📰</div>
                            <p>Aucun article trouvé. Créez votre premier article !</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                            
                            <div class="item fade-in">
                                <?php if (!empty($article['titre_eng']) && !empty($article['text_eng'])): ?>
                                <div class="item-meta">🇬🇧 Traduction disponible</div>
                            <?php endif; ?> 
                            <?php if (empty($article['titre_eng']) || empty($article['text_eng'])): ?>
                                <div class="item-meta">🇬🇧 Traduction non-disponible ⚠️</div>
                            <?php endif; ?> 
                                <div class="item-title"><?= htmlspecialchars($article['nom']) ?></div>
                                <div class="item-meta">
                                    Créé le: <?= date('d/m/Y', strtotime($article['date_creation'])) ?>
                                    <?php if (!empty($article['utilisateur_nom'])): ?>
                                        | Par: <?= htmlspecialchars($article['utilisateur_nom']) ?> (<?= htmlspecialchars($article['utilisateur_role']) ?>)
                                            <?php endif; ?>
                                </div>
                                <div class="item-text"><?= htmlspecialchars(substr($article['text'], 0, 150)) ?><?= strlen($article['text']) > 150 ? '...' : '' ?></div>
                                <?php if (!empty($article['tags'])): ?>
                                    <div class="item-tags"><?= htmlspecialchars($article['tags']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($article['image_miniature'])): ?>
                                    <div class="item-meta">🖼️ Image disponible</div>
                                <?php endif; ?>
                                <div class="item-actions">
                                    <button class="btn btn-edit" onclick="editItem('article', <?= $article['id_a'] ?>)">✏️ Modifier</button>
                                    <button class="btn btn-danger" onclick="deleteItem('article', <?= $article['id_a'] ?>)">🗑️ Supprimer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Anecdotes Section -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">Anecdotes</h2>
                    <span class="btn btn-secondary" id="anecdotesTotal"><?= count($anecdotes) ?> anecdotes</span>
                </div>
                <div class="item-list" id="anecdotesList">
                    <?php if (empty($anecdotes)): ?>
                        <div class="empty-state">
                            <div style="font-size: 3rem; margin-bottom: 20px;">📚</div>
                            <p>Aucune anecdote trouvée. Créez votre première anecdote !</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($anecdotes as $anecdote): ?>
                            <div class="item fade-in">
                                <div class="item-title"><?= htmlspecialchars($anecdote['nom']) ?></div>
                                <div class="item-meta">Date: <?= date('d/m/Y', strtotime($anecdote['date_'])) ?></div>
                                <div class="item-text"><?= htmlspecialchars(substr($anecdote['text'], 0, 150)) ?><?= strlen($anecdote['text']) > 150 ? '...' : '' ?></div>
                                <?php if (!empty($anecdote['tags'])): ?>
                                    <div class="item-tags"><?= htmlspecialchars($anecdote['tags']) ?></div>
                                <?php endif; ?>
                                <div class="item-actions">
                                    <button class="btn btn-edit" onclick="editItem('anecdote', <?= $anecdote['id_ann'] ?>)">✏️ Modifier</button>
                                    <button class="btn btn-danger" onclick="deleteItem('anecdote', <?= $anecdote['id_ann'] ?>)">🗑️ Supprimer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>


    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Nouvel Article</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="contentForm">
                <input type="hidden" id="itemId" value="">
                <input type="hidden" id="itemType" value="">
                
                <div class="form-group">
                    <label class="form-label" for="nom">Nom *</label>
                    <input type="text" class="form-input" id="nom" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="text">Contenu *</label>
                    <textarea class="form-input form-textarea" id="text" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="tags">Tags</label>
                    <input type="text" class="form-input" id="tags" placeholder="Ex: #histoire #culture #architecture">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="date">Date</label>
                    <input type="date" class="form-input" id="date">
                </div>
                
                <!-- Champs spécifiques aux articles -->
                <div id="articleFields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="image_miniature">Image miniature</label>
                        <input type="text" class="form-input" id="image_miniature" placeholder="URL de l'image">
                    </div>
                <div class="form-group">
    <h3 style="margin-top: 20px; color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px;">🇬🇧 Version Anglaise </h3>
</div>

<div class="form-group">
    <label class="form-label" for="titre_eng">Titre en anglais</label>
    <input type="text" class="form-input" id="titre_eng" placeholder="English title">
</div>

<div class="form-group">
    <label class="form-label" for="text_eng">Contenu en anglais</label>
    <textarea class="form-input form-textarea" id="text_eng" placeholder="English content"></textarea>
</div>

<div class="form-group">
    <label class="form-label" for="tags_eng">Tags en anglais</label>
    <input type="text" class="form-input" id="tags_eng" placeholder="Ex: #history #culture #architecture">
</div>
                    
                    <div class="form-group">
                        <label class="form-label" for="utilisateur">Utilisateur</label>
                        <select class="form-select" id="utilisateur">
                            <option value="1">Marie Dubois - Historienne</option>
                            <option value="2">Pierre Martin - Guide touristique</option>
                            <option value="3">Sophie Laurent - Photographe</option>
                            <option value="4">Jean Rousseau - Architecte</option>
                            <option value="5">Claire Moreau - Journaliste culturelle</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="anecdote_liee">Anecdote liée</label>
                        <select class="form-select" id="anecdote_liee">
                            <option value="">Sélectionner une anecdote</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

<script>

    // Système de logging
function logError(message, error = null) {
    const timestamp = new Date().toISOString();
    const logMessage = `[${timestamp}] ${message}`;
    
    if (error) {
        console.error(logMessage, error);
    } else {
        console.log(logMessage);
    }
    
    // Envoyer au serveur pour logging
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=log_error&message=${encodeURIComponent(logMessage)}&error=${encodeURIComponent(error ? error.toString() : '')}`
    }).catch(e => console.error('Erreur lors de l\'envoi du log:', e));
}

// Logging des erreurs globales
window.addEventListener('error', function(e) {
    logError(`Erreur JavaScript: ${e.message} à la ligne ${e.lineno}`, e.error);
});

// Logging des erreurs de promesses non gérées
window.addEventListener('unhandledrejection', function(e) {
    logError('Promesse rejetée non gérée:', e.reason);
});
let currentEditItem = null;

// Modal functions
function openModal(type, item = null) {
    try {
        logError(`Tentative d'ouverture du modal - Type: ${type}, Item: ${item}`);
        
        const modal = document.getElementById('modal');
        const form = document.getElementById('contentForm');
        const articleFields = document.getElementById('articleFields');
        const modalTitle = document.getElementById('modalTitle');
        
        // Vérifier que tous les éléments existent (POUR LE DEBUG)
        if (!modal) {
            logError('ERREUR: Élément modal non trouvé');
            return;
        }
        if (!form) {
            logError('ERREUR: Élément contentForm non trouvé');
            return;
        }
        if (!articleFields) {
            logError('ERREUR: Élément articleFields non trouvé');
            return;
        }
        if (!modalTitle) {
            logError('ERREUR: Élément modalTitle non trouvé');
            return;
        }
        
        logError('Tous les éléments DOM trouvés, reset du formulaire');
        
        form.reset();
        currentEditItem = item;
        
        // Modals par rapport à l'elem qu'on modif
        if (type === 'article') {
            modalTitle.textContent = item ? 'Modifier l\'article' : 'Nouvel Article';
            articleFields.style.display = 'block';
            logError('Chargement des anecdotes pour le select');
            loadAnecdotesForSelect();
        } else {
            modalTitle.textContent = item ? 'Modifier l\'anecdote' : 'Nouvelle Anecdote';
            articleFields.style.display = 'none';
        }
        
        document.getElementById('itemType').value = type;
        
        if (item) {
            logError(`Chargement des données pour l'item ${item}`);
            loadItemData(type, item);
        }
        
        logError('Ajout de la classe show au modal');
        modal.classList.add('show');
        logError('Modal ouvert avec succès');
        
    } catch (error) {
        logError('ERREUR dans openModal:', error);
    }
}

function closeModal() {
    document.getElementById('modal').classList.remove('show');
    currentEditItem = null;
}

function loadItemData(type, id) {
    logError(`loadItemData appelée - Type: ${type}, ID: ${id}`);
    
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=get_item&type=${type}&id=${id}`
    })
    .then(response => {
        logError(`Réponse reçue pour loadItemData - Status: ${response.status}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        logError('Données reçues pour loadItemData:', JSON.stringify(data));
        
        if (data && Object.keys(data).length > 0) {
            document.getElementById('itemId').value = type === 'article' ? data.id_a : data.id_ann;
            document.getElementById('nom').value = data.nom || '';
            document.getElementById('text').value = data.text || '';
            document.getElementById('tags').value = data.tags || '';            
            document.getElementById('titre_eng').value = data.titre_eng || '';
            document.getElementById('text_eng').value = data.text_eng || '';
            document.getElementById('tags_eng').value = data.tags_eng || '';
            
            if (type === 'article') {
                document.getElementById('image_miniature').value = data.image_miniature || '';
                document.getElementById('utilisateur').value = data.id_u || '';
                document.getElementById('anecdote_liee').value = data.id_ann || '';
                if (data.date_creation) {
                    const dateOnly = data.date_creation.split(' ')[0];
                    document.getElementById('date').value = dateOnly;
                }
            } else {
                document.getElementById('date').value = data.date_ || '';
            }
            logError('Données chargées avec succès dans le formulaire');
        } else {
            logError('ERREUR: Aucune donnée reçue ou données vides');
            alert('Erreur: Aucune donnée trouvée pour cet élément');
        }
    })
    .catch(error => {
        logError('ERREUR dans loadItemData:', error);
        alert('Erreur lors du chargement des données: ' + error.message);
    });
}
document.getElementById('contentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const type = document.getElementById('itemType').value;
    const id = document.getElementById('itemId').value;
    
    // Validation basique
    const nom = document.getElementById('nom').value.trim();
    const text = document.getElementById('text').value.trim();
    
    if (!nom || !text) {
        alert('Veuillez remplir tous les champs obligatoires');
        return;
    }
    
    const params = new URLSearchParams();
    
    const action = id ? `update_${type}` : `create_${type}`;
    params.append('action', action);
    
    if (id) params.append('id', id);
    params.append('nom', nom);
    params.append('text', text);
    params.append('tags', document.getElementById('tags').value.trim());
    
    // Ajouter les champs anglais
    params.append('titre_eng', document.getElementById('titre_eng').value.trim());
    params.append('text_eng', document.getElementById('text_eng').value.trim());
    params.append('tags_eng', document.getElementById('tags_eng').value.trim());
    
    if (type === 'article') {
        params.append('image_miniature', document.getElementById('image_miniature').value.trim());
        const userId = document.getElementById('utilisateur').value;
        const anecdoteId = document.getElementById('anecdote_liee').value;
        params.append('id_u', userId || '');
        params.append('id_ann', anecdoteId || '');
    } else {
        const dateValue = document.getElementById('date').value || new Date().toISOString().split('T')[0];
        params.append('date', dateValue);
    }
    
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
        } else {
            alert('Erreur lors de la sauvegarde: ' + (data.error || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la sauvegarde: ' + error.message);
    });
});


// Edit item
function editItem(type, id) {
    openModal(type, id);
}

// Delete item  
function deleteItem(type, id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
        const formData = new FormData();
        formData.append('action', `delete_${type}`);
        formData.append('id', id);
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la suppression');
        });
    }
}

// Close modal when clicking outside
document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Fonction pour charger les anecdotes dans le select
function loadAnecdotesForSelect() {
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_anecdotes_list'
    })
    .then(response => response.json())
    .then(anecdotes => {
        const select = document.getElementById('anecdote_liee');
        if (select) {
            select.innerHTML = '<option value="">Sélectionner une anecdote </option>';
            anecdotes.forEach(anecdote => {
                select.innerHTML += `<option value="${anecdote.id_ann}">${anecdote.nom}</option>`;
            });
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des anecdotes:', error);
    });
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page chargée, initialisation...');
});

// Gestion des erreurs globales
window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
});

document.addEventListener('DOMContentLoaded', function() {
    logError('DOM chargé, initialisation des gestionnaires d\'événements');
    
    // Correction 1: Utiliser les IDs définis dans le HTML
    const btnNewArticle = document.getElementById('btnNewArticle');
    if (btnNewArticle) {
        btnNewArticle.addEventListener('click', function() {
            logError('Clic sur bouton Nouvel Article');
            openModal('article');
        });
    } else {
        logError('ERREUR: Bouton Nouvel Article non trouvé');
    }
    
    const btnNewAnecdote = document.getElementById('btnNewAnecdote');
    if (btnNewAnecdote) {
        btnNewAnecdote.addEventListener('click', function() {
            logError('Clic sur bouton Nouvelle Anecdote');
            openModal('anecdote');
        });
    } else {
        logError('ERREUR: Bouton Nouvelle Anecdote non trouvé');
    }
    
    logError('Gestionnaires d\'événements initialisés');
});
</script>
