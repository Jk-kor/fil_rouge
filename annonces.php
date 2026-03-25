<?php
// Fichier : annonces.php
require_once 'config/init.php';

$pdo = Database::getInstance();

// Récupération et nettoyage des filtres
$ville = isset($_GET['ville']) ? trim($_GET['ville']) : '';
$prix_min = isset($_GET['prix_min']) && is_numeric($_GET['prix_min']) ? (float)$_GET['prix_min'] : null;
$prix_max = isset($_GET['prix_max']) && is_numeric($_GET['prix_max']) ? (float)$_GET['prix_max'] : null;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$statut = isset($_GET['statut']) ? $_GET['statut'] : 'disponible';

// Forcer les prix à >= 0
if ($prix_min !== null && $prix_min < 0) $prix_min = 0;
if ($prix_max !== null && $prix_max < 0) $prix_max = 0;

// Construction de la requête avec filtres
$sql = "SELECT * FROM biens WHERE 1=1";
$params = [];

if (!empty($ville)) {
    $sql .= " AND ville LIKE :ville";
    $params[':ville'] = '%' . $ville . '%';
}
if ($prix_min !== null) {
    $sql .= " AND prix >= :prix_min";
    $params[':prix_min'] = $prix_min;
}
if ($prix_max !== null) {
    $sql .= " AND prix <= :prix_max";
    $params[':prix_max'] = $prix_max;
}
if (!empty($type)) {
    $sql .= " AND type = :type";
    $params[':type'] = $type;
}
if (!empty($statut)) {
    $sql .= " AND statut = :statut";
    $params[':statut'] = $statut;
}

$sql .= " ORDER BY date_creation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$biens = $stmt->fetchAll();

// Récupérer la liste des types pour le select
$types = $pdo->query("SELECT DISTINCT type FROM biens WHERE type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annonces immobilières</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1>Rechercher un bien</h1>
        
        <!-- Formulaire de filtres -->
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="ville" class="form-label">Ville</label>
                <input type="text" class="form-control" id="ville" name="ville" value="<?= htmlspecialchars($ville) ?>">
            </div>
            <div class="col-md-2">
                <label for="prix_min" class="form-label">Prix min (€)</label>
                <input type="number" class="form-control" id="prix_min" name="prix_min" value="<?= htmlspecialchars($prix_min ?? '') ?>" min="0" step="1">
            </div>
            <div class="col-md-2">
                <label for="prix_max" class="form-label">Prix max (€)</label>
                <input type="number" class="form-control" id="prix_max" name="prix_max" value="<?= htmlspecialchars($prix_max ?? '') ?>" min="0" step="1">
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Tous</option>
                    <?php foreach ($types as $t): ?>
                    <option value="<?= $t ?>" <?= ($type == $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" name="statut">
                    <option value="disponible" <?= ($statut == 'disponible') ? 'selected' : '' ?>>Disponible</option>
                    <option value="reserve" <?= ($statut == 'reserve') ? 'selected' : '' ?>>Réservé</option>
                    <option value="vendu" <?= ($statut == 'vendu') ? 'selected' : '' ?>>Vendu</option>
                </select>
            </div>
            <div class="col-md-1 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>

        <h2>Résultats (<?= count($biens) ?>)</h2>
        <div class="row">
            <?php if (empty($biens)): ?>
                <p class="alert alert-warning">Aucune annonce ne correspond à vos critères.</p>
            <?php else: ?>
                <?php foreach ($biens as $b): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php 
                        // Récupérer la première photo si elle existe
                        $stmt = $pdo->prepare("SELECT chemin FROM photos WHERE bien_id = ? ORDER BY ordre LIMIT 1");
                        $stmt->execute([$b['id']]);
                        $photo = $stmt->fetch();
                        $image = $photo ? $photo['chemin'] : 'https://via.placeholder.com/300x200';
                        ?>
                        <img src="<?= htmlspecialchars($image) ?>" class="card-img-top" alt="..." style="height:200px; object-fit:cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($b['titre']) ?></h5>
                            <p class="card-text text-primary fw-bold"><?= number_format($b['prix'], 0, ',', ' ') ?> €</p>
                            <p class="card-text"><?= htmlspecialchars($b['ville']) ?> - <?= $b['surface'] ?> m², <?= $b['pieces'] ?> pièces</p>
                            <a href="details.php?id=<?= $b['id'] ?>" class="btn btn-outline-primary">Voir détail</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>