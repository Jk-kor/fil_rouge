<?php
// Fichier : admin/utilisateurs_ajout.php
require_once '../config/init.php';

// Vérification admin
if (!Utilisateur::isLogged()) {
    header('Location: ../login.php');
    exit;
}
$currentUser = Utilisateur::getCurrentUser();
if (!$currentUser->hasRole('admin')) {
    header('Location: ../index.php');
    exit;
}

$pdo = Database::getInstance();
$errors = [];

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
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetchColumn() > 0) {
            $errors[] = "Cet email est déjà utilisé.";
        }
    }
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, password, nom, prenom, role, agence_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$email, $hash, $nom, $prenom, $role, $agence_id]);
        header('Location: utilisateurs.php?added=1');
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
    <title>Ajouter un utilisateur - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <h2 class="mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur</h2>
            <p class="text-muted">Créez un nouveau compte (client, commercial ou admin)</p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control rounded-pill" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nom</label>
                        <input type="text" name="nom" class="form-control rounded-pill" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Prénom</label>
                        <input type="text" name="prenom" class="form-control rounded-pill" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mot de passe</label>
                    <input type="password" name="password" class="form-control rounded-pill" required>
                    <small class="text-muted">Minimum 6 caractères</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rôle</label>
                    <select name="role" class="form-select rounded-pill">
                        <option value="client">Client</option>
                        <option value="commercial">Commercial</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Agence (pour les commerciaux)</label>
                    <select name="agence_id" class="form-select rounded-pill">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($agences as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom'] . ' - ' . $a['ville']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i>Créer</button>
                    <a href="utilisateurs.php" class="btn btn-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>