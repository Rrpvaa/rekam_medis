
<?php
require_once "../koneksi.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// AMBIL DATA DOKTER
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM dokter WHERE id_dokter = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$dokter = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$dokter) {
    die("Data dokter tidak ditemukan.");
}

$error = "";

// PROSES HAPUS DOKTER
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM dokter WHERE id_dokter = ?"
    );

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                mysqli_stmt_close($stmt);
                header("Location: index.php");
                exit;
            } else {
                $error = "Data dokter tidak berhasil ditemukan untuk dihapus.";
            }

        } else {

            $error = "Data dokter gagal dihapus. Periksa apakah dokter masih digunakan pada data rekam medis.";

        }

        mysqli_stmt_close($stmt);

    } else {

        $error = "Terjadi kesalahan saat menyiapkan penghapusan data.";

    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hapus Dokter</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .delete-card {
            background: white;
            max-width: 550px;
            margin: 35px auto;
            padding: 35px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
        }

        .trash-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .delete-card h2 {
            margin-bottom: 12px;
        }

        .delete-card p {
            color: #7d8999;
            line-height: 1.6;
        }

        .doctor-info {
            background: #f1f5fb;
            padding: 18px;
            border-radius: 9px;
            margin: 22px 0;
            text-align: left;
        }

        .doctor-info p {
            margin: 7px 0;
            color: #263449;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-cancel {
            background: #e8edf4;
            color: #263449;
        }

        .btn-delete {
            background: #d94343;
            color: white;
        }

        .btn-delete:hover {
            background: #bd3030;
        }

        .error {
            background: #ffecec;
            color: #b83232;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>

<body>

<aside class="sidebar">

    <h2>
        <i class="fa-solid fa-hospital"></i>
        Rekam Medis
    </h2>

    <p class="subjudul">Sistem Informasi Klinik</p>

    <a href="../index.php">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>

    <a href="../pasien/index.php">
        <i class="fa-solid fa-hospital-user"></i>
        Data Pasien
    </a>

    <a href="index.php" class="active">
        <i class="fa-solid fa-user-doctor"></i>
        Data Dokter
    </a>

    <a href="../obat/index.php">
        <i class="fa-solid fa-capsules"></i>
        Data Obat
    </a>

    <a href="../rekam_medis/index.php">
        <i class="fa-solid fa-file-medical"></i>
        Rekam Medis
    </a>

    <a href="../laporan.php">
        <i class="fa-solid fa-chart-column"></i>
        Laporan
    </a>

    <a href="../pengaturan.php">
        <i class="fa-solid fa-gear"></i>
        Pengaturan
    </a>

</aside>

<main class="main">

    <header>
        <h2>Hapus Dokter</h2>
        <p>Konfirmasi penghapusan data dokter.</p>
    </header>

    <section class="delete-card">

        <div class="trash-icon">🗑️</div>

        <h2>Hapus Data Dokter?</h2>

        <p>
            Apakah kamu yakin ingin menghapus data dokter berikut?
        </p>

        <div class="doctor-info">

            <p>
                <strong>Nama Dokter:</strong>
                <?= htmlspecialchars($dokter['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                <strong>Spesialisasi:</strong>
                <?= htmlspecialchars($dokter['spesialisasi'], ENT_QUOTES, 'UTF-8') ?>
            </p>

        </div>

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="actions">

                <a href="index.php" class="btn btn-cancel">
                    <i class="fa-solid fa-arrow-left"></i>
                    Batal
                </a>

                <button
                    type="submit"
                    class="btn btn-delete"
                >
                    <i class="fa-solid fa-trash"></i>
                    Hapus Dokter
                </button>

            </div>

        </form>

    </section>

</main>

</body>
</html>
