<?php
// Fichier : admin/stats.php
require_once '../config/init.php';
$requiredRole = 'admin';
require_once '../config/auth.php';

$pdo = Database::getInstance();

// 1. Prix moyen au m² par ville (top 10)
$stmt = $pdo->query("SELECT ville, AVG(prix/surface) as prix_m2 FROM biens WHERE surface > 0 GROUP BY ville ORDER BY prix_m2 DESC LIMIT 10");
$prixM2Data = $stmt->fetchAll();
$villes = [];
$prixM2 = [];
foreach ($prixM2Data as $row) {
    $villes[] = $row['ville'];
    $prixM2[] = round($row['prix_m2'], 2);
}

// 2. Biens les plus consultés (top 5)
$stmt = $pdo->query("SELECT titre, vue_count FROM biens ORDER BY vue_count DESC LIMIT 5");
$topVues = $stmt->fetchAll();
$titresVues = [];
$vues = [];
foreach ($topVues as $row) {
    $titresVues[] = $row['titre'];
    $vues[] = $row['vue_count'];
}

// 3. Évolution des ventes par mois (derniers 12 mois)
$stmt = $pdo->query("SELECT DATE_FORMAT(date_achat, '%Y-%m') as mois, COUNT(*) as nb_ventes FROM achats WHERE date_achat >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY mois ORDER BY mois");
$ventesMois = $stmt->fetchAll();
$mois = [];
$nbVentes = [];
foreach ($ventesMois as $row) {
    $mois[] = $row['mois'];
    $nbVentes[] = $row['nb_ventes'];
}

// 4. Répartition par type de bien
$stmt = $pdo->query("SELECT type, COUNT(*) as nb FROM biens GROUP BY type");
$typesData = $stmt->fetchAll();
$types = [];
$nbTypes = [];
foreach ($typesData as $row) {
    $types[] = $row['type'];
    $nbTypes[] = $row['nb'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <h1>Statistiques et analyses</h1>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Prix moyen au m² par ville (top 10)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="prixM2Chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Biens les plus consultés</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="topVuesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Évolution des ventes (12 derniers mois)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="ventesMoisChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Répartition par type de bien</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique prix moyen au m²
        new Chart(document.getElementById('prixM2Chart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($villes) ?>,
                datasets: [{
                    label: 'Prix moyen au m² (€)',
                    data: <?= json_encode($prixM2) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });

        // Graphique top vues
        new Chart(document.getElementById('topVuesChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($titresVues) ?>,
                datasets: [{
                    label: 'Nombre de vues',
                    data: <?= json_encode($vues) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });

        // Graphique évolution ventes
        new Chart(document.getElementById('ventesMoisChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($mois) ?>,
                datasets: [{
                    label: 'Nombre de ventes',
                    data: <?= json_encode($nbVentes) ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });

        // Graphique répartition par type
        new Chart(document.getElementById('typeChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($types) ?>,
                datasets: [{
                    data: <?= json_encode($nbTypes) ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });
    </script>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
