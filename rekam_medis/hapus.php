
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

/* =========================
   CEK ID REKAM MEDIS
========================= */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: index.php");
    exit;
}

/* =========================
   AMBIL DATA REKAM MEDIS
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        r.*,
        p.nama_pasien,
        p.no_rm,
        d.nama_dokter
     FROM rekam_medis r
     JOIN pasien p ON r.id_pasien = p.id_pasien
     JOIN dokter d ON r.id_dokter = d.id_dokter
     WHERE r.id_rekam = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$rekam = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$rekam) {
    die("Data rekam medis tidak ditemukan.");
}

$error = "";

/* =========================
   PROSES HAPUS
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM rekam_medis WHERE id_rekam = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {

        if (mysqli_stmt_affected_rows($stmt) > 0) {

            mysqli_stmt_close($stmt);

            header("Location: index.php");
            exit;

        } else {

            $error = "Data rekam medis tidak ditemukan atau sudah dihapus.";

        }

    } else {

        $error = "Data rekam medis gagal dihapus: " .
            mysqli_stmt_error($stmt);

    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hapus Rekam Medis</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        .konfirmasi {
            max-width: 550px;
            margin: 40px auto;
            text-align: center;
        }

        .ikon-hapus {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .detail {
            background: #f1f5fb;
            padding: 18px;
            border-radius: 9px;
            margin: 22px 0;
            text-align: left;
            line-height: 1.8;
            overflow-wrap: anywhere;
        }

        .btn {
            display: inline-block;
            padding: 11px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-danger {
            background: #c0392b;
            color: white;
        }

        .btn-secondary {
            background: #e8edf4;
            color: #263449;
        }

        .error {
            background: #ffecec;
            color: #c0392b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

    </style>

</head>

<body>

<aside class="sidebar">

    <h2>
        <i class="fa-solid fa-hospital"></i> Rekam Medis
    </h2>

    <p class="subjudul">Sistem Informasi Klinik</p>

    <a href="../index.php">
        <i class="fa-solid fa-house"></i> Dashboard
    </a>

    <a href="../pasien/index.php">
        <i class="fa-solid fa-hospital-user"></i> Data Pasien
    </a>

    <a href="../dokter/index.php">
        <i class="fa-solid fa-user-doctor"></i> Data Dokter
    </a>

    <a href="../obat/index.php">
        <i class="fa-solid fa-capsules"></i> Data Obat
    </a>

    <a href="../rekam_medis/index.php" class="active">
        <i class="fa-solid fa-file-medical"></i> Rekam Medis
    </a>

    <a href="../laporan.php">
        <i class="fa-solid fa-chart-column"></i> Laporan
    </a>

    <a href="../pengaturan.php">
        <i class="fa-solid fa-gear"></i> Pengaturan
    </a>

</aside>

<main class="main">

    <header>
        <h2>Hapus Rekam Medis</h2>
        <p>Konfirmasi penghapusan data rekam medis.</p>
    </header>

    <section class="panel konfirmasi">

        <div class="ikon-hapus">🗑️</div>

        <h2>Yakin ingin menghapus rekam medis ini?</h2>

        <p>Data yang dihapus tidak dapat dikembalikan.</p>

        <?php if ($error != ""): ?>

            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>

        <?php endif; ?>

        <div class="detail">

            <strong>No. Rekam Medis:</strong>
            <?= htmlspecialchars($rekam['no_rm'], ENT_QUOTES, 'UTF-8') ?>
            <br>

            <strong>Nama Pasien:</strong>
            <?= htmlspecialchars($rekam['nama_pasien'], ENT_QUOTES, 'UTF-8') ?>
            <br>

            <strong>Nama Dokter:</strong>
            <?= htmlspecialchars($rekam['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>
            <br>

            <strong>Tanggal Pemeriksaan:</strong>
            <?= htmlspecialchars($rekam['tanggal_periksa'], ENT_QUOTES, 'UTF-8') ?>
            <br>

            <strong>Keluhan:</strong>
            <?= nl2br(htmlspecialchars($rekam['keluhan'], ENT_QUOTES, 'UTF-8')) ?>
            <br>

            <strong>Diagnosa:</strong>
            <?= nl2br(htmlspecialchars($rekam['diagnosa'], ENT_QUOTES, 'UTF-8')) ?>
            <br>

            <strong>Tindakan:</strong>
            <?= nl2br(htmlspecialchars($rekam['tindakan'], ENT_QUOTES, 'UTF-8')) ?>
            <br>

            <strong>Resep Obat:</strong>
            <?= nl2br(htmlspecialchars($rekam['resep'], ENT_QUOTES, 'UTF-8')) ?>

        </div>

        <form method="POST">

            <a href="index.php" class="btn btn-secondary">
                Batal
            </a>

            <button type="submit" class="btn btn-danger">
                <i class="fa-solid fa-trash"></i>
                Ya, Hapus Rekam Medis
            </button>

        </form>

    </section>

</main>

</body>
</html>
