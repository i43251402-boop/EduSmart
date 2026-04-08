<?php
require_once __DIR__ . '/../config/database.php';
require_once 'Laporan.php';

abstract class User implements Laporan {
    protected $id;
    protected $username;
    protected $nama;
    protected $role;
    
    public function __construct($id, $username, $nama, $role) {
        $this->id = $id;
        $this->username = $username;
        $this->nama = $nama;
        $this->role = $role;
    }
    
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getNama() { return $this->nama; }
    public function getRole() { return $this->role; }
    
    abstract public function getDashboardLink();
    
    public static function login($username, $password) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama'];
            return true;
        }
        return false;
    }
    
    public static function logout() {
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) return null;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return null;
        
        // Load class Dosen atau Mahasiswa
        require_once __DIR__ . '/Dosen.php';
        require_once __DIR__ . '/Mahasiswa.php';
        
        if ($user['role'] == 'mahasiswa') {
            return new Mahasiswa($user['id'], $user['username'], $user['nama'], $user['nim'], $user['prodi']);
        } else {
            return new Dosen($user['id'], $user['username'], $user['nama'], $user['nidn']);
        }
    }
}
?>