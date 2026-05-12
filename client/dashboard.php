<?php
require_once '../config/init.php';
$requiredRole = 'client';
require_once '../config/auth.php';

$pdo = Database::getInstance();
$user = Utilisateur::getCurrentUser();

// Favoris
$stmt = $pdo->prepare("SELECT b.* FROM biens b JOIN favoris f ON b.id = f.bien_id WHERE f.utilisateur_id = ? ORDER BY f.date_ajout DESC");
$stmt->execute([$_SESSION['user_id']]);
$favoris = $stmt->fetchAll();

// Achats
$stmt = $pdo->prepare("SELECT a.*, b.titre, b.prix, b.id AS bien_id FROM achats a JOIN biens b ON a.bien_id = b.id WHERE a.utilisateur_id = ? ORDER BY a.date_achat DESC");
$stmt->execute([$_SESSION['user_id']]);
$achats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <!-- En-tête du profil -->
    <div class="row align-items-center mb-5">
        <div class="col-md-2 text-center text-md-start">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3">
                <i class="fas fa-user-circle fa-4x text-primary"></i>
            </div>
        </div>
        <div class="col-md-10">
            <h1 class="display-6 fw-bold mb-1"><?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?></h1>
            <p class="text-muted"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user->getEmail()) ?></p>
            <span class="badge bg-primary rounded-pill"><?= ucfirst($user->getRole()) ?></span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Favoris -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h4 class="mb-0"><i class="fas fa-heart text-danger me-2"></i>Mes favoris</h4>
                    <p class="text-muted small mt-1">Biens que vous avez mis en favori</p>
                </div>
                <div class="card-body">
                    <?php if (empty($favoris)): ?>
                        <div class="text-center py-5">
                            <i class="far fa-heart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Vous n'avez aucun favori pour le moment.</p>
                            <a href="/annonces.php" class="btn btn-outline-primary rounded-pill">Découvrir des annonces</a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($favoris as $bien): ?>
                            <a href="../details.php?id=<?= $bien['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($bien['titre']) ?></h6>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($bien['ville']) ?></small>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?= number_format($bien['prix'], 0, ',', ' ') ?> €</span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Historique des achats -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h4 class="mb-0"><i class="fas fa-shopping-cart text-success me-2"></i>Mes achats</h4>
                    <p class="text-muted small mt-1">Simulations d'achat effectuées</p>
                </div>
                <div class="card-body">
                    <?php if (empty($achats)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-store fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Vous n'avez encore rien acheté.</p>
                            <a href="/annonces.php" class="btn btn-outline-primary rounded-pill">Parcourir les annonces</a>
                        </div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($achats as $achat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($achat['titre']) ?></h6>
                                    <small class="text-muted">Acheté le <?= date('d/m/Y', strtotime($achat['date_achat'])) ?></small>
                                </div>
                                <span class="badge bg-success rounded-pill"><?= number_format($achat['montant'], 0, ',', ' ') ?> €</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>