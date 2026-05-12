<?php
require_once '../config/init.php';
$requiredRole = 'commercial';
require_once '../config/auth.php';

$pdo = Database::getInstance();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    if (empty($titre)) $errors[] = "Le titre est requis.";
    if (!$prix || $prix <= 0) $errors[] = "Prix invalide.";
    if (!$surface || $surface <= 0) $errors[] = "Surface invalide.";
    if (!$pieces || $pieces <= 0) $errors[] = "Nombre de pièces invalide.";
    if (empty($type)) $errors[] = "Type requis.";
    if (empty($ville)) $errors[] = "Ville requise.";

    if (empty($errors)) {
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
        $bien->save();

        // Upload d'images
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $ext;
                    $destination = $uploadDir . $filename;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $bien->addPhoto($destination, $key);
                    }
                }
            }
        }
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
    <title>Ajouter une annonce - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container py-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <h2><i class="fas fa-plus-circle me-2"></i>Ajouter une annonce</h2>
            <p class="text-muted">Remplissez les informations du bien</p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold">Titre *</label><input type="text" name="titre" class="form-control rounded-pill" required></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Prix (€) *</label><input type="number" step="0.01" name="prix" class="form-control rounded-pill" required></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Statut</label><select name="statut" class="form-select rounded-pill"><option value="disponible">Disponible</option><option value="reserve">Réservé</option><option value="vendu">Vendu</option></select></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Surface (m²) *</label><input type="number" name="surface" class="form-control rounded-pill" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Pièces *</label><input type="number" name="pieces" class="form-control rounded-pill" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Type *</label><select name="type" class="form-select rounded-pill" required><option value="">Choisir</option><option value="appartement">Appartement</option><option value="maison">Maison</option><option value="terrain">Terrain</option><option value="local">Local commercial</option></select></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Ville *</label><input type="text" name="ville" class="form-control rounded-pill" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Code postal</label><input type="text" name="code_postal" class="form-control rounded-pill"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Adresse</label><input type="text" name="adresse" class="form-control rounded-pill"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" rows="4" class="form-control"></textarea></div>
                    <div class="col-12"><label class="form-label fw-semibold">Photos (plusieurs possibles)</label><input type="file" name="images[]" multiple class="form-control" accept="image/*"></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">Publier l'annonce</button> <a href="dashboard.php" class="btn btn-secondary rounded-pill">Annuler</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>