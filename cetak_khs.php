<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

$selectedMahasiswaId = $userId;
if ($role == 'dosen' && isset($_GET['mahasiswa_id'])) {
    $selectedMahasiswaId = (int)$_GET['mahasiswa_id'];
}

$stmt = $db->prepare("SELECT id, nama, nim, prodi FROM users WHERE id = ? AND role = 'mahasiswa'");
$stmt->execute([$selectedMahasiswaId]);
$mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$mahasiswa) die("Data mahasiswa tidak ditemukan.");

$stmt = $db->prepare("
    SELECT mk.kode, mk.nama as matkul_nama, mk.sks, n.nilai_huruf, n.semester, n.tahun_akademik
    FROM nilai n
    JOIN matakuliah mk ON n.matakuliah_id = mk.id
    WHERE n.mahasiswa_id = ?
    ORDER BY n.tahun_akademik DESC, n.semester DESC, mk.kode
");
$stmt->execute([$selectedMahasiswaId]);
$nilaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalSks = 0; $totalMutu = 0;
foreach ($nilaiList as $row) {
    $sks = $row['sks'];
    switch ($row['nilai_huruf']) {
        case 'A': $mutu = 4; break;
        case 'B': $mutu = 3; break;
        case 'C': $mutu = 2; break;
        case 'D': $mutu = 1; break;
        default: $mutu = 0;
    }
    $totalMutu += $mutu * $sks;
    $totalSks += $sks;
}
$ipk = ($totalSks > 0) ? round($totalMutu / $totalSks, 2) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak KHS - <?= htmlspecialchars($mahasiswa['nama']) ?></title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Times New Roman',Georgia,serif;padding:2rem;font-size:11pt;}
        .laporan{max-width:1100px;margin:0 auto;background:white;padding:2rem;}
        .kop{text-align:center;border-bottom:2px solid #1e3a8a;margin-bottom:1.5rem;padding-bottom:1rem;}
        .kop h1{font-size:1.4rem;color:#1e3a8a;}
        .kop h2{font-size:1rem;font-weight:normal;color:#334155;}
        .kop p{font-size:0.75rem;color:#475569;}
        .judul-laporan{text-align:center;margin:1.5rem 0 1rem;}
        .judul-laporan h3{font-size:1.2rem;text-transform:uppercase;text-decoration:underline;}
        .info-laporan{display:flex;justify-content:space-between;margin:1rem 0;border-bottom:1px dashed #cbd5e1;padding-bottom:0.5rem;}
        .info-mahasiswa{display:flex;flex-wrap:wrap;gap:1.5rem;margin-bottom:1.5rem;background:#f8fafc;padding:1rem;border-radius:0.5rem;}
        .info-item{flex:1;}
        .info-item label{font-size:0.7rem;text-transform:uppercase;color:#475569;}
        .info-item p{font-size:1rem;font-weight:500;margin-top:4px;}
        table{width:100%;border-collapse:collapse;margin:1.5rem 0;}
        th,td{border:1px solid #cbd5e1;padding:0.6rem;text-align:left;}
        th{background:#f1f5f9;text-align:center;}
        .text-center{text-align:center;}
        .ttd{margin-top:2rem;display:flex;justify-content:flex-end;text-align:center;}
        .ttd div{width:220px;}
        .garis{border-top:1px solid black;width:100%;margin:1.5rem 0 0.2rem;}
        .footer-cetak{margin-top:1.5rem;font-size:0.7rem;text-align:center;color:#64748b;border-top:1px solid #e2e8f0;padding-top:0.5rem;}
        @media print{body{padding:0;}}
    </style>
</head>
<body onload="window.print()">
<div class="laporan">
    <div class="kop">
        <h1>UNIVERSITAS TEKNOLOGI DIGITAL</h1>
        <h2>Sistem Akademik Mini</h2>
        <p>Jl. Teknologi No. 123, Kota Bandung 40123 | Telp. (022) 1234567</p>
    </div>
    <div class="judul-laporan"><h3>KARTU HASIL STUDI (KHS)</h3></div>
    <div class="info-laporan">
        <span>No. Dokumen : KHS/<?= $mahasiswa['nim'] ?>/<?= date('Ymd') ?></span>
        <span>Tanggal Cetak : <?= date('d-m-Y') ?></span>
    </div>
    <div class="info-mahasiswa">
        <div class="info-item"><label>Nama</label><p><?= htmlspecialchars($mahasiswa['nama']) ?></p></div>
        <div class="info-item"><label>NIM</label><p><?= htmlspecialchars($mahasiswa['nim']) ?></p></div>
        <div class="info-item"><label>Program Studi</label><p><?= htmlspecialchars($mahasiswa['prodi']) ?></p></div>
        <div class="info-item"><label>IPK</label><p><strong><?= $ipk ?></strong></p></div>
    </div>
    <table>
        <thead><tr><th>No</th><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Semester</th><th>Tahun</th></tr></thead>
        <tbody>
            <?php if (empty($nilaiList)): ?>
                <td><td colspan="7">Belum ada nilai</td></tr>
            <?php else: $no=1; foreach ($nilaiList as $row): ?>
                <tr><td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['kode']) ?></td>
                    <td><?= htmlspecialchars($row['matkul_nama']) ?></td>
                    <td class="text-center"><?= $row['sks'] ?></td>
                    <td class="text-center"><?= $row['nilai_huruf'] ?></td>
                    <td><?= $row['semester'] ?></td>
                    <td><?= $row['tahun_akademik'] ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
        <tfoot><tr><td colspan="3"><strong>Total SKS</strong></td><td class="text-center"><strong><?= $totalSks ?></strong></td><td colspan="3"><strong>IPK: <?= $ipk ?></strong></td></tr></tfoot>
    </table>
    <div class="ttd"><div><p>Bandung, <?= date('d-m-Y') ?></p><p>Kepala Bagian Akademik,</p><div class="garis"></div><p><strong>Dr. Ir. Rina Suryani, M.T.</strong></p><p>NIP. 197512312003122001</p></div></div>
    <div class="footer-cetak"><p>Kartu Hasil Studi ini dicetak secara otomatis dari Sistem Akademik Mini.</p></div>
</div>
</body>
</html>