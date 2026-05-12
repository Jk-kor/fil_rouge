<?php
// Fichier : includes/header.php (version avec bouton publication)
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="/index.php">
            <i class="fas fa-building me-2"></i>ImmoApp
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?= ($_SERVER['REQUEST_URI'] == '/index.php') ? 'active' : '' ?>" href="/index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'annonces.php') !== false) ? 'active' : '' ?>" href="/annonces.php">Annonces</a></li>
                <?php if (Utilisateur::isLogged()): 
                    $user = Utilisateur::getCurrentUser();
                ?>
                    <!-- Bouton "Publier une annonce" pour commercial/admin -->
                    <?php if ($user->hasRole('commercial') || $user->hasRole('admin')): ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-primary rounded-pill" href="/commercial/ajout.php">
                                <i class="fas fa-plus-circle me-1"></i> Publier une annonce
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/client/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Mon compte</a></li>
                            <?php if ($user->hasRole('commercial') || $user->hasRole('admin')): ?>
                                <li><a class="dropdown-item" href="/commercial/dashboard.php"><i class="fas fa-store me-2"></i>Espace commercial</a></li>
                            <?php endif; ?>
                            <?php if ($user->hasRole('admin')): ?>
                                <li><a class="dropdown-item" href="/admin/dashboard.php"><i class="fas fa-shield-alt me-2"></i>Administration</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/login.php">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary text-white rounded-pill px-3 mx-1" href="/register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>