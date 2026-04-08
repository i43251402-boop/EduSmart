<?php
session_start();
require_once 'classes/Nilai.php';
require_once 'classes/Matakuliah.php';
require_once 'classes/User.php';

if (!User::isLoggedIn() || $_SESSION['role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

$message = '';
$selected_mahasiswa_id = isset($_GET['mahasiswa_id']) ? (int)$_GET['mahasiswa_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_nilai'])) {
    $mahasiswa_id = (int)$_POST['mahasiswa_id'];
    $matakuliah_id = (int)$_POST['matakuliah_id'];
    $nilai_huruf = $_POST['nilai_huruf'];
    $semester = $_POST['semester'];
    $tahun_akademik = $_POST['tahun_akademik'];
    if (Nilai::input($mahasiswa_id, $matakuliah_id, $nilai_huruf, $semester, $tahun_akademik)) {
        $message = "✅ Nilai berhasil disimpan!";
        $selected_mahasiswa_id = $mahasiswa_id;
    } else {
        $message = "❌ Gagal menyimpan nilai!";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (Nilai::delete($id)) {
        $message = "✅ Nilai berhasil dihapus!";
    } else {
        $message = "❌ Gagal menghapus nilai!";
    }
}

$mahasiswa_list = Nilai::getAllMahasiswa();
$matakuliah_list = [];
$selected_mahasiswa_nama = '';
$prodi_mahasiswa = '';

if ($selected_mahasiswa_id > 0) {
    foreach ($mahasiswa_list as $m) {
        if ($m['id'] == $selected_mahasiswa_id) {
            $selected_mahasiswa_nama = $m['nama'] . ' (' . $m['nim'] . ')';
            $prodi_mahasiswa = $m['prodi'];
            break;
        }
    }
    $matakuliah_list = Matakuliah::getByProdi($prodi_mahasiswa);
} else {
    $matakuliah_list = Matakuliah::getAll();
}

$nilai_mahasiswa = [];
if ($selected_mahasiswa_id > 0) {
    $nilai_mahasiswa = Nilai::getByMahasiswa($selected_mahasiswa_id);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Nilai - EduSmart</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="animated-bg"></div>
<div class="blob" style="top:-20%; left:-10%;"></div>
<div class="blob" style="bottom:-10%; right:-5%; width:60vw; height:60vw;"></div>

<div class="dashboard-container">
    <div class="sidebar">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="mahasiswa.php"><i class="fas fa-user-graduate"></i> Manajemen Mahasiswa</a>
        <a href="matakuliah.php"><i class="fas fa-book"></i> Manajemen MK</a>
        <a href="nilai.php" class="active"><i class="fas fa-pen-fancy"></i> Input Nilai</a>
        <a href="khs.php"><i class="fas fa-file-alt"></i> KHS Mahasiswa</a>
        <a href="ipk.php"><i class="fas fa-chart-line"></i> Hitung IPK</a>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['nama']) ?> (<?= $_SESSION['role'] ?>)</span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><i class="fas fa-search"></i> Pilih Mahasiswa</h3>
            <form method="GET" action="">
                <div class="form-group">
                    <select name="mahasiswa_id" onchange="this.form.submit()">
                        <option value="">-- Pilih Mahasiswa --</option>
                        <?php foreach ($mahasiswa_list as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= ($selected_mahasiswa_id == $m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nim']) ?> - <?= htmlspecialchars($m['nama']) ?> (<?= htmlspecialchars($m['prodi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($selected_mahasiswa_id > 0): ?>
            <div class="card">
                <h3><i class="fas fa-pen"></i> Input Nilai untuk: <?= htmlspecialchars($selected_mahasiswa_nama) ?> (Prodi: <?= htmlspecialchars($prodi_mahasiswa) ?>)</h3>
                <form method="POST">
                    <input type="hidden" name="mahasiswa_id" value="<?= $selected_mahasiswa_id ?>">
                    <div class="form-group">
                        <label>Mata Kuliah</label>
                        <select name="matakuliah_id" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($matakuliah_list as $mk): ?>
                                <option value="<?= $mk['id'] ?>">
                                    <?= htmlspecialchars($mk['kode']) ?> - <?= htmlspecialchars($mk['nama']) ?> (<?= $mk['sks'] ?> SKS) - <?= htmlspecialchars($mk['prodi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nilai Huruf</label>
                        <select name="nilai_huruf" required>
                            <option value="A">A (4.00) - Sangat Baik</option>
                            <option value="B">B (3.00) - Baik</option>
                            <option value="C">C (2.00) - Cukup</option>
                            <option value="D">D (1.00) - Kurang</option>
                            <option value="E">E (0.00) - Gagal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester" required>
                            <option>Ganjil</option>
                            <option>Genap</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun Akademik</label>
                        <input type="text" name="tahun_akademik" placeholder="2024/2025" required>
                    </div>
                    <button type="submit" name="simpan_nilai" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Nilai</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fas fa-table-list"></i> Daftar Nilai <?= htmlspecialchars($selected_mahasiswa_nama) ?></h3>
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Kode MK</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Semester</th><th>Tahun</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($nilai_mahasiswa)): ?>
                            <tr><td colspan="8" style="text-align: center;">Belum ada nilai untuk mahasiswa ini</td></tr>
                        <?php else: ?>
                            <?php foreach ($nilai_mahasiswa as $n): ?>
                            <tr>
                                <td><?= $n['id'] ?></td>
                                <td><?= htmlspecialchars($n['kode']) ?></td>
                                <td><?= htmlspecialchars($n['matakuliah_nama']) ?></td>
                                <td><?= $n['sks'] ?></td>
                                <td><strong><?= $n['nilai_huruf'] ?></strong></td>
                                <td><?= $n['semester'] ?></td>
                                <td><?= $n['tahun_akademik'] ?></td>
                                <td><a href="?delete=<?= $n['id'] ?>&mahasiswa_id=<?= $selected_mahasiswa_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>