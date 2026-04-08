<?php
session_start();
require_once 'classes/User.php';
require_once 'classes/Mahasiswa.php';

if (!User::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$mode = $_GET['mode'] ?? 'khs';
$user = User::getCurrentUser();
if (!$user || $user->getRole() != 'mahasiswa') {
    echo "Akses ditolak.";
    exit;
}

$khs_data = $user->getKHS();
$ipk = $user->hitungIPK();
$nama = $user->getNama();
$nim = $user->getNim();
$prodi = $user->getProdi();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak KHS - <?= htmlspecialchars($nama) ?></title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 2rem;
            color: #000;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        h1, h3 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border: 1px solid #000;
            padding: 0.5rem;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
        .footer {
            margin-top: 2rem;
            text-align: right;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>KARTU HASIL STUDI (KHS)</h1>
    <h3>EduSmart - Sistem Akademik</h3>
    <hr>
    <p><strong>Nama:</strong> <?= htmlspecialchars($nama) ?></p>
    <p><strong>NIM:</strong> <?= htmlspecialchars($nim) ?></p>
    <p><strong>Program Studi:</strong> <?= htmlspecialchars($prodi) ?></p>
    <p><strong>IPK Kumulatif:</strong> <?= $ipk ?></p>

    <table>
        <thead>
            <tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Semester</th><th>Tahun Akademik</th></tr>
        </thead>
        <tbody>
            <?php foreach ($khs_data as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['kode']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= $row['sks'] ?></td>
                <td><?= $row['nilai_huruf'] ?></td>
                <td><?= $row['semester'] ?></td>
                <td><?= $row['tahun_akademik'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="footer">
        Dicetak pada: <?= date('d-m-Y H:i:s') ?>
    </div>
    <div class="no-print" style="text-align: center; margin-top: 2rem;">
        <button onclick="window.print()">Cetak / Simpan PDF</button>
        <button onclick="window.close()">Tutup</button>
    </div>
</div>
</body>
</html>