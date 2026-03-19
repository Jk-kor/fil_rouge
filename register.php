<?php
require_once('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';

    if ($email && $password && $nom && $prenom) {
        // Vérifier si l'email existe déjà
        if (Utilisateur::findByEmail($email)) {
            $error = "Cet email est déjà utilisé.";
        } else {
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
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>