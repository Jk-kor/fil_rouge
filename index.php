<?php
// Fichier : index.php
require_once 'config/init.php';

$biens = Bien::getAll(6); // 6 dernières annonces
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>ImmoApp - Votre agence immobilière en ligne</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- Hero section -->
<section class="hero bg-primary-gradient text-white text-center py-5">
    <div class="container py-5">
        <h1 class="display-4 fw-bold mb-3">Trouvez le bien de vos rêves</h1>
        <p class="lead mb-4">Des milliers d'annonces immobilières partout en France</p>
        <a href="annonces.php" class="btn btn-light btn-lg rounded-pill px-5">Découvrir nos annonces</a>
    </div>
</section>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">Nos dernières annonces</h2>
        <a href="annonces.php" class="text-primary text-decoration-none">Voir toutes <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
        <?php foreach ($biens as $bien): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card property-card h-100 border-0 shadow-sm">
                <?php 
                $photos = $bien->getPhotos();
                $image = !empty($photos) ? $photos[0]['chemin'] : 'https://placehold.co/600x400?text=Image+indisponible';
                ?>
                <div class="card-img-top-wrapper">
                    <img src="<?= htmlspecialchars($image) ?>" class="card-img-top" alt="<?= htmlspecialchars($bien->getTitre()) ?>" style="height: 220px; object-fit: cover; width: 100%;">
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($bien->getTitre()) ?></h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2"><?= ucfirst($bien->getType()) ?></span>
                    </div>
                    <div class="property-location text-muted small mb-2">
                        <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($bien->getVille()) ?>
                    </div>
                    <p class="property-price h4 text-primary fw-bold mt-2"><?= number_format($bien->getPrix(), 0, ',', ' ') ?> €</p>
                    <div class="property-features d-flex gap-3 mt-3 mb-3 text-muted small">
                        <span><i class="fas fa-chart-area"></i> <?= $bien->getSurface() ?> m²</span>
                        <span><i class="fas fa-bed"></i> <?= $bien->getPieces() ?> pièces</span>
                    </div>
                    <a href="details.php?id=<?= $bien->getId() ?>" class="btn btn-outline-primary w-100 rounded-pill">Voir détail <i class="fas fa-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>