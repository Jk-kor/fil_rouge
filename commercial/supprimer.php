<?php
require_once '../config/init.php';
$requiredRole = 'commercial';
require_once '../config/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: dashboard.php');
    exit;
}

$bien = Bien::getById($id);
if (!$bien) {
    header('Location: dashboard.php');
    exit;
}

// Vérifier que le bien appartient bien au commercial connecté (ou admin)
$user = Utilisateur::getCurrentUser();
if ($bien->getCommercialId() != $_SESSION['user_id'] && !$user->hasRole('admin')) {
    header('Location: dashboard.php');
    exit;
}

// Supprimer les photos associées (physiquement et en BDD)
$pdo = Database::getInstance();
$photos = $bien->getPhotos();
foreach ($photos as $photo) {
    if (file_exists($photo['chemin'])) {
        unlink($photo['chemin']);
    }
}
// La suppression en BDD se fera automatiquement par ON DELETE CASCADE
$bien->delete();

header('Location: dashboard.php?deleted=1');
exit;