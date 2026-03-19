<?php
// Fichier : index.php
require_once('db_connect.php'); // 기존 DB 접속 (테스트용 메시지 출력 코드는 지우세요!)
require_once('classes/Database.php'); // 추가!
require_once('classes/Bien.php');     // 추가!

$biens = Bien::getAll(6); // 6 dernières annonces
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Agence Immobilière</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Dernières annonces</h1>
        <div class="row">
            <?php foreach ($biens as $bien): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php 
                    $photos = $bien->getPhotos();
                    $image = !empty($photos) ? $photos[0]['chemin'] : 'https://via.placeholder.com/300x200';
                    ?>
                    <img src="<?= htmlspecialchars($image) ?>" class="card-img-top" alt="..." style="height:200px; object-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($bien->getTitre()) ?></h5>
                        <p class="card-text text-primary fw-bold"><?= number_format($bien->getPrix(), 0, ',', ' ') ?> €</p>
                        <p class="card-text"><?= htmlspecialchars($bien->getVille()) ?> - <?= $bien->getSurface() ?> m², <?= $bien->getPieces() ?> pièces</p>
                        <a href="details.php?id=<?= $bien->getId() ?>" class="btn btn-outline-primary">Voir détail</a>
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