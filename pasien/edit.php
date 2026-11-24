
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data pasien
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM pasien WHERE id_pasien = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pasien = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$pasien) {
    die("Data pasien tidak ditemukan.");
}

$error = '';

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

        // Validasi format tanggal
        $tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);

        if (
            !$tanggalObj ||
            $tanggalObj->format('Y-m-d') !== $tanggal
        ) {

            $error = "Format tanggal lahir tidak valid.";

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE pasien
                 SET no_rm = ?,
                     nama_pasien = ?,
                     tanggal_lahir = ?,
                     jenis_kelamin = ?,
                     alamat = ?
                 WHERE id_pasien = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssssi",
                $no_rm,
                $nama,
                $tanggal,
                $jk,
                $alamat,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: index.php");
                exit;

            } else {

                $error = "Gagal memperbarui data. Periksa nomor RM.";

            }

            mysqli_stmt_close($stmt);
        }
    }
}

// Data yang ditampilkan pada form
$no_rm_form = $_POST['no_rm'] ?? $pasien['no_rm'];
$nama_form = $_POST['nama_pasien'] ?? $pasien['nama_pasien'];
$tanggal_form = $_POST['tanggal_lahir'] ?? $pasien['tanggal_lahir'];
$jk_form = $_POST['jenis_kelamin'] ?? $pasien['jenis_kelamin'];
$alamat_form = $_POST['alamat'] ?? $pasien['alamat'];
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Pasien</title>

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
            font-size: 14px;
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
        <h2>Edit Pasien</h2>
        <p>Perbarui informasi pasien.</p>
    </header>

    <div class="form-box">

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Nomor Rekam Medis</label>

                <input
                    type="text"
                    name="no_rm"
                    value="<?= htmlspecialchars($no_rm_form, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Nama Pasien</label>

                <input
                    type="text"
                    name="nama_pasien"
                    value="<?= htmlspecialchars($nama_form, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Tanggal Lahir</label>

                <input
                    type="date"
                    name="tanggal_lahir"
                    value="<?= htmlspecialchars($tanggal_form, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Jenis Kelamin</label>

                <select name="jenis_kelamin" required>

                    <option value="">Pilih Jenis Kelamin</option>

                    <option value="L" <?= $jk_form === 'L' ? 'selected' : '' ?>>
                        Laki-laki
                    </option>

                    <option value="P" <?= $jk_form === 'P' ? 'selected' : '' ?>>
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
                ><?= htmlspecialchars($alamat_form, ENT_QUOTES, 'UTF-8') ?></textarea>

            </div>

            <button class="btn" type="submit">
                Simpan Perubahan
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
