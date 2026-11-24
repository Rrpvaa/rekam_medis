
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

// Mengambil filter tanggal
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

$error = '';
$result = false;

// Validasi format tanggal
function tanggalValid($tanggal) {
    if ($tanggal === '') {
        return true;
    }

    $format = DateTime::createFromFormat('Y-m-d', $tanggal);

    return $format && $format->format('Y-m-d') === $tanggal;
}

if (!tanggalValid($tanggal_awal) || !tanggalValid($tanggal_akhir)) {
    $error = "Format tanggal tidak valid.";
} elseif (
    $tanggal_awal !== '' &&
    $tanggal_akhir !== '' &&
    $tanggal_awal > $tanggal_akhir
) {
    $error = "Tanggal awal tidak boleh melebihi tanggal akhir.";
} else {

    // Query laporan rekam medis
    $sql = "SELECT
                r.id_rekam,
                r.tanggal_periksa,
                r.keluhan,
                r.diagnosa,
                p.no_rm,
                p.nama_pasien,
                d.nama_dokter
            FROM rekam_medis r
            JOIN pasien p ON r.id_pasien = p.id_pasien
            JOIN dokter d ON r.id_dokter = d.id_dokter";

    if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
        $sql .= " WHERE r.tanggal_periksa BETWEEN ? AND ?";
    } elseif ($tanggal_awal !== '') {
        $sql .= " WHERE r.tanggal_periksa >= ?";
    } elseif ($tanggal_akhir !== '') {
        $sql .= " WHERE r.tanggal_periksa <= ?";
    }

    $sql .= " ORDER BY r.tanggal_periksa DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Query gagal: " . mysqli_error($conn));
    }

    // Menjalankan query sesuai filter
    if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
        mysqli_stmt_bind_param($stmt, "ss", $tanggal_awal, $tanggal_akhir);
    } elseif ($tanggal_awal !== '') {
        mysqli_stmt_bind_param($stmt, "s", $tanggal_awal);
    } elseif ($tanggal_akhir !== '') {
        mysqli_stmt_bind_param($stmt, "s", $tanggal_akhir);
    }

    if (!mysqli_stmt_execute($stmt)) {
        die("Query gagal: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekam Medis</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* TOOLBAR */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        /* FILTER FORM */
        .filter-form {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-form input {
            padding: 10px;
            border: 1px solid #d5deea;
            border-radius: 8px;
        }

        /* TOMBOL */
        .btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            background: #2865b5;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-reset {
            background: #8290a3;
        }

        /* TABEL */
        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            min-width: 850px;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #e8edf4;
        }

        th {
            background: #edf4ff;
            color: #263449;
        }

        tr:hover {
            background: #f8faff;
        }

        /* PESAN ERROR */
        .pesan-error {
            background: #ffeaea;
            color: #c0392b;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        @media print {
            .sidebar,
            .toolbar,
            .popup-notifikasi {
                display: none !important;
            }

            .main {
                margin: 0;
                padding: 0;
            }

            body {
                background: white;
            }

            .panel {
                box-shadow: none;
                padding: 0;
            }

            table {
                min-width: 100%;
                font-size: 11px;
            }

            th, td {
                padding: 8px;
            }
        }

        @media (max-width: 600px) {
            .filter-form {
                width: 100%;
            }

            .filter-form input {
                width: 100%;
            }

            .toolbar > .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <h2>
        <i class="fa-solid fa-hospital"></i>
        Rekam Medis
    </h2>

    <p class="subjudul">Sistem Informasi Klinik</p>

    <a href="index.php">
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

    <a href="laporan.php" class="active">
        <i class="fa-solid fa-chart-column"></i>
        Laporan
    </a>

    <a href="pengaturan.php">
        <i class="fa-solid fa-gear"></i>
        Pengaturan
    </a>

</aside>


<!-- MAIN CONTENT -->
<main class="main">

    <header>
        <h2>Laporan Rekam Medis</h2>
        <p>Laporan riwayat pemeriksaan pasien.</p>
    </header>


    <section class="panel">

        <h3>
            <i class="fa-solid fa-file-lines"></i>
            Data Laporan Pemeriksaan
        </h3>

        <!-- TOOLBAR -->
        <div class="toolbar">

            <form method="GET" class="filter-form">

                <input
                    type="date"
                    name="tanggal_awal"
                    value="<?= htmlspecialchars($tanggal_awal) ?>"
                    aria-label="Tanggal awal"
                >

                <input
                    type="date"
                    name="tanggal_akhir"
                    value="<?= htmlspecialchars($tanggal_akhir) ?>"
                    aria-label="Tanggal akhir"
                >

                <button type="submit" class="btn">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>

                <a href="laporan.php" class="btn btn-reset">
                    <i class="fa-solid fa-rotate-left"></i>
                    Reset
                </a>

            </form>

            <button onclick="window.print()" class="btn">
                <i class="fa-solid fa-print"></i>
                Cetak Laporan
            </button>

        </div>


        <!-- TABEL LAPORAN -->
        <?php if ($error !== ''): ?>

            <div class="pesan-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($error) ?>
            </div>

        <?php else: ?>

            <div class="table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>No. RM</th>
                            <th>Nama Pasien</th>
                            <th>Nama Dokter</th>
                            <th>Keluhan</th>
                            <th>Diagnosa</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($result) > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal_periksa']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['no_rm']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_pasien']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_dokter']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['keluhan']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['diagnosa']) ?>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7" style="text-align:center;">
                                    Belum ada data laporan.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
