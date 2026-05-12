<?php
// Fichier : details.php
require_once 'config/init.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

$bien = Bien::getById($id);
if (!$bien) {
    header('Location: index.php');
    exit;
}

$bien->incrementVue();

$pdo = Database::getInstance();
$stmt = $pdo->prepare("INSERT INTO logs_consultation (bien_id, utilisateur_id) VALUES (?, ?)");
$stmt->execute([$id, $_SESSION['user_id'] ?? null]);

$photos = $bien->getPhotos();

$estFavori = false;
if (Utilisateur::isLogged()) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE utilisateur_id = ? AND bien_id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    $estFavori = $stmt->fetchColumn() > 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($bien->getTitre()) ?> - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Accueil</a></li>
            <li class="breadcrumb-item"><a href="annonces.php" class="text-decoration-none">Annonces</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($bien->getTitre()) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Colonne images -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <?php if (!empty($photos)): ?>
                    <div id="carouselDetails" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($photos as $index => $photo): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= htmlspecialchars($photo['chemin']) ?>" class="d-block w-100" alt="Photo du bien" style="max-height: 500px; object-fit: cover;">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselDetails" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselDetails" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                    </div>
                <?php else: ?>
                    <img src="https://placehold.co/800x500?text=Aucune+image" class="w-100" alt="Image indisponible">
                <?php endif; ?>
            </div>
        </div>

        <!-- Colonne infos -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <h1 class="h2 mb-3"><?= htmlspecialchars($bien->getTitre()) ?></h1>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2"><?= ucfirst($bien->getType()) ?></span>
                </div>
                <div class="mb-3 text-muted">
                    <i class="fas fa-map-marker-alt me-2 text-primary"></i> <?= htmlspecialchars($bien->getAdresse() ?: $bien->getVille()) ?>, <?= htmlspecialchars($bien->getCodePostal()) ?> - <?= htmlspecialchars($bien->getVille()) ?>
                </div>
                <div class="row g-3 my-3">
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-3 text-center">
                            <i class="fas fa-chart-area fa-2x text-primary mb-2"></i>
                            <h5 class="mb-0"><?= $bien->getSurface() ?> m²</h5>
                            <small>Surface</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-3 text-center">
                            <i class="fas fa-bed fa-2x text-primary mb-2"></i>
                            <h5 class="mb-0"><?= $bien->getPieces() ?></h5>
                            <small>Pièces</small>
                        </div>
                    </div>
                </div>
                <div class="mt-2 mb-4">
                    <span class="text-muted">Prix</span>
                    <p class="h1 text-primary fw-bold"><?= number_format($bien->getPrix(), 0, ',', ' ') ?> €</p>
                    <span class="badge <?= $bien->getStatut() === 'disponible' ? 'bg-success' : ($bien->getStatut() === 'reserve' ? 'bg-warning' : 'bg-secondary') ?> rounded-pill">
                        <?= $bien->getStatut() === 'disponible' ? 'Disponible' : ($bien->getStatut() === 'reserve' ? 'Réservé' : 'Vendu') ?>
                    </span>
                </div>
                <hr>
                <h5>Description</h5>
                <p class="text-muted"><?= nl2br(htmlspecialchars($bien->getDescription())) ?></p>

                <?php if (Utilisateur::isLogged()): 
                    $user = Utilisateur::getCurrentUser();
                ?>
                    <div class="d-flex gap-2 mt-3">
                        <?php if ($estFavori): ?>
                            <a href="ajouter_favoris.php?action=remove&id=<?= $id ?>" class="btn btn-outline-danger rounded-pill flex-grow-1"><i class="fas fa-heart me-1"></i> Retirer des favoris</a>
                        <?php else: ?>
                            <a href="ajouter_favoris.php?action=add&id=<?= $id ?>" class="btn btn-outline-danger rounded-pill flex-grow-1"><i class="far fa-heart me-1"></i> Ajouter aux favoris</a>
                        <?php endif; ?>
                        <?php if ($bien->getStatut() === 'disponible' && ($user->hasRole('client') || $user->hasRole('commercial') || $user->hasRole('admin'))): ?>
                            <a href="paiement.php?id=<?= $id ?>" class="btn btn-success rounded-pill flex-grow-1"><i class="fas fa-shopping-cart me-1"></i> Acheter (simulation)</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <a href="annonces.php" class="btn btn-outline-secondary rounded-pill mt-3"><i class="fas fa-arrow-left me-1"></i> Retour aux annonces</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>