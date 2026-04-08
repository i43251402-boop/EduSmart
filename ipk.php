<?php
session_start();
require_once 'classes/User.php';
require_once 'classes/Mahasiswa.php';
require_once 'config/database.php';

if (!User::isLoggedIn()) {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['role'];
$db = Database::getInstance()->getConnection();

$selected_mahasiswa_id = null;
$mahasiswa_list = [];
$info_mhs = null;
$khs_data = [];
$ipk_kumulatif = 0;
$ipa_per_semester = [];
$total_sks_per_semester = [];
$total_mutu_per_semester = [];

if ($role == 'dosen') {
    $stmt = $db->query("SELECT id, nama, nim, prodi FROM users WHERE role = 'mahasiswa' ORDER BY nama");
    $mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (isset($_GET['mahasiswa_id'])) {
        $selected_mahasiswa_id = (int)$_GET['mahasiswa_id'];
        $stmt = $db->prepare("SELECT id, nama, nim, prodi FROM users WHERE id = ? AND role = 'mahasiswa'");
        $stmt->execute([$selected_mahasiswa_id]);
        $info_mhs = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $info_mhs = $mahasiswa_list[0] ?? null;
        if ($info_mhs) $selected_mahasiswa_id = $info_mhs['id'];
    }
} else {
    $user = User::getCurrentUser();
    $info_mhs = ['id' => $user->getId(), 'nama' => $user->getNama(), 'nim' => $user->getNim(), 'prodi' => $user->getProdi()];
    $selected_mahasiswa_id = $user->getId();
}

if ($info_mhs) {
    $mhs = new Mahasiswa($info_mhs['id'], '', $info_mhs['nama'], $info_mhs['nim'], $info_mhs['prodi']);
    $khs_data = $mhs->getKHS();
    $ipk_kumulatif = $mhs->hitungIPK();
    foreach ($khs_data as $row) {
        $semester_key = $row['tahun_akademik'] . ' - ' . $row['semester'];
        $sks = $row['sks'];
        $mutu = match($row['nilai_huruf']) {
            'A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, default => 0,
        };
        if (!isset($total_sks_per_semester[$semester_key])) {
            $total_sks_per_semester[$semester_key] = 0;
            $total_mutu_per_semester[$semester_key] = 0;
        }
        $total_sks_per_semester[$semester_key] += $sks;
        $total_mutu_per_semester[$semester_key] += $mutu * $sks;
    }
    foreach ($total_sks_per_semester as $sem => $total_sks) {
        $ipa_per_semester[$sem] = ($total_sks > 0) ? round($total_mutu_per_semester[$sem] / $total_sks, 2) : 0;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hitung IPK - EduSmart</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="animated-bg"></div>
<div class="blob" style="top:-20%; left:-10%;"></div>
<div class="blob" style="bottom:-10%; right:-5%; width:60vw; height:60vw;"></div>

<div class="dashboard-container">
    <div class="sidebar">
        <?php if ($role == 'dosen'): ?>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="mahasiswa.php"><i class="fas fa-user-graduate"></i> Manajemen Mahasiswa</a>
            <a href="matakuliah.php"><i class="fas fa-book"></i> Manajemen MK</a>
            <a href="nilai.php"><i class="fas fa-pen-fancy"></i> Input Nilai</a>
            <a href="khs.php"><i class="fas fa-file-alt"></i> KHS Mahasiswa</a>
            <a href="ipk.php" class="active"><i class="fas fa-chart-line"></i> Hitung IPK</a>
        <?php else: ?>
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="khs.php"><i class="fas fa-file-alt"></i> KHS Saya</a>
            <a href="ipk.php" class="active"><i class="fas fa-chart-line"></i> IPK Saya</a>
            <a href="laporan_cetak.php?mode=khs"><i class="fas fa-print"></i> Cetak KHS</a>
        <?php endif; ?>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['nama']) ?> (<?= $role ?>)</span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <?php if ($role == 'dosen'): ?>
            <div class="card">
                <h3><i class="fas fa-search"></i> Pilih Mahasiswa</h3>
                <form method="GET">
                    <div class="form-group">
                        <select name="mahasiswa_id" onchange="this.form.submit()">
                            <option value="">-- Pilih --</option>
                            <?php foreach ($mahasiswa_list as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($selected_mahasiswa_id == $m['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nim']) ?> - <?= htmlspecialchars($m['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($info_mhs): ?>
            <div class="card">
                <h3><i class="fas fa-user-graduate"></i> Informasi Mahasiswa</h3>
                <p><strong>Nama:</strong> <?= htmlspecialchars($info_mhs['nama']) ?></p>
                <p><strong>NIM:</strong> <?= htmlspecialchars($info_mhs['nim']) ?></p>
                <p><strong>Program Studi:</strong> <?= htmlspecialchars($info_mhs['prodi']) ?></p>
            </div>

            <?php if (!empty($ipa_per_semester)): ?>
            <div class="card">
                <h3><i class="fas fa-chart-bar"></i> Indeks Prestasi Akademik (IPA) per Semester</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>Semester</th><th>Total SKS</th><th>Total Mutu</th><th>IPA Semester</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total_sks = 0;
                        $grand_total_mutu = 0;
                        foreach ($ipa_per_semester as $sem => $ipa): 
                            $sks_sem = $total_sks_per_semester[$sem];
                            $mutu_sem = $total_mutu_per_semester[$sem];
                            $grand_total_sks += $sks_sem;
                            $grand_total_mutu += $mutu_sem;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($sem) ?></td>
                            <td><?= $sks_sem ?></td>
                            <td><?= $mutu_sem ?></td>
                            <td><span class="btn-primary" style="padding:0.2rem 0.6rem; border-radius:2rem;"><?= $ipa ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot style="background:rgba(0,0,0,0.5); font-weight:bold;">
                        <tr>
                            <td><strong>TOTAL / IPK KUMULATIF</strong></td>
                            <td><strong><?= $grand_total_sks ?></strong></td>
                            <td><strong><?= $grand_total_mutu ?></strong></td>
                            <td><span class="btn-primary" style="background:#f97316;"><?= $ipk_kumulatif ?></span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-warning">Belum ada nilai untuk mahasiswa ini.</div>
            <?php endif; ?>

            <div class="card">
                <h3><i class="fas fa-list"></i> Detail Nilai Mata Kuliah</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Mutu</th><th>Bobot</th><th>Semester</th><th>Tahun</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($khs_data as $row):
                            $mutu = match($row['nilai_huruf']) {
                                'A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, default => 0,
                            };
                            $bobot = $mutu * $row['sks'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['kode']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= $row['sks'] ?></td>
                            <td><?= $row['nilai_huruf'] ?></td>
                            <td><?= $mutu ?></td>
                            <td><?= $bobot ?></td>
                            <td><?= $row['semester'] ?></td>
                            <td><?= $row['tahun_akademik'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>