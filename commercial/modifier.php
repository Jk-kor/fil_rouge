<?php
// Fichier : commercial/modifier.php
require_once '../config/init.php';
$requiredRole = 'commercial';
require_once '../config/auth.php';

$pdo = Database::getInstance();
$errors = [];

// Récupérer l'ID de l'annonce
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: dashboard.php');
    exit;
}

$bien = Bien::getById($id);
if (!$bien) {
    header('Location: dashboard.php');
    exit;
}

// Vérifier que le bien appartient au commercial connecté (ou admin)
$user = Utilisateur::getCurrentUser();
if ($bien->getCommercialId() != $_SESSION['user_id'] && !$user->hasRole('admin')) {
    header('Location: dashboard.php');
    exit;
}

$photosExistantes = $bien->getPhotos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des champs
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT);
    $surface = filter_input(INPUT_POST, 'surface', FILTER_VALIDATE_INT);
    $pieces = filter_input(INPUT_POST, 'pieces', FILTER_VALIDATE_INT);
    $type = $_POST['type'] ?? '';
    $ville = trim($_POST['ville'] ?? '');
    $code_postal = trim($_POST['code_postal'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $statut = $_POST['statut'] ?? 'disponible';

    // Validations
    if (empty($titre)) $errors[] = "Le titre est requis.";
    if (!$prix || $prix <= 0) $errors[] = "Le prix doit être un nombre positif.";
    if (!$surface || $surface <= 0) $errors[] = "La surface doit être un nombre positif.";
    if (!$pieces || $pieces <= 0) $errors[] = "Le nombre de pièces doit être positif.";
    if (empty($type)) $errors[] = "Le type est requis.";
    if (empty($ville)) $errors[] = "La ville est requise.";

    if (empty($errors)) {
        // Mettre à jour l'objet Bien
        $bien->setTitre($titre);
        $bien->setDescription($description);
        $bien->setPrix($prix);
        $bien->setSurface($surface);
        $bien->setPieces($pieces);
        $bien->setType($type);
        $bien->setVille($ville);
        $bien->setCodePostal($code_postal);
        $bien->setAdresse($adresse);
        $bien->setStatut($statut);
        $bien->save(); // Update

        // Upload des nouvelles images
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $extension;
                    $destination = $uploadDir . $filename;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $bien->addPhoto($destination, $key);
                    }
                }
            }
        }

        // Suppression d'images cochées
        if (isset($_POST['delete_photos']) && is_array($_POST['delete_photos'])) {
            $pdo = Database::getInstance();
            foreach ($_POST['delete_photos'] as $photoId) {
                $photoId = (int)$photoId;
                // Récupérer le chemin pour supprimer le fichier
                $stmt = $pdo->prepare("SELECT chemin FROM photos WHERE id = ? AND bien_id = ?");
                $stmt->execute([$photoId, $id]);
                $photo = $stmt->fetch();
                if ($photo) {
                    if (file_exists($photo['chemin'])) {
                        unlink($photo['chemin']);
                    }
                    $stmtDel = $pdo->prepare("DELETE FROM photos WHERE id = ?");
                    $stmtDel->execute([$photoId]);
                }
            }
        }

        header('Location: dashboard.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'annonce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <h1>Modifier l'annonce</h1>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="titre" class="form-label">Titre *</label>
                    <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($bien->getTitre()) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="prix" class="form-label">Prix (€) *</label>
                    <input type="number" step="0.01" class="form-control" id="prix" name="prix" value="<?= htmlspecialchars($bien->getPrix()) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="disponible" <?= $bien->getStatut() == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="reserve" <?= $bien->getStatut() == 'reserve' ? 'selected' : '' ?>>Réservé</option>
                        <option value="vendu" <?= $bien->getStatut() == 'vendu' ? 'selected' : '' ?>>Vendu</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="surface" class="form-label">Surface (m²) *</label>
                    <input type="number" class="form-control" id="surface" name="surface" value="<?= htmlspecialchars($bien->getSurface()) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pieces" class="form-label">Nombre de pièces *</label>
                    <input type="number" class="form-control" id="pieces" name="pieces" value="<?= htmlspecialchars($bien->getPieces()) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">Type *</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="">Choisir...</option>
                        <option value="appartement" <?= $bien->getType() == 'appartement' ? 'selected' : '' ?>>Appartement</option>
                        <option value="maison" <?= $bien->getType() == 'maison' ? 'selected' : '' ?>>Maison</option>
                        <option value="terrain" <?= $bien->getType() == 'terrain' ? 'selected' : '' ?>>Terrain</option>
                        <option value="local" <?= $bien->getType() == 'local' ? 'selected' : '' ?>>Local commercial</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="ville" class="form-label">Ville *</label>
                    <input type="text" class="form-control" id="ville" name="ville" value="<?= htmlspecialchars($bien->getVille()) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="code_postal" class="form-label">Code postal</label>
                    <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?= htmlspecialchars($bien->getCodePostal()) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="adresse" class="form-label">Adresse</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?= htmlspecialchars($bien->getAdresse()) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($bien->getDescription()) ?></textarea>
            </div>

            <!-- Gestion des photos existantes -->
            <?php if (!empty($photosExistantes)): ?>
                <div class="mb-3">
                    <label class="form-label">Photos actuelles (cochez pour supprimer)</label>
                    <div class="row">
                        <?php foreach ($photosExistantes as $photo): ?>
                            <div class="col-md-3 mb-2">
                                <div class="card">
                                    <img src="../<?= htmlspecialchars($photo['chemin']) ?>" class="card-img-top" alt="..." style="height: 150px; object-fit: cover;">
                                    <div class="card-body text-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_photos[]" value="<?= $photo['id'] ?>" id="photo<?= $photo['id'] ?>">
                                            <label class="form-check-label" for="photo<?= $photo['id'] ?>">Supprimer</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="images" class="form-label">Ajouter de nouvelles photos (vous pouvez en sélectionner plusieurs)</label>
                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                <small class="text-muted">Les nouvelles photos s'ajouteront aux existantes.</small>
            </div>

            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>