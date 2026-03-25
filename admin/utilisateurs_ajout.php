<?php
// Fichier : admin/utilisateurs_ajout.php
require_once '../config/init.php';
$requiredRole = 'admin';
require_once '../config/auth.php';

$pdo = Database::getInstance();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $role = $_POST['role'] ?? 'client';
    $agence_id = !empty($_POST['agence_id']) ? (int)$_POST['agence_id'] : null;

    if (!$email) $errors[] = "Email invalide.";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    if (empty($nom) || empty($prenom)) $errors[] = "Nom et prénom requis.";

    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Cet email est déjà utilisé.";
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, password, nom, prenom, role, agence_id) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$email, $hash, $nom, $prenom, $role, $agence_id])) {
            $success = true;
            // Rediriger vers la liste
            header('Location: utilisateurs.php?added=1');
            exit;
        } else {
            $errors[] = "Erreur lors de l'insertion.";
        }
    }
}

// Récupérer la liste des agences pour le select
$agences = $pdo->query("SELECT id, nom, ville FROM agences ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4" style="max-width: 600px;">
        <h1>Ajouter un utilisateur</h1>
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
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom *</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom *</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe *</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-select" id="role" name="role">
                    <option value="client">Client</option>
                    <option value="commercial">Commercial</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="agence_id" class="form-label">Agence (pour les commerciaux)</label>
                <select class="form-select" id="agence_id" name="agence_id">
                    <option value="">-- Aucune --</option>
                    <?php foreach ($agences as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom'] . ' - ' . $a['ville']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Créer</button>
            <a href="utilisateurs.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>