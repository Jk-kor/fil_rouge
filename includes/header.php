<?php
// Fichier : includes/header.php
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">🏠 ImmoApp</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="annonces.php">Annonces</a></li>
                <?php if (Utilisateur::isLogged()): 
                    $user = Utilisateur::getCurrentUser();
                ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if ($user->hasRole('commercial') || $user->hasRole('admin')): ?>
                                <li><a class="dropdown-item" href="commercial/dashboard.php">Espace commercial</a></li>
                            <?php endif; ?>
                            <?php if ($user->hasRole('admin')): ?>
                                <li><a class="dropdown-item" href="admin/dashboard.php">Administration</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="client/dashboard.php">Mon compte</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>