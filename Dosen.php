<?php
require_once 'User.php';

class Dosen extends User {
    private $nidn;
    
    public function __construct($id, $username, $nama, $nidn) {
        parent::__construct($id, $username, $nama, 'dosen');
        $this->nidn = $nidn;
    }
    
    public function getNidn() { return $this->nidn; }
    
    public function getDashboardLink() {
        return "dashboard.php";
    }
    
    public function cetakLaporan() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT u.id, u.nim, u.nama, u.prodi,
                   COUNT(DISTINCT n.id) as jumlah_matkul,
                   SUM(CASE 
                       WHEN n.nilai_huruf = 'A' THEN 4 * mk.sks
                       WHEN n.nilai_huruf = 'B' THEN 3 * mk.sks
                       WHEN n.nilai_huruf = 'C' THEN 2 * mk.sks
                       WHEN n.nilai_huruf = 'D' THEN 1 * mk.sks
                       ELSE 0
                   END) as total_mutu,
                   SUM(CASE WHEN n.id IS NOT NULL THEN mk.sks ELSE 0 END) as total_sks
            FROM users u
            LEFT JOIN nilai n ON u.id = n.mahasiswa_id
            LEFT JOIN matakuliah mk ON n.matakuliah_id = mk.id
            WHERE u.role = 'mahasiswa'
            GROUP BY u.id
            ORDER BY u.nim
        ");
        $mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $html = "<div class='laporan-mahasiswa'><h2>Laporan Data Mahasiswa</h2>";
        $html .= "<p><strong>Dosen:</strong> {$this->nama}</p>";
        $html .= "<table class='table'><thead><tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>Jumlah MK</th><th>IPK</th></tr></thead><tbody>";
        foreach ($mahasiswa as $m) {
            $ipk = ($m['total_sks'] > 0) ? round($m['total_mutu'] / $m['total_sks'], 2) : 0;
            $html .= "<tr>
                <td>{$m['nim']}</td>
                <td>{$m['nama']}</td>
                <td>{$m['prodi']}</td>
                <td>{$m['jumlah_matkul']}</td>
                <td>{$ipk}</td>
            </tr>";
        }
        $html .= "</tbody></table></div>";
        return $html;
    }
}
?>