
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_obat = trim($_POST['nama_obat'] ?? '');
    $satuan = trim($_POST['satuan'] ?? '');
    $stok_input = $_POST['stok'] ?? '';

    if (
        $nama_obat === "" ||
        $satuan === "" ||
        $stok_input === "" ||
        !is_numeric($stok_input) ||
        (int)$stok_input < 0 ||
        (string)(int)$stok_input !== (string)$stok_input
    ) {

        $error = "Semua data harus diisi dengan benar.";

    } else {

        $stok = (int) $stok_input;

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO obat (nama_obat, satuan, stok)
             VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $nama_obat,
            $satuan,
            $stok
        );

        if (mysqli_stmt_execute($stmt)) {

            header("Location: index.php");
            exit;

        } else {

            $error = "Gagal menambahkan obat: " . mysqli_error($conn);

        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Obat</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5deea;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #2865b5;
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

    <a href="index.php" class="active">
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

<main class="main">

    <header>
        <h2>Tambah Obat</h2>
        <p>Tambahkan informasi obat baru ke dalam sistem.</p>
    </header>

    <section class="panel">

        <?php if ($error != ""): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Nama Obat</label>

                <input
                    type="text"
                    name="nama_obat"
                    placeholder="Masukkan nama obat"
                    value="<?= htmlspecialchars($_POST['nama_obat'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Satuan</label>

                <input
                    type="text"
                    name="satuan"
                    placeholder="Contoh: Tablet, Kapsul, Botol"
                    value="<?= htmlspecialchars($_POST['satuan'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Stok</label>

                <input
                    type="number"
                    name="stok"
                    min="0"
                    step="1"
                    placeholder="Masukkan jumlah stok"
                    value="<?= htmlspecialchars($_POST['stok'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Obat
            </button>

            <a href="index.php" class="btn btn-secondary">
                Batal
            </a>

        </form>

    </section>

</main>

</body>
</html>
