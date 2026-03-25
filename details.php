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

// Incrémenter le compteur de vues
$bien->incrementVue();

// Enregistrer dans les logs
$pdo = Database::getInstance();
$stmt = $pdo->prepare("INSERT INTO logs_consultation (bien_id, utilisateur_id) VALUES (?, ?)");
$stmt->execute([$id, $_SESSION['user_id'] ?? null]);

$photos = $bien->getPhotos();

// Vérifier si l'utilisateur connecté a ce bien en favori
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
    <title><?= htmlspecialchars($bien->getTitre()) ?> - Détail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <?php if (!empty($photos)): ?>
                    <div id="carouselPhotos" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($photos as $index => $photo): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= htmlspecialchars($photo['chemin']) ?>" class="d-block w-100" alt="..." style="max-height:400px; object-fit:cover;">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotos" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotos" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                    </div>
                <?php else: ?>
                    <img src="https://via.placeholder.com/600x400" class="img-fluid rounded" alt="...">
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h1><?= htmlspecialchars($bien->getTitre()) ?></h1>
                <p class="text-primary h2"><?= number_format($bien->getPrix(), 0, ',', ' ') ?> €</p>
                <p><strong>Ville :</strong> <?= htmlspecialchars($bien->getVille()) ?> (<?= htmlspecialchars($bien->getCodePostal()) ?>)</p>
                <p><strong>Adresse :</strong> <?= htmlspecialchars($bien->getAdresse()) ?></p>
                <p><strong>Surface :</strong> <?= $bien->getSurface() ?> m²</p>
                <p><strong>Pièces :</strong> <?= $bien->getPieces() ?></p>
                <p><strong>Type :</strong> <?= ucfirst($bien->getType()) ?></p>
                <p><strong>Statut :</strong> 
                    <?php if ($bien->getStatut() === 'disponible'): ?>
                        <span class="badge bg-success">Disponible</span>
                    <?php elseif ($bien->getStatut() === 'reserve'): ?>
                        <span class="badge bg-warning">Réservé</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Vendu</span>
                    <?php endif; ?>
                </p>
                <hr>
                <h5>Description</h5>
                <p><?= nl2br(htmlspecialchars($bien->getDescription())) ?></p>

                <?php if (Utilisateur::isLogged()): 
                    $user = Utilisateur::getCurrentUser();
                ?>
                    <div class="mt-4">
                        <?php if ($estFavori): ?>
                            <a href="ajouter_favoris.php?action=remove&id=<?= $id ?>" class="btn btn-outline-danger">❤️ Retirer des favoris</a>
                        <?php else: ?>
                            <a href="ajouter_favoris.php?action=add&id=<?= $id ?>" class="btn btn-outline-danger">❤️ Ajouter aux favoris</a>
                        <?php endif; ?>
                        
                        <?php if ($bien->getStatut() === 'disponible' && ($user->hasRole('client') || $user->hasRole('commercial') || $user->hasRole('admin'))): ?>
                            <a href="paiement.php?id=<?= $id ?>" class="btn btn-success">💰 Acheter (simulation)</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary mt-3">← Retour aux annonces</a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>