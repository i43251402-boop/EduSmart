<?php
// Hapus semua spasi/karakter sebelum <?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];

// Load semua class dengan toleransi error (gunakan @ untuk suppress jika file hilang)
$classDir = __DIR__ . '/classes/';
$files = ['User', 'Nilai', 'Matakuliah', 'Mahasiswa', 'Kalender', 'Dosen'];
foreach ($files as $file) {
    $path = $classDir . $file . '.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        // Buat class dummy agar tidak fatal error
        if (!class_exists($file)) {
            eval("class $file { public static function __callStatic(\$n, \$a) { return []; } public function __call(\$n, \$a) { return null; } }");
        }
    }
}

// Ambil data statistik dengan aman
$totalMahasiswa = (class_exists('Nilai') && method_exists('Nilai', 'getAllMahasiswa')) ? count(Nilai::getAllMahasiswa()) : 0;
$totalMatakuliah = (class_exists('Matakuliah') && method_exists('Matakuliah', 'getAll')) ? count(Matakuliah::getAll()) : 0;
$totalNilai = (class_exists('Nilai') && method_exists('Nilai', 'getAll')) ? count(Nilai::getAll()) : 0;
$kalender = (class_exists('Kalender') && method_exists('Kalender', 'getAll')) ? Kalender::getAll() : [];

// Proses kalender (hanya dosen)
$message = '';
if ($role === 'dosen') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['tambah_kalender']) && class_exists('Kalender') && method_exists('Kalender', 'add')) {
            $judul = $_POST['judul'] ?? '';
            $tanggal = $_POST['tanggal'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $tipe = $_POST['tipe'] ?? 'acara';
            $message = Kalender::add($judul, $tanggal, $deskripsi, $tipe) ? "Event berhasil ditambahkan!" : "Gagal!";
        } elseif (isset($_POST['edit_kalender']) && class_exists('Kalender') && method_exists('Kalender', 'update')) {
            $id = (int)$_POST['id'];
            $judul = $_POST['judul'] ?? '';
            $tanggal = $_POST['tanggal'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $tipe = $_POST['tipe'] ?? 'acara';
            $message = Kalender::update($id, $judul, $tanggal, $deskripsi, $tipe) ? "Event diupdate!" : "Gagal update!";
        }
    }
    if (isset($_GET['delete_kalender']) && class_exists('Kalender') && method_exists('Kalender', 'delete')) {
        $id = (int)$_GET['delete_kalender'];
        $message = Kalender::delete($id) ? "Event dihapus!" : "Gagal hapus!";
    }
}

// Data mahasiswa
$ipk = 0;
$khs = [];
if ($role === 'mahasiswa' && class_exists('User') && method_exists('User', 'getCurrentUser')) {
    $user = User::getCurrentUser();
    if ($user && method_exists($user, 'hitungIPK')) $ipk = $user->hitungIPK();
    if ($user && method_exists($user, 'getKHS')) $khs = $user->getKHS();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EduSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0a0c15;
            color: #f1f5f9;
            overflow-x: hidden;
            min-height: 100vh;
        }
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, #1e2a4a, #0a0c15 80%);
            z-index: -2;
        }
        .animated-bg::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: conic-gradient(from 0deg, #f97316, #facc15, #f97316, #facc15);
            animation: rotateBg 20s linear infinite;
            opacity: 0.15;
            z-index: -1;
        }
        @keyframes rotateBg { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .blob {
            position: fixed;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(249,115,22,0.25), rgba(250,204,21,0.08));
            border-radius: 62% 38% 72% 28% / 45% 32% 68% 55%;
            filter: blur(80px);
            z-index: -1;
            animation: blobMove 20s infinite alternate;
        }
        @keyframes blobMove { 0% { transform: translate(10%, 10%) scale(1); } 100% { transform: translate(-10%, -10%) scale(1.2); } }
        .dashboard-container { display: flex; min-height: 100vh; position: relative; z-index: 2; }
        .sidebar {
            width: 280px;
            background: rgba(15,25,45,0.6);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(255,215,0,0.25);
            padding: 1.5rem 0;
            flex-shrink: 0;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.5rem;
            color: #e2e8f0;
            text-decoration: none;
            transition: 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar a i { width: 24px; }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(249,115,22,0.2);
            border-left-color: #f97316;
            color: #facc15;
            transform: translateX(5px);
        }
        .main-content { flex: 1; padding: 1.5rem; overflow-y: auto; }
        .top-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }
        .logout-btn {
            background: linear-gradient(95deg,#f97316,#ea580c);
            padding:0.4rem 1rem;
            border-radius:40px;
            text-decoration:none;
            color:white;
            font-weight:600;
            transition:0.2s;
        }
        .logout-btn:hover { background:#facc15; color:#0f172a; transform:scale(1.02); }
        .welcome-card {
            background: rgba(30,35,55,0.6);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 1.2rem 1.5rem;
            border-left: 5px solid #f97316;
            margin-bottom: 1.5rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px,1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-box {
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(8px);
            border-radius: 24px;
            padding: 1rem;
            text-align: center;
            transition: 0.2s;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .stat-box:hover { transform: translateY(-5px); border-color: #facc15; background: rgba(249,115,22,0.15); }
        .stat-box i { font-size: 1.8rem; color: #facc15; margin-bottom: 0.3rem; }
        .stat-box h3 { font-size: 1.8rem; margin: 0.2rem 0; }
        .kalender-card {
            background: rgba(15,25,45,0.5);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 1.2rem;
            border: 1px solid rgba(255,215,0,0.2);
        }
        .event-item {
            border-left: 3px solid #f97316;
            padding: 0.7rem;
            margin-bottom: 0.7rem;
            background: rgba(0,0,0,0.3);
            border-radius: 1rem;
        }
        .event-date { font-size: 0.75rem; color: #facc15; font-weight: bold; }
        .event-title { font-weight: bold; }
        .event-type {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.65rem;
            margin-top: 0.2rem;
        }
        .type-acara { background: #3b82f6; }
        .type-ujian { background: #ef4444; }
        .type-libur { background: #10b981; }
        .type-pendaftaran { background: #f59e0b; }
        .btn-primary, .btn-warning, .btn-danger {
            padding: 0.3rem 0.9rem;
            border-radius: 40px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .btn-primary { background: linear-gradient(95deg,#f97316,#ea580c); color:white; }
        .btn-primary:hover { background:#facc15; color:#0f172a; }
        .btn-warning { background: rgba(250,204,21,0.8); color:#0f172a; }
        .btn-danger { background: rgba(220,38,38,0.8); color:white; }
        .alert { background:rgba(16,185,129,0.2); padding:0.7rem; border-radius:1rem; margin-bottom:1rem; border-left:4px solid #10b981; }
        @media (max-width:768px) {
            .dashboard-container { flex-direction: column; }
            .sidebar { width:100%; flex-direction:row; flex-wrap:wrap; justify-content:center; padding:1rem; }
            .sidebar a { border-left:none; border-radius:40px; margin:0.2rem; }
            .main-content { padding:1rem; }
        }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(15px); } to { opacity:1; transform:translateY(0); } }
        .welcome-card, .stats-grid, .kalender-card { animation: fadeInUp 0.5s ease-out; }
    </style>
</head>
<body>
<div class="animated-bg"></div>
<div class="blob" style="top:-20%; left:-10%;"></div>
<div class="blob" style="bottom:-10%; right:-5%; width:60vw; height:60vw;"></div>

<div class="dashboard-container">
    <div class="sidebar">
        <?php if ($role == 'dosen'): ?>
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="mahasiswa.php"><i class="fas fa-user-graduate"></i> Mahasiswa</a>
            <a href="matakuliah.php"><i class="fas fa-book"></i> Mata Kuliah</a>
            <a href="nilai.php"><i class="fas fa-pen-fancy"></i> Input Nilai</a>
            <a href="khs.php"><i class="fas fa-file-alt"></i> KHS Mahasiswa</a>
            <a href="ipk.php"><i class="fas fa-chart-line"></i> Hitung IPK</a>
        <?php else: ?>
            <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="khs.php"><i class="fas fa-file-alt"></i> KHS Saya</a>
            <a href="ipk.php"><i class="fas fa-chart-line"></i> IPK Saya</a>
            <a href="laporan_cetak.php"><i class="fas fa-print"></i> Cetak KHS</a>
        <?php endif; ?>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nama) ?> (<?= $role ?>)</span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="welcome-card">
            <h2>Selamat Datang, <?= htmlspecialchars($nama) ?>! 👋</h2>
            <p>Anda login sebagai <strong><?= $role ?></strong>. Gunakan menu di samping.</p>
        </div>

        <?php if ($role == 'dosen'): ?>
            <div class="stats-grid">
                <div class="stat-box"><i class="fas fa-users"></i><h3><?= $totalMahasiswa ?></h3><p>Total Mahasiswa</p></div>
                <div class="stat-box"><i class="fas fa-book"></i><h3><?= $totalMatakuliah ?></h3><p>Total Mata Kuliah</p></div>
                <div class="stat-box"><i class="fas fa-chart-simple"></i><h3><?= $totalNilai ?></h3><p>Total Nilai</p></div>
            </div>
        <?php else: ?>
            <div class="stats-grid">
                <div class="stat-box"><i class="fas fa-check-double"></i><h3><?= count($khs) ?></h3><p>Mata Kuliah Diambil</p></div>
                <div class="stat-box"><i class="fas fa-star"></i><h3><?= $ipk ?></h3><p>IPK Saat Ini</p></div>
            </div>
        <?php endif; ?>

        <div class="kalender-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3><i class="fas fa-calendar-alt"></i> Kalender Akademik</h3>
                <?php if ($role == 'dosen'): ?>
                    <button onclick="toggleForm()" class="btn-primary"><i class="fas fa-plus"></i> Tambah Event</button>
                <?php endif; ?>
            </div>
            <?php if ($role == 'dosen'): ?>
                <div id="formKalender" style="display:none; background:rgba(0,0,0,0.5); padding:1rem; border-radius:1rem; margin-bottom:1rem;">
                    <form method="POST">
                        <input type="hidden" name="id" id="eventId">
                        <input type="text" name="judul" id="judul" placeholder="Judul event" required style="width:100%; margin-bottom:0.5rem; padding:0.6rem; border-radius:40px; background:rgba(255,255,255,0.1); border:none; color:white;">
                        <input type="date" name="tanggal" id="tanggal" required style="width:100%; margin-bottom:0.5rem; padding:0.6rem; border-radius:40px; background:rgba(255,255,255,0.1); border:none; color:white;">
                        <textarea name="deskripsi" id="deskripsi" placeholder="Deskripsi" rows="2" style="width:100%; margin-bottom:0.5rem; padding:0.6rem; border-radius:1rem; background:rgba(255,255,255,0.1); border:none; color:white;"></textarea>
                        <select name="tipe" id="tipe" style="width:100%; margin-bottom:0.5rem; padding:0.6rem; border-radius:40px; background:rgba(255,255,255,0.1); border:none; color:white;">
                            <option value="acara">Acara</option>
                            <option value="ujian">Ujian</option>
                            <option value="libur">Libur</option>
                            <option value="pendaftaran">Pendaftaran</option>
                        </select>
                        <button type="submit" name="tambah_kalender" id="submitBtn" class="btn-primary">Simpan</button>
                        <button type="button" onclick="toggleForm()" class="btn-warning">Batal</button>
                    </form>
                </div>
            <?php endif; ?>
            <div style="max-height: 350px; overflow-y: auto;">
                <?php if (empty($kalender)): ?>
                    <p>Belum ada event kalender.</p>
                <?php else: ?>
                    <?php foreach ($kalender as $event): 
                        $dateObj = new DateTime($event['tanggal']);
                        $formattedDate = $dateObj->format('l, d F Y');
                    ?>
                        <div class="event-item">
                            <div class="event-date">📅 <?= $formattedDate ?></div>
                            <div class="event-title"><?= htmlspecialchars($event['judul']) ?></div>
                            <div class="event-desc"><?= nl2br(htmlspecialchars($event['deskripsi'])) ?></div>
                            <span class="event-type type-<?= $event['tipe'] ?>"><?= ucfirst($event['tipe']) ?></span>
                            <?php if ($role == 'dosen'): ?>
                                <div style="margin-top:0.5rem;">
                                    <button onclick='editEvent(<?= $event['id'] ?>, "<?= addslashes($event['judul']) ?>", "<?= $event['tanggal'] ?>", "<?= addslashes($event['deskripsi']) ?>", "<?= $event['tipe'] ?>")' class="btn-warning">Edit</button>
                                    <a href="?delete_kalender=<?= $event['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn-danger">Hapus</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleForm() {
        var form = document.getElementById('formKalender');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            document.getElementById('eventId').value = '';
            document.getElementById('submitBtn').name = 'tambah_kalender';
        } else {
            form.style.display = 'none';
        }
    }
    function editEvent(id, judul, tanggal, deskripsi, tipe) {
        document.getElementById('formKalender').style.display = 'block';
        document.getElementById('eventId').value = id;
        document.getElementById('judul').value = judul;
        document.getElementById('tanggal').value = tanggal;
        document.getElementById('deskripsi').value = deskripsi;
        document.getElementById('tipe').value = tipe;
        document.getElementById('submitBtn').name = 'edit_kalender';
    }
</script>
</body>
</html>