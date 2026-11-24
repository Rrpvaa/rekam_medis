
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../koneksi.php";

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data obat
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM obat WHERE id_obat = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$obat = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$obat) {
    die("Data obat tidak ditemukan.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM obat WHERE id_obat = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {

        header("Location: index.php");
        exit;

    } else {

        $error = "Data obat gagal dihapus: " . mysqli_error($conn);

    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hapus Obat</title>

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
        <h2>Hapus Obat</h2>
        <p>Konfirmasi penghapusan data obat.</p>
    </header>

    <section class="panel konfirmasi">

        <div class="ikon-hapus">🗑️</div>

        <h2>Yakin ingin menghapus obat ini?</h2>

        <p>Data yang dihapus tidak dapat dikembalikan.</p>

        <?php if ($error != ""): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="detail">

            <strong>Nama Obat:</strong>
            <?= htmlspecialchars($obat['nama_obat'], ENT_QUOTES, 'UTF-8') ?>
            <br>

            <strong>Satuan:</strong>
            <?= htmlspecialchars($obat['satuan'], ENT_QUOTES, 'UTF-8') ?>
            <br>

            <strong>Stok:</strong>
            <?= htmlspecialchars($obat['stok'], ENT_QUOTES, 'UTF-8') ?>

        </div>

        <form method="POST">

            <a href="index.php" class="btn btn-secondary">
                Batal
            </a>

            <button type="submit" class="btn btn-danger">
                Ya, Hapus Obat
            </button>

        </form>

    </section>

</main>

</body>
</html>
