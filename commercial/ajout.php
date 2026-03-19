// Traitement du formulaire d'ajout (simplifié)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bien = new Bien($_POST); // $_POST doit contenir les champs
    $bien->setCommercialId($_SESSION['user_id']); // à adapter
    $bien->save();

    // Upload des images
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadDir . $filename;
                if (move_uploaded_file($tmp_name, $destination)) {
                    $bien->addPhoto($destination, $key);
                }
            }
        }
    }
    header('Location: commercial/annonces.php');
    exit;
}