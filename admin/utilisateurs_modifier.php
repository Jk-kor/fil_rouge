<?php
// Fichier : admin/utilisateurs_modifier.php
require_once '../config/init.php';
$requiredRole = 'admin';
require_once '../config/auth.php';

$pdo = Database::getInstance();
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: utilisateurs.php');
    exit;
}

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: utilisateurs.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $role = $_POST['role'] ?? 'client';
    $agence_id = !empty($_POST['agence_id']) ? (int)$_POST['agence_id'] : null;
    $new_password = $_POST['new_password'] ?? '';

    if (!$email) $errors[] = "Email invalide.";
    if (empty($nom) || empty($prenom)) $errors[] = "Nom et prénom requis.";

    // Vérifier si l'email existe déjà pour un autre utilisateur
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Cet email est déjà utilisé par un autre compte.";
        }
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilisateurs SET email=?, nom=?, prenom=?, role=?, agence_id=?, password=? WHERE id=?");
                $stmt->execute([$email, $nom, $prenom, $role, $agence_id, $hash, $id]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET email=?, nom=?, prenom=?, role=?, agence_id=? WHERE id=?");
            $stmt->execute([$email, $nom, $prenom, $role, $agence_id, $id]);
        }
        header('Location: utilisateurs.php?updated=1');
        exit;
    }
}

$agences = $pdo->query("SELECT id, nom, ville FROM agences ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4" style="max-width: 600px;">
        <h1>Modifier l'utilisateur</h1>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom *</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? $user['nom']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom *</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? $user['prenom']) ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" class="form-control" id="new_password" name="new_password">
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-select" id="role" name="role">
                    <option value="client" <?= ($_POST['role'] ?? $user['role']) == 'client' ? 'selected' : '' ?>>Client</option>
                    <option value="commercial" <?= ($_POST['role'] ?? $user['role']) == 'commercial' ? 'selected' : '' ?>>Commercial</option>
                    <option value="admin" <?= ($_POST['role'] ?? $user['role']) == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="agence_id" class="form-label">Agence (pour les commerciaux)</label>
                <select class="form-select" id="agence_id" name="agence_id">
                    <option value="">-- Aucune --</option>
                    <?php foreach ($agences as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= (($_POST['agence_id'] ?? $user['agence_id']) == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['nom'] . ' - ' . $a['ville']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="utilisateurs.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>