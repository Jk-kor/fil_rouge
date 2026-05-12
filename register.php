<?php
require_once 'config/init.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');

    if (!$email) $errors[] = "Email invalide.";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas.";
    if (empty($nom) || empty($prenom)) $errors[] = "Le nom et le prénom sont requis.";
    if (empty($errors) && Utilisateur::findByEmail($email)) $errors[] = "Cet email est déjà utilisé.";

    if (empty($errors)) {
        $user = new Utilisateur([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'nom' => $nom,
            'prenom' => $prenom,
            'role' => 'client'
        ]);
        $user->save();
        $user->login();
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card border-0 shadow-lg rounded-4" style="width: 500px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                <h2 class="fw-bold">Inscription</h2>
                <p class="text-muted">Créez votre compte gratuitement</p>
            </div>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold"><i class="fas fa-envelope me-1"></i> Email</label>
                    <input type="email" class="form-control rounded-pill" id="email" name="email" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="nom" class="form-label fw-semibold">Nom</label>
                        <input type="text" class="form-control rounded-pill" id="nom" name="nom" required>
                    </div>
                    <div class="col-md-6">
                        <label for="prenom" class="form-label fw-semibold">Prénom</label>
                        <input type="text" class="form-control rounded-pill" id="prenom" name="prenom" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold"><i class="fas fa-lock me-1"></i> Mot de passe</label>
                    <input type="password" class="form-control rounded-pill" id="password" name="password" required>
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="form-label fw-semibold"><i class="fas fa-check-circle me-1"></i> Confirmation</label>
                    <input type="password" class="form-control rounded-pill" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mb-3">S'inscrire</button>
                <div class="text-center">
                    <a href="login.php" class="text-decoration-none">Déjà inscrit ? Connectez-vous</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>