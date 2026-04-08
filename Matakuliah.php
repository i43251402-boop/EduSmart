<?php
require_once __DIR__ . '/../config/database.php';

class Matakuliah {
    public static function getAll() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM matakuliah ORDER BY prodi, kode");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM matakuliah WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function getByProdi($prodi) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM matakuliah WHERE prodi = ? OR prodi = 'Umum' ORDER BY kode");
        $stmt->execute([$prodi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function create($kode, $nama, $sks, $prodi) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO matakuliah (kode, nama, sks, prodi) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$kode, $nama, $sks, $prodi]);
    }
    
    public static function update($id, $kode, $nama, $sks, $prodi) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE matakuliah SET kode = ?, nama = ?, sks = ?, prodi = ? WHERE id = ?");
        return $stmt->execute([$kode, $nama, $sks, $prodi, $id]);
    }
    
    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM matakuliah WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function getAllProdi() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT DISTINCT prodi FROM matakuliah ORDER BY prodi");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>