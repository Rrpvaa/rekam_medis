
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once "../koneksi.php";

$error = "";
$pasien = null;

// CEK ID PASIEN
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: index.php");
    exit;
}

// AMBIL DATA PASIEN
try {

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
        header("Location: index.php");
        exit;
    }

} catch (Throwable $e) {
    die("ERROR SAAT MENGAMBIL DATA PASIEN: " . htmlspecialchars($e->getMessage()));
}

// PROSES HAPUS
if (isset($_POST['hapus'])) {

    try {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM pasien WHERE id_pasien = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php");
        exit;

    } catch (Throwable $e) {

        $error = "Data pasien gagal dihapus: " . $e->getMessage();

    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Pasien</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header p {
            color: #8290a3;
            margin-top: 7px;
        }

        .delete-card {
            background: white;
            max-width: 650px;
            margin: 30px auto;
            padding: 35px;
            border-radius: 13px;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
            text-align: center;
        }

        .delete-icon {
            width: 75px;
            height: 75px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #fee2e2;
            color: #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
        }

        .delete-card h3 {
            margin-bottom: 12px;
            font-size: 23px;
        }

        .delete-card > p {
            color: #8290a3;
            line-height: 1.6;
        }

        .delete-info {
            background: #f1f5fb;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
            text-align: left;
        }

        .delete-info div {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #dce3ed;
        }

        .delete-info div:last-child {
            border-bottom: none;
        }

        .delete-info strong {
            color: #526176;
        }

        .delete-info span {
            text-align: right;
            overflow-wrap: anywhere;
        }

        .delete-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 25px;
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

        .btn-secondary {
            background: #e5eaf1;
            color: #263449;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .alert {
            background: #fee2e2;
            color: #b91c1c;
            padding: 15px;
            border-radius: 9px;
            margin-bottom: 20px;
            overflow-wrap: anywhere;
        }

        @media(max-width: 600px) {
            .delete-card {
                padding: 20px;
            }

            .delete-actions {
                flex-direction: column;
            }

            .delete-info div {
                flex-direction: column;
                gap: 5px;
            }

            .delete-info span {
                text-align: left;
            }
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
            <h2>Hapus Pasien</h2>
            <p>Sistem Informasi Rekam Medis</p>
        </header>

        <div class="page-header">
            <div>
                <h2>HAPUS PASIEN</h2>
                <p>Konfirmasi penghapusan data pasien.</p>
            </div>

            <a href="index.php" class="btn btn-secondary">
                ← Kembali
            </a>
        </div>

        <?php if ($error != ""): ?>
            <div class="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="delete-card">

            <div class="delete-icon">
                <i class="fa-solid fa-trash"></i>
            </div>

            <h3>Hapus Pasien Ini?</h3>

            <p>
                Data yang sudah dihapus tidak dapat dikembalikan.
                Apakah kamu yakin ingin menghapus pasien ini?
            </p>

            <div class="delete-info">

                <div>
                    <strong>Nomor Rekam Medis</strong>
                    <span><?= htmlspecialchars($pasien['no_rm'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div>
                    <strong>Nama Pasien</strong>
                    <span><?= htmlspecialchars($pasien['nama_pasien'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div>
                    <strong>Tanggal Lahir</strong>
                    <span><?= htmlspecialchars($pasien['tanggal_lahir'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div>
                    <strong>Jenis Kelamin</strong>
                    <span><?= htmlspecialchars($pasien['jenis_kelamin'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div>
                    <strong>Alamat</strong>
                    <span><?= htmlspecialchars($pasien['alamat'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

            </div>

            <form method="POST">

                <div class="delete-actions">

                    <a href="index.php" class="btn btn-secondary">
                        Batal
                    </a>

                    <button type="submit" name="hapus" class="btn btn-danger">
                        <i class="fa-solid fa-trash"></i>
                        Hapus Pasien
                    </button>

                </div>

            </form>

        </div>

    </div>

</body>
</html>
