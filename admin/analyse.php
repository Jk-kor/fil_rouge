<?php
// Fichier : admin/analyse.php
require_once '../config/init.php';

// Vérification admin
if (!Utilisateur::isLogged()) {
    header('Location: ../login.php');
    exit;
}
$currentUser = Utilisateur::getCurrentUser();
if (!$currentUser->hasRole('admin')) {
    header('Location: ../index.php');
    exit;
}

// Lire les fichiers générés par Python
$statsFile = '../assets/stats/stats.json';
$predictionsFile = '../assets/stats/predictions.json';
$stats = [];
$predictions = [];
if (file_exists($statsFile)) {
    $stats = json_decode(file_get_contents($statsFile), true);
}
if (file_exists($predictionsFile)) {
    $predictions = json_decode(file_get_contents($predictionsFile), true);
}

// Date de dernière génération
$lastGen = file_exists($statsFile) ? date('d/m/Y H:i:s', filemtime($statsFile)) : 'Jamais';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse de données - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Analyse de données immobilières</h1>
        <a href="run_analyse.php" class="btn btn-primary rounded-pill"><i class="fas fa-sync-alt me-1"></i>Actualiser les analyses</a>
    </div>
    <p class="text-muted">Dernière mise à jour : <?= $lastGen ?></p>

    <div class="row g-4">
        <!-- Statistiques textuelles -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5><i class="fas fa-chart-simple me-2"></i>Indicateurs clés</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats)): ?>
                        <?php if (isset($stats['prix_moyen_ville'])): ?>
                            <h6>💰 Prix moyen par ville (top 5) :</h6>
                            <ul>
                                <?php 
                                $i=0;
                                foreach ($stats['prix_moyen_ville'] as $ville => $prix):
                                    if ($i++ >= 5) break;
                                ?>
                                    <li><strong><?= htmlspecialchars($ville) ?></strong> : <?= number_format($prix, 0, ',', ' ') ?> €</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (isset($stats['prix_m2_ville'])): ?>
                            <h6>📐 Prix moyen au m² par ville (top 5) :</h6>
                            <ul>
                                <?php 
                                $i=0;
                                foreach ($stats['prix_m2_ville'] as $ville => $prix):
                                    if ($i++ >= 5) break;
                                ?>
                                    <li><strong><?= htmlspecialchars($ville) ?></strong> : <?= number_format($prix, 0, ',', ' ') ?> €/m²</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (isset($stats['type_count'])): ?>
                            <h6>🏷️ Répartition par type :</h6>
                            <ul>
                            <?php foreach ($stats['type_count'] as $type => $count): ?>
                                <li><?= ucfirst($type) ?> : <?= $count ?> biens</li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">Aucune donnée statistique disponible. Exécutez l’analyse.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Prédictions -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5><i class="fas fa-chart-line me-2"></i>Prédiction des prix</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($predictions)): ?>
                        <p class="mb-2">Estimation du prix en fonction de la surface (régression linéaire) :</p>
                        <ul>
                            <?php foreach ($predictions as $surface => $prix): ?>
                                <li><strong><?= $surface ?> m²</strong> : <?= number_format($prix, 0, ',', ' ') ?> €</li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="small text-muted">Modèle entraîné sur les annonces existantes. À prendre avec précaution.</p>
                    <?php else: ?>
                        <p class="text-muted">Pas assez de données pour une prédiction.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Graphiques générés par Python -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5><i class="fas fa-chart-pie me-2"></i>Visualisations</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-center">Prix moyen par ville</h6>
                            <img src="../assets/stats/prix_moyen_ville.png" class="img-fluid rounded" alt="Prix moyen par ville">
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-center">Prix au m² par ville</h6>
                            <img src="../assets/stats/prix_m2_ville.png" class="img-fluid rounded" alt="Prix au m² par ville">
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-center">Répartition par type</h6>
                            <img src="../assets/stats/type_repartition.png" class="img-fluid rounded" alt="Répartition par type">
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-center">Top 5 des biens les plus consultés</h6>
                            <img src="../assets/stats/top_vues.png" class="img-fluid rounded" alt="Top vues">
                        </div>
                        <?php if (file_exists('../assets/stats/ventes_mois.png')): ?>
                        <div class="col-12">
                            <h6 class="text-center">Évolution des ventes par mois</h6>
                            <img src="../assets/stats/ventes_mois.png" class="img-fluid rounded" alt="Ventes mensuelles">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>