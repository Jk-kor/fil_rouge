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

// Types distincts pour le filtre
$types = $pdo->query("SELECT DISTINCT type FROM biens WHERE type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche immobilière - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-4 mb-5">
        <div class="card-body p-4">
            <h2 class="section-title mb-4"><i class="fas fa-filter me-2"></i>Filtrer les annonces</h2>
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="ville" class="form-label fw-semibold">Ville</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-city"></i></span>
                        <input type="text" class="form-control border-start-0" id="ville" name="ville" value="<?= htmlspecialchars($ville) ?>" placeholder="Ex: Lyon">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="prix_min" class="form-label fw-semibold">Prix min (€)</label>
                    <input type="number" class="form-control" id="prix_min" name="prix_min" value="<?= htmlspecialchars($prix_min ?? '') ?>" min="0" step="1" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label for="prix_max" class="form-label fw-semibold">Prix max (€)</label>
                    <input type="number" class="form-control" id="prix_max" name="prix_max" value="<?= htmlspecialchars($prix_max ?? '') ?>" min="0" step="1" placeholder="Illimité">
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label fw-semibold">Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tous</option>
                        <?php foreach ($types as $t): ?>
                        <option value="<?= $t ?>" <?= ($type == $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="statut" class="form-label fw-semibold">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="disponible" <?= ($statut == 'disponible') ? 'selected' : '' ?>>Disponible</option>
                        <option value="reserve" <?= ($statut == 'reserve') ? 'selected' : '' ?>>Réservé</option>
                        <option value="vendu" <?= ($statut == 'vendu') ? 'selected' : '' ?>>Vendu</option>
                    </select>
                </div>
                <div class="col-md-1 align-self-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill"><i class="fas fa-search me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title mb-0"><i class="fas fa-home me-2"></i>Résultats <span class="badge bg-primary rounded-pill"><?= count($biens) ?></span></h2>
    </div>

    <?php if (empty($biens)): ?>
        <div class="alert alert-warning text-center p-5 rounded-4">
            <i class="fas fa-frown fa-3x mb-3"></i>
            <h4>Aucune annonce ne correspond à vos critères</h4>
            <p>Essayez de modifier vos filtres ou revenez plus tard.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($biens as $b): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card property-card h-100 border-0 shadow-sm">
                    <?php 
                    $stmt = $pdo->prepare("SELECT chemin FROM photos WHERE bien_id = ? ORDER BY ordre LIMIT 1");
                    $stmt->execute([$b['id']]);
                    $photo = $stmt->fetch();
                    $image = $photo ? $photo['chemin'] : 'https://placehold.co/600x400?text=Image+indisponible';
                    ?>
                    <div class="card-img-top-wrapper">
                        <img src="<?= htmlspecialchars($image) ?>" class="card-img-top" alt="<?= htmlspecialchars($b['titre']) ?>" style="height: 220px; object-fit: cover;">
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($b['titre']) ?></h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2"><?= ucfirst($b['type']) ?></span>
                        </div>
                        <div class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($b['ville']) ?></div>
                        <p class="h4 text-primary fw-bold mt-2"><?= number_format($b['prix'], 0, ',', ' ') ?> €</p>
                        <div class="d-flex gap-3 mt-3 mb-3 text-muted small">
                            <span><i class="fas fa-chart-area"></i> <?= $b['surface'] ?> m²</span>
                            <span><i class="fas fa-bed"></i> <?= $b['pieces'] ?> pièces</span>
                        </div>
                        <a href="details.php?id=<?= $b['id'] ?>" class="btn btn-outline-primary w-100 rounded-pill">Voir détail <i class="fas fa-chevron-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>