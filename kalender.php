<?php
require_once __DIR__ . '/../config/database.php';

class Kalender {
    public static function getAll() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM kalender_akademik ORDER BY tanggal DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add($judul, $tanggal, $deskripsi, $tipe = 'acara') {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO kalender_akademik (judul, tanggal, deskripsi, tipe) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$judul, $tanggal, $deskripsi, $tipe]);
    }

    public static function update($id, $judul, $tanggal, $deskripsi, $tipe) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE kalender_akademik SET judul=?, tanggal=?, deskripsi=?, tipe=? WHERE id=?");
        return $stmt->execute([$judul, $tanggal, $deskripsi, $tipe, $id]);
    }

    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM kalender_akademik WHERE id=?");
        return $stmt->execute([$id]);
    }
}
?>