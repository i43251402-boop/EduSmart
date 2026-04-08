<?php
class Database {
    private static $instance = null;
    private $conn;
    private $dbFile = __DIR__ . '/../akademik.db';

    private function __construct() {
        $isNew = !file_exists($this->dbFile);
        $this->conn = new PDO("sqlite:" . $this->dbFile);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($isNew) {
            $this->createTables();
            $this->seedData();
        } else {
            $this->addColumnIfNotExists();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function addColumnIfNotExists() {
        try {
            $this->conn->exec("ALTER TABLE matakuliah ADD COLUMN prodi TEXT DEFAULT 'Umum'");
        } catch (PDOException $e) {
            // Kolom sudah ada, abaikan
        }
    }

    private function createTables() {
        $sql = "
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE,
                password TEXT,
                role TEXT,
                nama TEXT,
                nim TEXT,
                nidn TEXT,
                prodi TEXT
            );
            CREATE TABLE matakuliah (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kode TEXT UNIQUE,
                nama TEXT,
                sks INTEGER,
                prodi TEXT DEFAULT 'Umum'
            );
            CREATE TABLE nilai (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                mahasiswa_id INTEGER,
                matakuliah_id INTEGER,
                nilai_huruf TEXT,
                semester TEXT,
                tahun_akademik TEXT,
                FOREIGN KEY(mahasiswa_id) REFERENCES users(id),
                FOREIGN KEY(matakuliah_id) REFERENCES matakuliah(id)
            );
            CREATE TABLE kalender_akademik (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                judul TEXT NOT NULL,
                tanggal DATE NOT NULL,
                deskripsi TEXT,
                tipe TEXT DEFAULT 'acara'
            );
        ";
        $this->conn->exec($sql);
    }

    private function seedData() {
        $dosenPass = password_hash('123', PASSWORD_DEFAULT);
        $mhs1Pass = password_hash('123', PASSWORD_DEFAULT);
        $mhs2Pass = password_hash('123', PASSWORD_DEFAULT);
        $this->conn->exec("
            INSERT INTO users (id, username, password, role, nama, nim, nidn, prodi) VALUES
            (1, 'dosen', '$dosenPass', 'dosen', 'Dr. Ahmad Suherman', NULL, '197001012010011001', NULL),
            (2, 'mhs1', '$mhs1Pass', 'mahasiswa', 'Budi Santoso', '20241001', NULL, 'Informatika'),
            (3, 'mhs2', '$mhs2Pass', 'mahasiswa', 'Siti Aminah', '20241002', NULL, 'Sistem Informasi')
        ");
        $this->conn->exec("
            INSERT INTO matakuliah (id, kode, nama, sks, prodi) VALUES
            (1, 'MK101', 'Pemrograman Web', 3, 'Informatika'),
            (2, 'MK102', 'Basis Data', 3, 'Umum'),
            (3, 'MK103', 'Pemrograman Berorientasi Objek', 3, 'Informatika'),
            (4, 'MK104', 'Matematika Diskrit', 2, 'Umum'),
            (5, 'MK105', 'Jaringan Komputer', 3, 'Sistem Informasi')
        ");
        $this->conn->exec("
            INSERT INTO nilai (mahasiswa_id, matakuliah_id, nilai_huruf, semester, tahun_akademik) VALUES
            (2, 1, 'A', 'Ganjil', '2025/2026'),
            (2, 2, 'B', 'Ganjil', '2025/2026'),
            (2, 3, 'A', 'Genap', '2025/2026')
        ");
    }
}
?>