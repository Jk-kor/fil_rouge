<?php
// Fichier : paiement.php
require_once 'config/init.php';

if (!Utilisateur::isLogged()) {
    header('Location: login.php');
    exit;
}

$bien_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$bien_id) {
    header('Location: index.php');
    exit;
}

$bien = Bien::getById($bien_id);
if (!$bien || $bien->getStatut() !== 'disponible') {
    header('Location: index.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Database::getInstance();
    try {
        $stmt = $pdo->prepare("INSERT INTO achats (utilisateur_id, bien_id, montant) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $bien_id, $bien->getPrix()]);
        $bien->setStatut('vendu');
        $bien->save();
        $success = true;
    } catch (Exception $e) {
        $error = "Une erreur est survenue lors de la simulation d'achat.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulation d'achat - ImmoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <?php if ($success): ?>
                <div class="card border-0 shadow-lg rounded-4 text-center p-4">
                    <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                    <h2 class="mb-3">Achat simulé avec succès !</h2>
                    <p class="text-muted">Vous avez acquis <strong><?= htmlspecialchars($bien->getTitre()) ?></strong> pour <strong><?= number_format($bien->getPrix(), 0, ',', ' ') ?> €</strong>.</p>
                    <p>Ceci est une simulation, aucune transaction réelle n’a eu lieu.</p>
                    <div class="d-flex justify-content-center gap-3 mt-3">
                        <a href="client/dashboard.php" class="btn btn-primary rounded-pill px-4">Voir mes achats</a>
                        <a href="details.php?id=<?= $bien_id ?>" class="btn btn-outline-secondary rounded-pill px-4">Retour à l'annonce</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-header bg-primary text-white rounded-top-4 py-3">
                        <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Simulation d'achat</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-home fa-3x text-primary mb-2"></i>
                            <h5><?= htmlspecialchars($bien->getTitre()) ?></h5>
                            <p class="h3 text-primary fw-bold"><?= number_format($bien->getPrix(), 0, ',', ' ') ?> €</p>
                        </div>
                        <hr>
                        <p class="text-muted">Ceci est une simulation. Aucune transaction réelle.</p>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Numéro de carte (simulé)</label>
                                <input type="text" class="form-control rounded-pill" value="4242 4242 4242 4242" readonly>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Date d'expiration</label>
                                    <input type="text" class="form-control rounded-pill" value="12/26" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">CVV</label>
                                    <input type="text" class="form-control rounded-pill" value="123" readonly>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill mt-4">Confirmer l'achat simulé</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>