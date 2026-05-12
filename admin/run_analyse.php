<?php
require_once '../config/init.php';
if (!Utilisateur::isLogged() || !Utilisateur::getCurrentUser()->hasRole('admin')) {
    header('Location: ../index.php');
    exit;
}

// Exécution du script Python
$output = [];
$return_var = 0;
$command = 'python3 ../python/analyse.py 2>&1';
exec($command, $output, $return_var);

if ($return_var === 0) {
    header('Location: analyse.php?success=1');
} else {
    // Log d'erreur
    file_put_contents('../logs/python_error.log', implode("\n", $output), FILE_APPEND);
    header('Location: analyse.php?error=1');
}
exit;