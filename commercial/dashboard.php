<?php
require_once '../config/init.php';
$requiredRole = 'commercial';
require_once '../config/auth.php';

$pdo = Database::getInstance();

// Récupérer les annonces du commercial
$stmt = $pdo->prepare("SELECT * FROM biens WHERE commercial_id = ? ORDER BY date_creation DESC");
$stmt->execute([$_SESSION['user_id']]);
$mesBiens = $stmt->fetchAll();

$nbBiens = count($mesBiens);
$nbVues = array_sum(array_column($mesBiens, 'vue_count'));
$nbDisponibles = count(array_filter($mesBiens, fn($b) => $b['statut'] === 'disponible'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace commercial - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold"><i class="fas fa-store me-2 text-primary"></i>Espace commercial</h1>
        <a href="ajout.php" class="btn btn-primary rounded-pill"><i class="fas fa-plus-circle me-1"></i> Nouvelle annonce</a>
    </div>

    <!-- Cartes statistiques -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
                <i class="fas fa-home fa-2x text-primary mb-2"></i>
                <h3 class="mb-0"><?= $nbBiens ?></h3>
                <p class="text-muted">Annonces postées</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
                <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                <h3 class="mb-0"><?= $nbVues ?></h3>
                <p class="text-muted">Vues totales</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
                <i class="fas fa-check-circle fa-2x text-warning mb-2"></i>
                <h3 class="mb-0"><?= $nbDisponibles ?></h3>
                <p class="text-muted">Disponibles</p>
            </div>
        </div>
    </div>

    <!-- Liste des annonces -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <h4 class="mb-0"><i class="fas fa-list me-2"></i>Mes annonces</h4>
        </div>
        <div class="card-body">
            <?php if (empty($mesBiens)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Vous n'avez encore aucune annonce.</p>
                    <a href="ajout.php" class="btn btn-outline-primary rounded-pill">Créer ma première annonce</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th><th>Titre</th><th>Prix</th><th>Ville</th><th>Statut</th><th>Vues</th><th>Actions</th>
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
                                        <span class="badge bg-success rounded-pill">Disponible</span>
                                    <?php elseif ($b['statut'] === 'reserve'): ?>
                                        <span class="badge bg-warning rounded-pill">Réservé</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill">Vendu</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $b['vue_count'] ?></td>
                                <td>
                                    <a href="modifier.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-edit"></i></a>
                                    <a href="supprimer.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Supprimer cette annonce ?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>