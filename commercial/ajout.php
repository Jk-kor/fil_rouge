<?php
require_once '../config/init.php';
$requiredRole = 'commercial';
require_once '../config/auth.php';

$pdo = Database::getInstance();
$errors = [];
$success = false;

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
        // Créer l'objet Bien
        $bien = new Bien([
            'titre' => $titre,
            'description' => $description,
            'prix' => $prix,
            'surface' => $surface,
            'pieces' => $pieces,
            'type' => $type,
            'ville' => $ville,
            'code_postal' => $code_postal,
            'adresse' => $adresse,
            'statut' => $statut,
            'commercial_id' => $_SESSION['user_id']
        ]);
        $bien->save(); // Insertion

        // Upload des images
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
        $success = true;
        // Rediriger vers la liste
        header('Location: dashboard.php?success=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une annonce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <h1>Ajouter une annonce</h1>
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
                    <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="prix" class="form-label">Prix (€) *</label>
                    <input type="number" step="0.01" class="form-control" id="prix" name="prix" value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="disponible">Disponible</option>
                        <option value="reserve">Réservé</option>
                        <option value="vendu">Vendu</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="surface" class="form-label">Surface (m²) *</label>
                    <input type="number" class="form-control" id="surface" name="surface" value="<?= htmlspecialchars($_POST['surface'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pieces" class="form-label">Nombre de pièces *</label>
                    <input type="number" class="form-control" id="pieces" name="pieces" value="<?= htmlspecialchars($_POST['pieces'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">Type *</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="">Choisir...</option>
                        <option value="appartement">Appartement</option>
                        <option value="maison">Maison</option>
                        <option value="terrain">Terrain</option>
                        <option value="local">Local commercial</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="ville" class="form-label">Ville *</label>
                    <input type="text" class="form-control" id="ville" name="ville" value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="code_postal" class="form-label">Code postal</label>
                    <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="adresse" class="form-label">Adresse</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="images" class="form-label">Photos (vous pouvez en sélectionner plusieurs)</label>
                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                <small class="text-muted">Formats acceptés : jpg, png, gif. Taille max : 2 Mo par image.</small>
            </div>
            <button type="submit" class="btn btn-primary">Publier l'annonce</button>
            <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>