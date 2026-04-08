<?php
require_once __DIR__ . '/../config/database.php';

class Nilai {
    public static function getAll() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT n.*, u.nama as mahasiswa_nama, u.nim, mk.nama as matakuliah_nama, mk.kode, mk.sks
            FROM nilai n
            JOIN users u ON n.mahasiswa_id = u.id
            JOIN matakuliah mk ON n.matakuliah_id = mk.id
            ORDER BY n.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getByMahasiswa($mahasiswa_id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT mk.kode, mk.nama as matakuliah_nama, mk.sks, n.nilai_huruf, n.semester, n.tahun_akademik, n.id
            FROM nilai n
            JOIN matakuliah mk ON n.matakuliah_id = mk.id
            WHERE n.mahasiswa_id = ?
            ORDER BY n.tahun_akademik DESC, n.semester DESC, mk.kode
        ");
        $stmt->execute([$mahasiswa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function input($mahasiswa_id, $matakuliah_id, $nilai_huruf, $semester, $tahun_akademik) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM nilai WHERE mahasiswa_id = ? AND matakuliah_id = ?");
        $stmt->execute([$mahasiswa_id, $matakuliah_id]);
        if ($stmt->fetch()) {
            $stmt = $db->prepare("UPDATE nilai SET nilai_huruf = ?, semester = ?, tahun_akademik = ? WHERE mahasiswa_id = ? AND matakuliah_id = ?");
            return $stmt->execute([$nilai_huruf, $semester, $tahun_akademik, $mahasiswa_id, $matakuliah_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO nilai (mahasiswa_id, matakuliah_id, nilai_huruf, semester, tahun_akademik) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$mahasiswa_id, $matakuliah_id, $nilai_huruf, $semester, $tahun_akademik]);
        }
    }
    
    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM nilai WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function getAllMahasiswa() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, nama, nim, prodi FROM users WHERE role = 'mahasiswa' ORDER BY nama");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getAllMatakuliah() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, kode, nama, sks FROM matakuliah ORDER BY kode");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>