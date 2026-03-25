<?php
// Fichier : config/auth.php
// À inclure après config/init.php dans les pages protégées

if (!Utilisateur::isLogged()) {
    header('Location: ../login.php');
    exit;
}

$user = Utilisateur::getCurrentUser();

// Vérifier le rôle si requis
if (isset($requiredRole) && !$user->hasRole($requiredRole) && !$user->hasRole('admin')) {
    header('Location: ../index.php');
    exit;
}