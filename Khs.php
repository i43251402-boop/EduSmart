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

$mahasiswa_list = [];
$selected_mahasiswa_id = null;
$khs_data = [];
$info_mhs = null;
$ipk = null;

if ($role == 'dosen') {
    $stmt = $db->query("SELECT id, nama, nim, prodi FROM users WHERE role = 'mahasiswa' ORDER BY nama");
    $mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (isset($_GET['mahasiswa_id'])) {
        $selected_mahasiswa_id = (int)$_GET['mahasiswa_id'];
        $stmt = $db->prepare("SELECT id, nama, nim, prodi FROM users WHERE id = ? AND role = 'mahasiswa'");
        $stmt->execute([$selected_mahasiswa_id]);
        $info_mhs = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($info_mhs) {
            $mhs = new Mahasiswa($info_mhs['id'], '', $info_mhs['nama'], $info_mhs['nim'], $info_mhs['prodi']);
            $khs_data = $mhs->getKHS();
            $ipk = $mhs->hitungIPK();
        }
    }
} else {
    $user = User::getCurrentUser();
    $info_mhs = [
        'id' => $user->getId(),
        'nama' => $user->getNama(),
        'nim' => $user->getNim(),
        'prodi' => $user->getProdi()
    ];
    $khs_data = $user->getKHS();
    $ipk = $user->hitungIPK();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KHS - EduSmart</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .btn-print {
            background: #1e3a8a;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            text-decoration: none;
            margin-left: 1rem;
            font-size: 0.9rem;
        }
        .btn-print:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
    </style>
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
            <a href="khs.php" class="active"><i class="fas fa-file-alt"></i> KHS Mahasiswa</a>
            <a href="ipk.php"><i class="fas fa-chart-line"></i> Hitung IPK</a>
        <?php else: ?>
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="khs.php" class="active"><i class="fas fa-file-alt"></i> KHS Saya</a>
            <a href="ipk.php"><i class="fas fa-chart-line"></i> IPK Saya</a>
            <a href="laporan_cetak.php?mode=khs" class="btn-print no-print"><i class="fas fa-print"></i> Cetak KHS</a>
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
                            <option value="">-- Pilih Mahasiswa --</option>
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
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fas fa-id-card"></i> Informasi Mahasiswa</h3>
                    <?php if ($role == 'mahasiswa'): ?>
                        <a href="laporan_cetak.php?mode=khs" target="_blank" class="btn-primary" style="padding:0.3rem 1rem;"><i class="fas fa-print"></i> Cetak KHS</a>
                    <?php endif; ?>
                </div>
                <p><strong>Nama:</strong> <?= htmlspecialchars($info_mhs['nama']) ?></p>
                <p><strong>NIM:</strong> <?= htmlspecialchars($info_mhs['nim']) ?></p>
                <p><strong>Program Studi:</strong> <?= htmlspecialchars($info_mhs['prodi']) ?></p>
                <p><strong>IPK:</strong> <strong style="color:#f97316;"><?= $ipk ?></strong></p>
            </div>

            <div class="card">
                <h3><i class="fas fa-table-list"></i> Daftar Nilai Mata Kuliah</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Semester</th><th>Tahun Akademik</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($khs_data): ?>
                            <?php foreach ($khs_data as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['kode']) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= $row['sks'] ?></td>
                                <td><strong><?= $row['nilai_huruf'] ?></strong></td>
                                <td><?= $row['semester'] ?></td>
                                <td><?= $row['tahun_akademik'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;">Belum ada nilai</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>