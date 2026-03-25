<?php
// Fichier : client/dashboard.php
require_once '../config/init.php';
$requiredRole = 'client';
require_once '../config/auth.php';

$pdo = Database::getInstance();
$user = Utilisateur::getCurrentUser();

// Récupérer les favoris de l'utilisateur
$stmt = $pdo->prepare("SELECT b.* FROM biens b JOIN favoris f ON b.id = f.bien_id WHERE f.utilisateur_id = ? ORDER BY f.date_ajout DESC");
$stmt->execute([$_SESSION['user_id']]);
$favoris = $stmt->fetchAll();

// Récupérer l'historique des achats (simulés)
$stmt = $pdo->prepare("SELECT a.*, b.titre, b.prix FROM achats a JOIN biens b ON a.bien_id = b.id WHERE a.utilisateur_id = ? ORDER BY a.date_achat DESC");
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
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <h1>Bonjour <?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?></h1>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Mes favoris</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($favoris)): ?>
                            <p class="text-muted">Vous n'avez aucun favori pour le moment.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($favoris as $b): ?>
                                <a href="../details.php?id=<?= $b['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($b['titre']) ?>
                                    <span class="badge bg-primary rounded-pill"><?= number_format($b['prix'], 0, ',', ' ') ?> €</span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Mes achats simulés</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($achats)): ?>
                            <p class="text-muted">Vous n'avez encore rien acheté.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($achats as $a): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <span><?= htmlspecialchars($a['titre']) ?></span>
                                        <span class="badge bg-success rounded-pill"><?= number_format($a['montant'], 0, ',', ' ') ?> €</span>
                                    </div>
                                    <small class="text-muted">Acheté le <?= date('d/m/Y', strtotime($a['date_achat'])) ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
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