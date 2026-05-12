<?php
require_once '../config/init.php';

// Vérification de l'authentification et du rôle admin
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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die("ID manquant");
}

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$utilisateur = $stmt->fetch();
if (!$utilisateur) {
    die("Utilisateur introuvable");
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $nom     = trim($_POST['nom'] ?? '');
    $prenom  = trim($_POST['prenom'] ?? '');
    $role    = $_POST['role'] ?? 'client';
    $agence_id = !empty($_POST['agence_id']) ? (int)$_POST['agence_id'] : null;
    $new_password = $_POST['new_password'] ?? '';

    if (!$email || !$nom || !$prenom) {
        $message = "Tous les champs (email, nom, prénom) sont requis.";
    } else {
        // Vérifier email unique
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $check->execute([$email, $id]);
        if ($check->fetch()) {
            $message = "Cet email est déjà utilisé par un autre compte.";
        } else {
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $message = "Le mot de passe doit faire au moins 6 caractères.";
                } else {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE utilisateurs SET email=?, nom=?, prenom=?, role=?, agence_id=?, password=? WHERE id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$email, $nom, $prenom, $role, $agence_id, $hash, $id]);
                    $message = "Utilisateur mis à jour avec succès ! <a href='utilisateurs.php'>Retour à la liste</a>";
                }
            } else {
                $sql = "UPDATE utilisateurs SET email=?, nom=?, prenom=?, role=?, agence_id=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $nom, $prenom, $role, $agence_id, $id]);
                $message = "Utilisateur mis à jour avec succès ! <a href='utilisateurs.php'>Retour à la liste</a>";
            }
        }
    }
}

// Récupération des agences pour le select
$agences = $pdo->query("SELECT id, nom, ville FROM agences ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un utilisateur - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <h2 class="mb-0"><i class="fas fa-user-edit me-2"></i>Modifier l'utilisateur</h2>
            <p class="text-muted">Modifiez les informations du compte</p>
        </div>
        <div class="card-body p-4">
            <?php if ($message): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control rounded-pill" value="<?= htmlspecialchars($utilisateur['email']) ?>" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nom</label>
                        <input type="text" name="nom" class="form-control rounded-pill" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Prénom</label>
                        <input type="text" name="prenom" class="form-control rounded-pill" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nouveau mot de passe</label>
                    <input type="password" name="new_password" class="form-control rounded-pill" placeholder="Laisser vide pour ne pas changer">
                    <small class="text-muted">Minimum 6 caractères</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rôle</label>
                    <select name="role" class="form-select rounded-pill">
                        <option value="client" <?= $utilisateur['role'] == 'client' ? 'selected' : '' ?>>Client</option>
                        <option value="commercial" <?= $utilisateur['role'] == 'commercial' ? 'selected' : '' ?>>Commercial</option>
                        <option value="admin" <?= $utilisateur['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Agence (pour les commerciaux)</label>
                    <select name="agence_id" class="form-select rounded-pill">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($agences as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $utilisateur['agence_id'] == $a['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nom'] . ' - ' . $a['ville']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i>Enregistrer</button>
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