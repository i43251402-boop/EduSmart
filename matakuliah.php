<?php
session_start();
require_once 'classes/User.php';
require_once 'classes/Matakuliah.php';

if (!User::isLoggedIn() || $_SESSION['role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'tambah') {
        if (Matakuliah::create($_POST['kode'], $_POST['nama'], $_POST['sks'], $_POST['prodi'])) {
            $message = "✅ Mata kuliah berhasil ditambahkan!";
        } else {
            $message = "❌ Gagal menambahkan!";
        }
    } elseif ($_POST['action'] == 'edit') {
        if (Matakuliah::update($_POST['id'], $_POST['kode'], $_POST['nama'], $_POST['sks'], $_POST['prodi'])) {
            $message = "✅ Mata kuliah berhasil diupdate!";
        } else {
            $message = "❌ Gagal mengupdate!";
        }
    }
}

if (isset($_GET['delete'])) {
    if (Matakuliah::delete($_GET['delete'])) {
        $message = "✅ Mata kuliah berhasil dihapus!";
    }
}

$matakuliah = Matakuliah::getAll();
$editData = null;
if (isset($_GET['edit'])) {
    $editData = Matakuliah::getById($_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Mata Kuliah - EduSmart</title>
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
        <a href="matakuliah.php" class="active"><i class="fas fa-book"></i> Manajemen MK</a>
        <a href="nilai.php"><i class="fas fa-pen-fancy"></i> Input Nilai</a>
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
            <h3><i class="fas fa-<?= $editData ? 'edit' : 'plus' ?>"></i> <?= $editData ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?= $editData ? 'edit' : 'tambah' ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Kode MK</label>
                    <input type="text" name="kode" required value="<?= $editData['kode'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Nama Mata Kuliah</label>
                    <input type="text" name="nama" required value="<?= $editData['nama'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>SKS</label>
                    <input type="number" name="sks" required value="<?= $editData['sks'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Program Studi</label>
                    <select name="prodi" required>
                        <option value="Umum" <?= (isset($editData['prodi']) && $editData['prodi'] == 'Umum') ? 'selected' : '' ?>>Umum</option>
                        <option value="Informatika" <?= (isset($editData['prodi']) && $editData['prodi'] == 'Informatika') ? 'selected' : '' ?>>Informatika</option>
                        <option value="Sistem Informasi" <?= (isset($editData['prodi']) && $editData['prodi'] == 'Sistem Informasi') ? 'selected' : '' ?>>Sistem Informasi</option>
                        <option value="Teknik Komputer" <?= (isset($editData['prodi']) && $editData['prodi'] == 'Teknik Komputer') ? 'selected' : '' ?>>Teknik Komputer</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><?= $editData ? 'Update' : 'Simpan' ?></button>
                <?php if ($editData): ?>
                    <a href="matakuliah.php" class="btn btn-warning">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3><i class="fas fa-list"></i> Daftar Mata Kuliah</h3>
            <table class="data-table">
                <thead>
                    <tr><th>Kode</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Program Studi</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($matakuliah as $mk): ?>
                    <tr>
                        <td><?= htmlspecialchars($mk['kode']) ?></td>
                        <td><?= htmlspecialchars($mk['nama']) ?></td>
                        <td><?= $mk['sks'] ?></td>
                        <td><?= htmlspecialchars($mk['prodi']) ?></td>
                        <td>
                            <a href="?edit=<?= $mk['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete=<?= $mk['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>