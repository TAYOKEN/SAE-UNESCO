<?php
// Ajouter une anecdote
if ($_POST['action'] === 'add_anecdote') {
    $sql = "INSERT INTO anecdote (nom, text, date_, tags) VALUES (:nom, :text, :date, :tags)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom' => $_POST['nom'],
        ':text' => $_POST['text'],
        ':date' => $_POST['date'],
        ':tags' => $_POST['tags']
    ]);
}

// Ajouter un article
if ($_POST['action'] === 'add_article') {
    $sql = "INSERT INTO articles (image_miniature, nom, date_creation, text, tags, id_ann, id_u) 
            VALUES (:image, :nom, NOW(), :text, :tags, :id_ann, :id_u)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':image' => $_POST['image_miniature'],
        ':nom' => $_POST['nom'],
        ':text' => $_POST['text'],
        ':tags' => $_POST['tags'],
        ':id_ann' => $_POST['anecdote_liee'] ?: null,
        ':id_u' => $_POST['utilisateur']
    ]);
}

// Supprimer
if ($_POST['action'] === 'delete') {
    $table = $_POST['type'] === 'article' ? 'articles' : 'anecdote';
    $id_field = $_POST['type'] === 'article' ? 'id_a' : 'id_ann';
    
    $sql = "DELETE FROM $table WHERE $id_field = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_POST['id']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_article':
            $stmt = $pdo->prepare("INSERT INTO articles (nom, text, tags, image_miniature, id_u, id_ann, date_creation) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([
                $_POST['nom'],
                $_POST['text'], 
                $_POST['tags'],
                $_POST['image_miniature'] ?? '',
                $_POST['id_u'],
                $_POST['id_ann'] ?? null
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'update_article':
            $stmt = $pdo->prepare("UPDATE articles SET nom=?, text=?, tags=?, image_miniature=?, id_u=?, id_ann=? WHERE id_a=?");
            $result = $stmt->execute([
                $_POST['nom'],
                $_POST['text'],
                $_POST['tags'], 
                $_POST['image_miniature'] ?? '',
                $_POST['id_u'],
                $_POST['id_ann'] ?? null,
                $_POST['id']
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'delete_article':
            $stmt = $pdo->prepare("DELETE FROM articles WHERE id_a = ?");
            $result = $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'create_anecdote':
            $stmt = $pdo->prepare("INSERT INTO anecdote (nom, text, tags, date_) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([
                $_POST['nom'],
                $_POST['text'],
                $_POST['tags'],
                $_POST['date'] ?? date('Y-m-d')
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'update_anecdote':
            $stmt = $pdo->prepare("UPDATE anecdote SET nom=?, text=?, tags=?, date_=? WHERE id_ann=?");
            $result = $stmt->execute([
                $_POST['nom'],
                $_POST['text'],
                $_POST['tags'],
                $_POST['date'],
                $_POST['id']
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'delete_anecdote':
            $stmt = $pdo->prepare("DELETE FROM anecdote WHERE id_ann = ?");
            $result = $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'get_item':
            $type = $_POST['type'];
            $id = $_POST['id'];
            
            if ($type === 'article') {
                $stmt = $pdo->prepare("SELECT * FROM articles WHERE id_a = ?");
                $stmt->execute([$id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM anecdote WHERE id_ann = ?");
                $stmt->execute([$id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            echo json_encode($item);
            exit;
    }
}


?>