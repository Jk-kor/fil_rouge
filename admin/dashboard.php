<?php
// Fichier : admin/dashboard.php
require_once '../config/init.php';
$requiredRole = 'admin';
require_once '../config/auth.php';

$pdo = Database::getInstance();

// Statistiques générales
$nbUtilisateurs = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$nbBiens = $pdo->query("SELECT COUNT(*) FROM biens")->fetchColumn();
$nbAnnoncesDisponibles = $pdo->query("SELECT COUNT(*) FROM biens WHERE statut = 'disponible'")->fetchColumn();
$nbVentes = $pdo->query("SELECT COUNT(*) FROM achats")->fetchColumn();

// Derniers utilisateurs inscrits
$stmt = $pdo->query("SELECT id, email, nom, prenom, role, date_inscription FROM utilisateurs ORDER BY date_inscription DESC LIMIT 5");
$derniersUtilisateurs = $stmt->fetchAll();

// Dernières annonces ajoutées
$stmt = $pdo->query("SELECT b.*, u.nom, u.prenom FROM biens b LEFT JOIN utilisateurs u ON b.commercial_id = u.id ORDER BY b.date_creation DESC LIMIT 5");
$dernieresAnnonces = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord admin - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <h1>Tableau de bord administrateur</h1>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Utilisateurs</h5>
                        <p class="card-text display-6"><?= $nbUtilisateurs ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Annonces totales</h5>
                        <p class="card-text display-6"><?= $nbBiens ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Disponibles</h5>
                        <p class="card-text display-6"><?= $nbAnnoncesDisponibles ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Ventes simulées</h5>
                        <p class="card-text display-6"><?= $nbVentes ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Derniers utilisateurs inscrits</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                32<th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($derniersUtilisateurs as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= $u['role'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <a href="utilisateurs.php" class="btn btn-sm btn-primary">Voir tous les utilisateurs</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Dernières annonces</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>ID</th><th>Titre</th><th>Commercial</th><th>Prix</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dernieresAnnonces as $a): ?>
                                <tr>
                                    <td><?= $a['id'] ?></td>
                                    <td><?= htmlspecialchars($a['titre']) ?></td>
                                    <td><?= htmlspecialchars(($a['prenom'] ?? '?') . ' ' . ($a['nom'] ?? '')) ?></td>
                                    <td><?= number_format($a['prix'], 0, ',', ' ') ?> €</td>
                                    <td><?= date('d/m/Y', strtotime($a['date_creation'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <a href="annonces.php" class="btn btn-sm btn-primary">Voir toutes les annonces</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="stats.php" class="btn btn-lg btn-success">Voir les statistiques détaillées 📊</a>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>