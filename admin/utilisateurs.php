<?php
require_once '../config/init.php';
$requiredRole = 'admin';
require_once '../config/auth.php';
$pdo = Database::getInstance();
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] != $_SESSION['user_id']) {
    $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: utilisateurs.php');
    exit;
}
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion utilisateurs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users me-2"></i>Gestion des utilisateurs</h1>
        <a href="utilisateurs_ajout.php" class="btn btn-primary rounded-pill"><i class="fas fa-plus"></i> Ajouter</a>
    </div>
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>ID</th><th>Email</th><th>Nom</th><th>Prénom</th><th>Rôle</th><th>Agence</th><th>Inscription</th><th>Actions</th></tr></thead><tbody><?php foreach($utilisateurs as $u): ?><tr><td><?= $u['id'] ?></td><td><?= htmlspecialchars($u['email']) ?></td><td><?= htmlspecialchars($u['nom']) ?></td><td><?= htmlspecialchars($u['prenom']) ?></td><td><?= $u['role'] ?></td><td><?= $u['agence_id'] ?: '-' ?></td><td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td><td><a href="utilisateurs_modifier.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-edit"></i></a> <?php if($u['id'] != $_SESSION['user_id']): ?><a href="utilisateurs.php?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>