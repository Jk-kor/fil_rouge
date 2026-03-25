<?php
// Fichier : admin/annonces.php
require_once '../config/init.php';
$requiredRole = 'admin';
require_once '../config/auth.php';

$pdo = Database::getInstance();

// Suppression d'une annonce
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $bien = Bien::getById($id);
    if ($bien) {
        // Supprimer les photos associées
        $photos = $bien->getPhotos();
        foreach ($photos as $photo) {
            if (file_exists($photo['chemin'])) {
                unlink($photo['chemin']);
            }
        }
        $bien->delete();
    }
    header('Location: annonces.php');
    exit;
}

// Récupérer toutes les annonces avec le nom du commercial
$stmt = $pdo->query("SELECT b.*, u.nom, u.prenom FROM biens b LEFT JOIN utilisateurs u ON b.commercial_id = u.id ORDER BY b.date_creation DESC");
$annonces = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des annonces</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <h1>Gestion des annonces</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Commercial</th>
                    <th>Prix</th>
                    <th>Ville</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($annonces as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['titre']) ?></td>
                    <td><?= htmlspecialchars(($a['prenom'] ?? '?') . ' ' . ($a['nom'] ?? '')) ?></td>
                    <td><?= number_format($a['prix'], 0, ',', ' ') ?> €</td>
                    <td><?= htmlspecialchars($a['ville']) ?></td>
                    <td>
                        <?php if ($a['statut'] === 'disponible'): ?>
                            <span class="badge bg-success">Disponible</span>
                        <?php elseif ($a['statut'] === 'reserve'): ?>
                            <span class="badge bg-warning">Réservé</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Vendu</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($a['date_creation'])) ?></td>
                    <td>
                        <a href="../commercial/modifier.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary">✏️</a>
                        <a href="annonces.php?delete=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette annonce ?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>