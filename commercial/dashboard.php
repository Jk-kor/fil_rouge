<?php
require_once '../config/init.php';
$requiredRole = 'commercial';
require_once '../config/auth.php'; // Vérifie que l'utilisateur est commercial ou admin

$pdo = Database::getInstance();

// Récupérer les annonces du commercial connecté
$stmt = $pdo->prepare("SELECT * FROM biens WHERE commercial_id = ? ORDER BY date_creation DESC");
$stmt->execute([$_SESSION['user_id']]);
$mesBiens = $stmt->fetchAll();

// Statistiques simples
$nbBiens = count($mesBiens);
$nbVues = array_sum(array_column($mesBiens, 'vue_count'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord commercial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <h1>Espace commercial</h1>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Mes annonces</h5>
                        <p class="card-text display-6"><?= $nbBiens ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Vues totales</h5>
                        <p class="card-text display-6"><?= $nbVues ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <h2>Mes annonces</h2>
            <a href="ajout.php" class="btn btn-success">➕ Ajouter une annonce</a>
        </div>

        <?php if (empty($mesBiens)): ?>
            <p class="alert alert-info">Vous n'avez encore aucune annonce.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Prix</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th>Vues</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mesBiens as $b): ?>
                    <tr>
                        <td><?= $b['id'] ?></td>
                        <td><?= htmlspecialchars($b['titre']) ?></td>
                        <td><?= number_format($b['prix'], 0, ',', ' ') ?> €</td>
                        <td><?= htmlspecialchars($b['ville']) ?></td>
                        <td>
                            <?php if ($b['statut'] === 'disponible'): ?>
                                <span class="badge bg-success">Disponible</span>
                            <?php elseif ($b['statut'] === 'reserve'): ?>
                                <span class="badge bg-warning">Réservé</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Vendu</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $b['vue_count'] ?></td>
                        <td>
                            <a href="modifier.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">✏️</a>
                            <a href="supprimer.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette annonce ?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>