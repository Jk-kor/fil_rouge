<?php
require_once 'config/init.php';
Utilisateur::logout();
header('Location: index.php');
exit;