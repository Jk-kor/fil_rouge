<?php
// Fichier : classes/Bien.php
class Bien {
    private $id;
    private $titre;
    private $description;
    private $prix;
    private $surface;
    private $pieces;
    private $type;
    private $ville;
    private $code_postal;
    private $adresse;
    private $latitude;
    private $longitude;
    private $statut;
    private $date_creation;
    private $commercial_id;
    private $agence_id;
    private $vue_count;

    // Constructeur (optionnel, on peut hydrater avec un tableau)
    public function __construct($data = []) {
        $this->hydrate($data);
    }

    public function hydrate($data) {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    // Getters / Setters (générés automatiquement, mais je te mets l'essentiel)
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getTitre() { return $this->titre; }
    public function setTitre($titre) { $this->titre = $titre; }

    public function getDescription() { return $this->description; }
    public function setDescription($desc) { $this->description = $desc; }

    public function getPrix() { return $this->prix; }
    public function setPrix($prix) { $this->prix = $prix; }

    public function getSurface() { return $this->surface; }
    public function setSurface($surface) { $this->surface = $surface; }

    public function getPieces() { return $this->pieces; }
    public function setPieces($pieces) { $this->pieces = $pieces; }

    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; }

    public function getVille() { return $this->ville; }
    public function setVille($ville) { $this->ville = $ville; }

    public function getCodePostal() { return $this->code_postal; }
    public function setCodePostal($cp) { $this->code_postal = $cp; }

    public function getAdresse() { return $this->adresse; }
    public function setAdresse($adresse) { $this->adresse = $adresse; }

    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = $statut; }

    public function getVueCount() { return $this->vue_count; }
    public function setVueCount($count) { $this->vue_count = $count; }

    // Méthodes métier

    /**
     * Récupère tous les biens (avec pagination possible)
     */
    public static function getAll($limit = null, $offset = 0) {
        $pdo = Database::getInstance();
        $sql = "SELECT * FROM biens ORDER BY date_creation DESC";
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->query($sql);
        }
        $results = $stmt->fetchAll();
        $biens = [];
        foreach ($results as $row) {
            $biens[] = new self($row);
        }
        return $biens;
    }

    /**
     * Récupère un bien par son ID
     */
    public static function getById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM biens WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            return new self($row);
        }
        return null;
    }

    /**
     * Sauvegarde le bien (insert ou update)
     */
    public function save() {
        $pdo = Database::getInstance();
        if ($this->id) {
            // Mise à jour
            $sql = "UPDATE biens SET titre=:titre, description=:description, prix=:prix, surface=:surface, pieces=:pieces, type=:type, ville=:ville, code_postal=:code_postal, adresse=:adresse, statut=:statut WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titre' => $this->titre,
                ':description' => $this->description,
                ':prix' => $this->prix,
                ':surface' => $this->surface,
                ':pieces' => $this->pieces,
                ':type' => $this->type,
                ':ville' => $this->ville,
                ':code_postal' => $this->code_postal,
                ':adresse' => $this->adresse,
                ':statut' => $this->statut,
                ':id' => $this->id
            ]);
        } else {
            // Insertion
            $sql = "INSERT INTO biens (titre, description, prix, surface, pieces, type, ville, code_postal, adresse, statut, commercial_id, agence_id) 
                    VALUES (:titre, :description, :prix, :surface, :pieces, :type, :ville, :code_postal, :adresse, :statut, :commercial_id, :agence_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titre' => $this->titre,
                ':description' => $this->description,
                ':prix' => $this->prix,
                ':surface' => $this->surface,
                ':pieces' => $this->pieces,
                ':type' => $this->type,
                ':ville' => $this->ville,
                ':code_postal' => $this->code_postal,
                ':adresse' => $this->adresse,
                ':statut' => $this->statut,
                ':commercial_id' => $this->commercial_id ?? $_SESSION['user_id'] ?? null,
                ':agence_id' => $this->agence_id ?? null
            ]);
            $this->id = $pdo->lastInsertId();
        }
    }

    /**
     * Supprime le bien
     */
    public function delete() {
        if ($this->id) {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("DELETE FROM biens WHERE id = ?");
            return $stmt->execute([$this->id]);
        }
        return false;
    }

    /**
     * Incrémente le compteur de vues
     */
    public function incrementVue() {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE biens SET vue_count = vue_count + 1 WHERE id = ?");
        $stmt->execute([$this->id]);
    }

    /**
     * Récupère les photos associées
     */
    public function getPhotos() {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM photos WHERE bien_id = ? ORDER BY ordre");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Ajoute une photo
     */
    public function addPhoto($chemin, $ordre = 0) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO photos (bien_id, chemin, ordre) VALUES (?, ?, ?)");
        return $stmt->execute([$this->id, $chemin, $ordre]);
    }
}