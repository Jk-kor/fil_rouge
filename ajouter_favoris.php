<?php
// Fichier : ajouter_favoris.php
require_once 'config/init.php';

if (!Utilisateur::isLogged()) {
    header('Location: login.php');
    exit;
}

$bien_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = $_GET['action'] ?? 'add'; // 'add' ou 'remove'

if (!$bien_id) {
    header('Location: index.php');
    exit;
}

$pdo = Database::getInstance();
$user_id = $_SESSION['user_id'];

if ($action === 'add') {
    // Vérifier si déjà en favori
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE utilisateur_id = ? AND bien_id = ?");
    $stmt->execute([$user_id, $bien_id]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO favoris (utilisateur_id, bien_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $bien_id]);
    }
} elseif ($action === 'remove') {
    $stmt = $pdo->prepare("DELETE FROM favoris WHERE utilisateur_id = ? AND bien_id = ?");
    $stmt->execute([$user_id, $bien_id]);
}

// Rediriger vers la page précédente (détail ou favoris)
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $referer");
exit;