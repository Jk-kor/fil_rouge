<?php
// Fichier : includes/footer.php
?>
<footer class="bg-dark text-white-50 pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5 class="text-white mb-3">ImmoApp</h5>
                <p>Votre partenaire immobilier de confiance. Des biens vérifiés et un accompagnement personnalisé.</p>
            </div>
            <div class="col-md-4">
                <h5 class="text-white mb-3">Liens utiles</h5>
                <ul class="list-unstyled">
                    <li><a href="/index.php" class="text-white-50 text-decoration-none">Accueil</a></li>
                    <li><a href="/annonces.php" class="text-white-50 text-decoration-none">Toutes les annonces</a></li>
                    <li><a href="/contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="text-white mb-3">Suivez-nous</h5>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white-50"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white-50"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white-50"><i class="fab fa-instagram fa-lg"></i></a>
                </div>
            </div>
        </div>
        <hr class="mt-4">
        <div class="text-center">
            <small>&copy; <?= date('Y') ?> ImmoApp - Tous droits réservés</small>
        </div>
    </div>
</footer>