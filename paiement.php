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
    // Simuler un paiement (aucune validation réelle)
    $pdo = Database::getInstance();
    try {
        // Enregistrer l'achat
        $stmt = $pdo->prepare("INSERT INTO achats (utilisateur_id, bien_id, montant) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $bien_id, $bien->getPrix()]);
        
        // Mettre à jour le statut du bien
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
    <title>Paiement simulé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5" style="max-width: 600px;">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <h4 class="alert-heading">Félicitations !</h4>
                <p>Votre achat a bien été simulé. Vous pouvez consulter votre historique dans votre espace client.</p>
                <hr>
                <a href="client/dashboard.php" class="btn btn-primary">Voir mes achats</a>
                <a href="details.php?id=<?= $bien_id ?>" class="btn btn-outline-secondary">Retour à l'annonce</a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Simulation d'achat</h4>
                </div>
                <div class="card-body">
                    <h5><?= htmlspecialchars($bien->getTitre()) ?></h5>
                    <p class="h3 text-primary"><?= number_format($bien->getPrix(), 0, ',', ' ') ?> €</p>
                    <hr>
                    <p>Ceci est une simulation de paiement. Aucune transaction réelle n'aura lieu.</p>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="card" class="form-label">Numéro de carte (simulé)</label>
                            <input type="text" class="form-control" id="card" value="4242 4242 4242 4242" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="exp" class="form-label">Date d'expiration</label>
                                <input type="text" class="form-control" id="exp" value="12/26" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" value="123" readonly>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100">Confirmer l'achat simulé</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>