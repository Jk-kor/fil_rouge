<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/init.php';
$requiredRole = 'commercial';
require_once '../config/auth.php';

$pdo = Database::getInstance();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Récupérer le bien
$stmt = $pdo->prepare("SELECT * FROM biens WHERE id = ?");
$stmt->execute([$id]);
$bien = $stmt->fetch();
if (!$bien) {
    header('Location: dashboard.php');
    exit;
}

// Vérifier droits
$user = Utilisateur::getCurrentUser();
if ($bien['commercial_id'] != $_SESSION['user_id'] && !$user->hasRole('admin')) {
    header('Location: dashboard.php');
    exit;
}

// Récupérer les photos
$stmtPhoto = $pdo->prepare("SELECT * FROM photos WHERE bien_id = ? ORDER BY ordre");
$stmtPhoto->execute([$id]);
$photos = $stmtPhoto->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = (float)($_POST['prix'] ?? 0);
    $surface = (int)($_POST['surface'] ?? 0);
    $pieces = (int)($_POST['pieces'] ?? 0);
    $type = $_POST['type'] ?? '';
    $ville = trim($_POST['ville'] ?? '');
    $code_postal = trim($_POST['code_postal'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $statut = $_POST['statut'] ?? 'disponible';

    if (empty($titre)) $error = "Le titre est requis.";
    elseif ($prix <= 0) $error = "Prix invalide.";
    elseif ($surface <= 0) $error = "Surface invalide.";
    elseif ($pieces <= 0) $error = "Nombre de pièces invalide.";
    elseif (empty($type)) $error = "Type requis.";
    elseif (empty($ville)) $error = "Ville requise.";

    if (empty($error)) {
        $sql = "UPDATE biens SET titre=?, description=?, prix=?, surface=?, pieces=?, type=?, ville=?, code_postal=?, adresse=?, statut=? WHERE id=?";
        $upd = $pdo->prepare($sql);
        $upd->execute([$titre, $description, $prix, $surface, $pieces, $type, $ville, $code_postal, $adresse, $statut, $id]);

        // Upload nouvelles photos
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $newName = uniqid() . '.' . $ext;
                    $dest = $uploadDir . $newName;
                    if (move_uploaded_file($tmp, $dest)) {
                        $insPhoto = $pdo->prepare("INSERT INTO photos (bien_id, chemin, ordre) VALUES (?, ?, ?)");
                        $insPhoto->execute([$id, $dest, $key]);
                    }
                }
            }
        }

        // Suppression photos
        if (isset($_POST['delete_photos']) && is_array($_POST['delete_photos'])) {
            foreach ($_POST['delete_photos'] as $pid) {
                $pid = (int)$pid;
                $sel = $pdo->prepare("SELECT chemin FROM photos WHERE id = ? AND bien_id = ?");
                $sel->execute([$pid, $id]);
                $path = $sel->fetchColumn();
                if ($path && file_exists($path)) unlink($path);
                $del = $pdo->prepare("DELETE FROM photos WHERE id = ?");
                $del->execute([$pid]);
            }
        }

        header('Location: dashboard.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modifier annonce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2>Modifier l'annonce</h2>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Titre</label>
                    <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($bien['titre']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label>Prix (€)</label>
                        <input type="number" step="0.01" name="prix" class="form-control" value="<?= $bien['prix'] ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Surface (m²)</label>
                        <input type="number" name="surface" class="form-control" value="<?= $bien['surface'] ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Pièces</label>
                        <input type="number" name="pieces" class="form-control" value="<?= $bien['pieces'] ?>" required>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <label>Type</label>
                        <select name="type" class="form-select" required>
                            <option value="appartement" <?= $bien['type']=='appartement'?'selected':'' ?>>Appartement</option>
                            <option value="maison" <?= $bien['type']=='maison'?'selected':'' ?>>Maison</option>
                            <option value="terrain" <?= $bien['type']=='terrain'?'selected':'' ?>>Terrain</option>
                            <option value="local" <?= $bien['type']=='local'?'selected':'' ?>>Local commercial</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Ville</label>
                        <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($bien['ville']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Code postal</label>
                        <input type="text" name="code_postal" class="form-control" value="<?= htmlspecialchars($bien['code_postal']) ?>">
                    </div>
                </div>
                <div class="mb-3 mt-2">
                    <label>Adresse</label>
                    <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($bien['adresse']) ?>">
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($bien['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label>Statut</label>
                    <select name="statut" class="form-select">
                        <option value="disponible" <?= $bien['statut']=='disponible'?'selected':'' ?>>Disponible</option>
                        <option value="reserve" <?= $bien['statut']=='reserve'?'selected':'' ?>>Réservé</option>
                        <option value="vendu" <?= $bien['statut']=='vendu'?'selected':'' ?>>Vendu</option>
                    </select>
                </div>

                <?php if ($photos): ?>
                    <div class="mb-3">
                        <label>Photos existantes :</label>
                        <div class="row">
                            <?php foreach ($photos as $p): ?>
                            <div class="col-md-3">
                                <img src="../<?= htmlspecialchars($p['chemin']) ?>" style="width:100%; height:100px; object-fit:cover;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="delete_photos[]" value="<?= $p['id'] ?>">
                                    <label class="form-check-label">Supprimer</label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label>Ajouter des photos</label>
                    <input type="file" name="images[]" multiple class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>