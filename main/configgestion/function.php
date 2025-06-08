<?php
function getAnecdotes($pdo, $search = '') {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM anecdote WHERE nom ILIKE ? OR text ILIKE ? OR tags ILIKE ? ORDER BY date_ DESC");
        $searchTerm = '%' . $search . '%';
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM anecdote ORDER BY date_ DESC");
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticles($pdo, $search = '') {
    if ($search) {
        $stmt = $pdo->prepare("SELECT a.*, u.nom as utilisateur_nom, u.role as utilisateur_role 
                             FROM articles a 
                             LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
                             WHERE a.nom ILIKE ? OR a.text ILIKE ? OR a.tags ILIKE ? 
                             ORDER BY a.date_creation DESC");
        $searchTerm = '%' . $search . '%';
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->prepare("SELECT a.*, u.nom as utilisateur_nom, u.role as utilisateur_role 
                             FROM articles a 
                             LEFT JOIN utilisateurs u ON a.id_u = u.id_u 
                             ORDER BY a.date_creation DESC");
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUtilisateurs($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs ORDER BY nom");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>