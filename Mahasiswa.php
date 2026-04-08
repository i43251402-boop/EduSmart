<?php
require_once 'User.php';

class Mahasiswa extends User {
    private $nim;
    private $prodi;
    
    public function __construct($id, $username, $nama, $nim, $prodi) {
        parent::__construct($id, $username, $nama, 'mahasiswa');
        $this->nim = $nim;
        $this->prodi = $prodi;
    }
    
    public function getNim() { return $this->nim; }
    public function getProdi() { return $this->prodi; }
    
    public function getDashboardLink() {
        return "dashboard.php";
    }
    
    public function getKHS() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT mk.kode, mk.nama, mk.sks, n.nilai_huruf, n.semester, n.tahun_akademik
            FROM nilai n
            JOIN matakuliah mk ON n.matakuliah_id = mk.id
            WHERE n.mahasiswa_id = ?
            ORDER BY n.tahun_akademik DESC, n.semester DESC, mk.kode
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function hitungIPK() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT mk.sks, n.nilai_huruf
            FROM nilai n
            JOIN matakuliah mk ON n.matakuliah_id = mk.id
            WHERE n.mahasiswa_id = ?
        ");
        $stmt->execute([$this->id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalMutu = 0;
        $totalSks = 0;
        foreach ($rows as $row) {
            $sks = $row['sks'];
            $mutu = match($row['nilai_huruf']) {
                'A' => 4,
                'B' => 3,
                'C' => 2,
                'D' => 1,
                default => 0,
            };
            $totalMutu += $mutu * $sks;
            $totalSks += $sks;
        }
        return $totalSks > 0 ? round($totalMutu / $totalSks, 2) : 0;
    }
    
    public function cetakLaporan() {
        $khs = $this->getKHS();
        $ipk = $this->hitungIPK();
        $html = "<div class='laporan-khs'><h2>Kartu Hasil Studi (KHS)</h2>";
        $html .= "<p><strong>Nama:</strong> {$this->nama}</p>";
        $html .= "<p><strong>NIM:</strong> {$this->nim}</p>";
        $html .= "<p><strong>Program Studi:</strong> {$this->prodi}</p>";
        $html .= "<p><strong>IPK:</strong> {$ipk}</p>";
        $html .= "<table class='table'><thead><tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Semester</th><th>Tahun</th></tr></thead><tbody>";
        foreach ($khs as $row) {
            $html .= "<tr>
                <td>{$row['kode']}</td>
                <td>{$row['nama']}</td>
                <td>{$row['sks']}</td>
                <td>{$row['nilai_huruf']}</td>
                <td>{$row['semester']}</td>
                <td>{$row['tahun_akademik']}</td>
            </tr>";
        }
        $html .= "</tbody></table></div>";
        return $html;
    }
}
?>