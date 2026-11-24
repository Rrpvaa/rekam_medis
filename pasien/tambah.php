
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

$error = "";

/* =========================
   PROSES TAMBAH PASIEN
========================= */

$no_rm = "";
$nama = "";
$tanggal = "";
$jk = "";
$alamat = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $no_rm = trim($_POST['no_rm'] ?? '');
    $nama = trim($_POST['nama_pasien'] ?? '');
    $tanggal = $_POST['tanggal_lahir'] ?? '';
    $jk = $_POST['jenis_kelamin'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');

    // Validasi input
    if (
        $no_rm === '' ||
        $nama === '' ||
        $tanggal === '' ||
        !in_array($jk, ['L', 'P'], true) ||
        $alamat === ''
    ) {

        $error = "Semua data harus diisi dengan benar.";

    } else {

        // Validasi tanggal lahir
        $tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);

        if (
            !$tanggalObj ||
            $tanggalObj->format('Y-m-d') !== $tanggal
        ) {

            $error = "Format tanggal lahir tidak valid.";

        } elseif ($tanggal > date('Y-m-d')) {

            $error = "Tanggal lahir tidak boleh melebihi tanggal hari ini.";

        } else {

            // Simpan data pasien
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO pasien
                (no_rm, nama_pasien, tanggal_lahir, jenis_kelamin, alamat)
                VALUES (?, ?, ?, ?, ?)"
            );

            if (!$stmt) {

                $error = "Query gagal: " . mysqli_error($conn);

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssss",
                    $no_rm,
                    $nama,
                    $tanggal,
                    $jk,
                    $alamat
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: index.php");
                    exit;

                } else {

                    if (mysqli_stmt_errno($stmt) == 1062) {
                        $error = "Nomor RM sudah digunakan. Gunakan nomor RM lain.";
                    } else {
                        $error = "Gagal menyimpan data pasien.";
                    }

                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Pasien</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        .form-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 750px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border: 0;
            border-radius: 8px;
            background: #2865b5;
            color: white;
            cursor: pointer;
            text-decoration: none;
        }

        .error {
            color: #dc3545;
            background: #fee2e2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
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

    <a href="index.php" class="active">
        <i class="fa-solid fa-hospital-user"></i> Data Pasien
    </a>

    <a href="../dokter/index.php">
        <i class="fa-solid fa-user-doctor"></i> Data Dokter
    </a>

    <a href="../obat/index.php">
        <i class="fa-solid fa-capsules"></i> Data Obat
    </a>

    <a href="../rekam_medis/index.php">
        <i class="fa-solid fa-file-medical"></i> Rekam Medis
    </a>

    <a href="../laporan.php">
        <i class="fa-solid fa-chart-column"></i> Laporan
    </a>

    <a href="../pengaturan.php">
        <i class="fa-solid fa-gear"></i> Pengaturan
    </a>

</aside>

<div class="main">

    <header>
        <h2>Tambah Pasien</h2>
        <p>Masukkan informasi pasien baru.</p>
    </header>

    <div class="form-box">

        <?php if ($error): ?>

            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Nomor Rekam Medis</label>

                <input
                    type="text"
                    name="no_rm"
                    value="<?= htmlspecialchars($no_rm, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Nama Pasien</label>

                <input
                    type="text"
                    name="nama_pasien"
                    value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Tanggal Lahir</label>

                <input
                    type="date"
                    name="tanggal_lahir"
                    value="<?= htmlspecialchars($tanggal, ENT_QUOTES, 'UTF-8') ?>"
                    max="<?= date('Y-m-d') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Jenis Kelamin</label>

                <select name="jenis_kelamin" required>

                    <option value="">Pilih Jenis Kelamin</option>

                    <option value="L" <?= $jk === 'L' ? 'selected' : '' ?>>
                        Laki-laki
                    </option>

                    <option value="P" <?= $jk === 'P' ? 'selected' : '' ?>>
                        Perempuan
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>Alamat</label>

                <textarea
                    name="alamat"
                    rows="4"
                    required
                ><?= htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8') ?></textarea>

            </div>

            <button class="btn" type="submit">
                Simpan Pasien
            </button>

            <a
                class="btn"
                href="index.php"
                style="background:#777"
            >
                Kembali
            </a>

        </form>

    </div>

</div>

</body>
</html>
