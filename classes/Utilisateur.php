<?php
// Fichier : classes/Utilisateur.php
class Utilisateur {
    private $id;
    private $email;
    private $password;
    private $nom;
    private $prenom;
    private $role;
    private $agence_id;
    private $date_inscription;

    // Hydratation
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

    // Getters / Setters essentiels
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getPassword() { return $this->password; }
    public function setPassword($pwd) { $this->password = $pwd; }

    public function getNom() { return $this->nom; }
    public function setNom($nom) { $this->nom = $nom; }

    public function getPrenom() { return $this->prenom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }

    public function getRole() { return $this->role; }
    public function setRole($role) { $this->role = $role; }

    public function getAgenceId() { return $this->agence_id; }
    public function setAgenceId($agence_id) { $this->agence_id = $agence_id; }

    /**
     * Trouve un utilisateur par email
     */
    public static function findByEmail($email) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row) {
            return new self($row);
        }
        return null;
    }

    /**
     * Trouve un utilisateur par ID
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            return new self($row);
        }
        return null;
    }

    /**
     * Vérifie le mot de passe
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    /**
     * Enregistre un nouvel utilisateur (inscription)
     */
    public function save() {
        $pdo = Database::getInstance();
        if ($this->id) {
            // Mise à jour
            $sql = "UPDATE utilisateurs SET email=:email, nom=:nom, prenom=:prenom, role=:role, agence_id=:agence_id WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':email' => $this->email,
                ':nom' => $this->nom,
                ':prenom' => $this->prenom,
                ':role' => $this->role,
                ':agence_id' => $this->agence_id,
                ':id' => $this->id
            ]);
        } else {
            // Insertion
            $sql = "INSERT INTO utilisateurs (email, password, nom, prenom, role, agence_id) VALUES (:email, :password, :nom, :prenom, :role, :agence_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':email' => $this->email,
                ':password' => $this->password, // déjà hashé
                ':nom' => $this->nom,
                ':prenom' => $this->prenom,
                ':role' => $this->role ?? 'client',
                ':agence_id' => $this->agence_id
            ]);
            $this->id = $pdo->lastInsertId();
        }
    }

    /**
     * Connexion : stocke l'utilisateur en session
     */
    public function login() {
        $_SESSION['user_id'] = $this->id;
        $_SESSION['user_role'] = $this->role;
        $_SESSION['user_email'] = $this->email;
        $_SESSION['user_nom'] = $this->nom;
        $_SESSION['user_prenom'] = $this->prenom;
    }

    /**
     * Déconnexion
     */
    public static function logout() {
        session_destroy();
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    public static function isLogged() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Récupère l'utilisateur connecté (objet)
     */
    public static function getCurrentUser() {
        if (self::isLogged()) {
            return self::findById($_SESSION['user_id']);
        }
        return null;
    }

    /**
     * Vérifie si l'utilisateur a un rôle donné
     */
    public function hasRole($role) {
        return $this->role === $role;
    }
}