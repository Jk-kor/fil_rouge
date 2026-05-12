<?php
// Fichier : login.php
require_once 'config/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $user = Utilisateur::findByEmail($email);
        if ($user && $user->verifyPassword($password)) {
            $user->login();
            header('Location: index.php');
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card border-0 shadow-lg rounded-4" style="width: 450px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fas fa-key fa-3x text-primary mb-3"></i>
                <h2 class="fw-bold">Connexion</h2>
                <p class="text-muted">Accédez à votre espace personnel</p>
            </div>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger rounded-pill"><i class="fas fa-exclamation-circle me-2"></i> <?= $error ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold"><i class="fas fa-envelope me-1"></i> Email</label>
                    <input type="email" class="form-control form-control-lg rounded-pill" id="email" name="email" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold"><i class="fas fa-lock me-1"></i> Mot de passe</label>
                    <input type="password" class="form-control form-control-lg rounded-pill" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mb-3">Se connecter</button>
                <div class="text-center">
                    <a href="register.php" class="text-decoration-none">Pas encore inscrit ? Créez un compte</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>