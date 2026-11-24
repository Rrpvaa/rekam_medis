
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

// Fungsi untuk menghitung jumlah data
function jumlahData($conn, $tabel) {
    $tabelDiizinkan = ['pasien', 'dokter', 'obat', 'rekam_medis'];

    if (!in_array($tabel, $tabelDiizinkan, true)) {
        return 0;
    }

    $query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$tabel`");

    if (!$query) {
        return 0;
    }

    $data = mysqli_fetch_assoc($query);

    return (int) $data['total'];
}

// Mengambil jumlah data dari database
$pasien = jumlahData($conn, 'pasien');
$dokter = jumlahData($conn, 'dokter');
$obat = jumlahData($conn, 'obat');
$rekam = jumlahData($conn, 'rekam_medis');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Rekam Medis</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>


<!-- SIDEBAR -->
<aside class="sidebar">

    <h2>
        <i class="fa-solid fa-hospital"></i>
        Rekam Medis
    </h2>

    <p class="subjudul">Sistem Informasi Klinik</p>

    <a href="index.php" class="active">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>

    <a href="pasien/index.php">
        <i class="fa-solid fa-hospital-user"></i>
        Data Pasien
    </a>

    <a href="dokter/index.php">
        <i class="fa-solid fa-user-doctor"></i>
        Data Dokter
    </a>

    <a href="obat/index.php">
        <i class="fa-solid fa-capsules"></i>
        Data Obat
    </a>

    <a href="rekam_medis/index.php">
        <i class="fa-solid fa-file-medical"></i>
        Rekam Medis
    </a>

    <a href="laporan.php">
        <i class="fa-solid fa-chart-column"></i>
        Laporan
    </a>

    <a href="pengaturan.php">
        <i class="fa-solid fa-gear"></i>
        Pengaturan
    </a>

</aside>


<!-- MAIN CONTENT -->
<div class="main">

    <!-- HEADER -->
    <header>
        <div>
            <h2>Dashboard</h2>
            <p>Selamat datang di Sistem Informasi Rekam Medis</p>
        </div>
    </header>


    <!-- WELCOME -->
    <section class="welcome">
        <h2>Selamat Datang!</h2>

        <p>
            Kelola data pasien, dokter, obat, dan rekam medis
            dengan mudah.
        </p>
    </section>


    <!-- RINGKASAN DATA -->
    <h3>Ringkasan Data</h3>

    <div class="cards">

        <div class="card">
            <p>Total Pasien</p>
            <h1><?= $pasien ?></h1>
        </div>

        <div class="card">
            <p>Total Dokter</p>
            <h1><?= $dokter ?></h1>
        </div>

        <div class="card">
            <p>Total Obat</p>
            <h1><?= $obat ?></h1>
        </div>

        <div class="card">
            <p>Total Rekam Medis</p>
            <h1><?= $rekam ?></h1>
        </div>

    </div>


    <!-- MENU UTAMA -->
    <div class="panel">

        <h3>Menu Utama</h3>

        <div class="menu-grid">

            <a href="pasien/index.php">
                <i class="fa-solid fa-hospital-user"></i>
                Kelola Pasien
            </a>

            <a href="dokter/index.php">
                <i class="fa-solid fa-user-doctor"></i>
                Kelola Dokter
            </a>

            <a href="obat/index.php">
                <i class="fa-solid fa-capsules"></i>
                Kelola Obat
            </a>

            <a href="rekam_medis/index.php">
                <i class="fa-solid fa-file-medical"></i>
                Kelola Rekam Medis
            </a>

        </div>

    </div>

</div>

</body>
</html>
