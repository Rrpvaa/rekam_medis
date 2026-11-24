
<?php
include "../koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST['nama_dokter'] ?? '');
    $spesialisasi = trim($_POST['spesialisasi'] ?? '');

    if ($nama === '' || $spesialisasi === '') {

        $error = "Nama dokter dan spesialisasi wajib diisi.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO dokter (nama_dokter, spesialisasi)
             VALUES (?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $nama,
            $spesialisasi
        );

        if (mysqli_stmt_execute($stmt)) {

            header("Location: index.php");
            exit;

        } else {

            $error = "Gagal menyimpan data dokter.";

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
    <title>Tambah Dokter</title>

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
            color: #b83232;
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

    <a href="index.php" class="active">
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

<main class="main">

    <header>
        <h2>Tambah Dokter</h2>
        <p>Masukkan informasi dokter baru.</p>
    </header>

    <section class="panel">

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Nama Dokter</label>

                <input
                    type="text"
                    name="nama_dokter"
                    value="<?= htmlspecialchars($_POST['nama_dokter'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Spesialisasi</label>

                <input
                    type="text"
                    name="spesialisasi"
                    value="<?= htmlspecialchars($_POST['spesialisasi'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Dokter
            </button>

            <a href="index.php" class="btn btn-secondary">
                Batal
            </a>

        </form>

    </section>

</main>

</body>
</html>
