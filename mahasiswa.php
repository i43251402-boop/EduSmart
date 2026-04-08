<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';

if (!User::isLoggedIn() || $_SESSION['role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

$db = Database::getInstance()->getConnection();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'tambah') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $nama = $_POST['nama'];
        $nim = $_POST['nim'];
        $prodi = $_POST['prodi'];
        $stmt = $db->prepare("INSERT INTO users (username, password, role, nama, nim, prodi) VALUES (?, ?, 'mahasiswa', ?, ?, ?)");
        if ($stmt->execute([$username, $password, $nama, $nim, $prodi])) {
            $message = "✅ Mahasiswa berhasil ditambahkan!";
            $message_type = 'success';
        } else {
            $message = "❌ Gagal menambahkan mahasiswa!";
            $message_type = 'error';
        }
    } elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $nama = $_POST['nama'];
        $nim = $_POST['nim'];
        $prodi = $_POST['prodi'];
        $stmt = $db->prepare("UPDATE users SET username = ?, nama = ?, nim = ?, prodi = ? WHERE id = ? AND role = 'mahasiswa'");
        if ($stmt->execute([$username, $nama, $nim, $prodi, $id])) {
            $message = "✅ Mahasiswa berhasil diupdate!";
            $message_type = 'success';
        } else {
            $message = "❌ Gagal mengupdate mahasiswa!";
            $message_type = 'error';
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'mahasiswa'");
    if ($stmt->execute([$id])) {
        $message = "✅ Mahasiswa berhasil dihapus!";
        $message_type = 'success';
    } else {
        $message = "❌ Gagal menghapus mahasiswa!";
        $message_type = 'error';
    }
}

$stmt = $db->query("SELECT * FROM users WHERE role = 'mahasiswa' ORDER BY nim");
$mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'mahasiswa'");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Mahasiswa - EduSmart</title>
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
        <a href="mahasiswa.php" class="active"><i class="fas fa-user-graduate"></i> Manajemen Mahasiswa</a>
        <a href="matakuliah.php"><i class="fas fa-book"></i> Manajemen MK</a>
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
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><i class="fas fa-<?= $editData ? 'edit' : 'plus' ?>"></i> <?= $editData ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?= $editData ? 'edit' : 'tambah' ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required value="<?= $editData['username'] ?? '' ?>">
                </div>
                <?php if (!$editData): ?>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" required value="<?= $editData['nama'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" name="nim" required value="<?= $editData['nim'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Program Studi</label>
                    <select name="prodi" required>
                        <option value="Informatika" <?= (isset($editData['prodi']) && $editData['prodi'] == 'Informatika') ? 'selected' : '' ?>>Informatika</option>
                        <option value="Sistem Informasi" <?= (isset($editData['prodi']) && $editData['prodi'] == 'Sistem Informasi') ? 'selected' : '' ?>>Sistem Informasi</option>
                        <option value="Teknik Komputer" <?= (isset($editData['prodi']) && $editData['prodi'] == 'Teknik Komputer') ? 'selected' : '' ?>>Teknik Komputer</option>
                        <option value="Manajemen Informatika" <?= (isset($editData['prodi']) && $editData['prodi'] == 'Manajemen Informatika') ? 'selected' : '' ?>>Manajemen Informatika</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><?= $editData ? 'Update' : 'Simpan' ?></button>
                <?php if ($editData): ?>
                    <a href="mahasiswa.php" class="btn btn-warning">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3><i class="fas fa-list"></i> Daftar Mahasiswa</h3>
            <table class="data-table">
                <thead>
                    <tr><th>NIM</th><th>Nama</th><th>Program Studi</th><th>Username</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($mahasiswa as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['nim']) ?></td>
                        <td><?= htmlspecialchars($m['nama']) ?></td>
                        <td><?= htmlspecialchars($m['prodi']) ?></td>
                        <td><?= htmlspecialchars($m['username']) ?></td>
                        <td>
                            <a href="?edit=<?= $m['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a>
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