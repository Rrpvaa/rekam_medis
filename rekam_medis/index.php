
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

/* =========================
   PENCARIAN DATA
========================= */

$cari = trim($_GET['cari'] ?? '');

$sql = "SELECT
            r.*,
            p.nama_pasien,
            p.no_rm,
            d.nama_dokter,
            o.nama_obat
        FROM rekam_medis r
        JOIN pasien p ON r.id_pasien = p.id_pasien
        JOIN dokter d ON r.id_dokter = d.id_dokter
        LEFT JOIN obat o ON r.id_obat = o.id_obat
        WHERE p.nama_pasien LIKE ?
           OR p.no_rm LIKE ?
           OR d.nama_dokter LIKE ?
           OR r.diagnosa LIKE ?
        ORDER BY r.id_rekam DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

$keyword = "%" . $cari . "%";

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $keyword,
    $keyword,
    $keyword,
    $keyword
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Rekam Medis</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
            min-width: 1100px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #edf4ff;
            color: #263449;
            white-space: nowrap;
        }

        .aksi a {
            margin-right: 8px;
            color: #2865b5;
            text-decoration: none;
        }

        .aksi a:hover {
            text-decoration: underline;
        }

        .aksi a:last-child {
            color: #c0392b;
        }

        .tombol {
            display: inline-block;
            padding: 10px 15px;
            background: #2865b5;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 7px;
            cursor: pointer;
        }

        .cari {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 7px;
            width: 280px;
            max-width: 100%;
        }

        .data-kosong {
            text-align: center;
            padding: 25px;
            color: #777;
        }

        .form-cari {
            margin-top: 20px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
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
        <h2>Data Rekam Medis</h2>
        <p>Daftar pemeriksaan dan riwayat pasien</p>
    </header>

    <section class="panel">

        <a href="tambah.php" class="tombol">
            <i class="fa-solid fa-plus"></i> Tambah Rekam Medis
        </a>

        <!-- FORM PENCARIAN -->

        <form method="GET" class="form-cari">

            <input
                class="cari"
                type="text"
                name="cari"
                placeholder="Cari pasien, dokter, atau diagnosa..."
                value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>"
            >

            <button class="tombol" type="submit">
                <i class="fa-solid fa-search"></i> Cari
            </button>

            <?php if ($cari !== ''): ?>

                <a href="index.php" class="tombol" style="background:#777;">
                    Reset
                </a>

            <?php endif; ?>

        </form>

        <!-- TABEL DATA -->

        <div style="overflow-x:auto;">

            <table>

                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>No. RM</th>
                    <th>Pasien</th>
                    <th>Dokter</th>
                    <th>Keluhan</th>
                    <th>Diagnosa</th>
                    <th>Tindakan</th>
                    <th>Resep Obat</th>
                    <th>Aksi</th>
                </tr>

                <?php if (mysqli_num_rows($result) > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td><?= $no++ ?></td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['tanggal_periksa'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['no_rm'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['nama_pasien'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['nama_dokter'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= nl2br(htmlspecialchars(
                                    $row['keluhan'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )) ?>
                            </td>

                            <td>
                                <?= nl2br(htmlspecialchars(
                                    $row['diagnosa'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )) ?>
                            </td>

                            <td>
                                <?= nl2br(htmlspecialchars(
                                    $row['tindakan'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )) ?>
                            </td>

                            <td>
                                <?= nl2br(htmlspecialchars(
                                    $row['resep'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )) ?>
                            </td>

                            <td class="aksi">

                                <a href="edit.php?id=<?= (int)$row['id_rekam'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>

                                <a href="hapus.php?id=<?= (int)$row['id_rekam'] ?>">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="10" class="data-kosong">

                            <?php if ($cari !== ''): ?>

                                <i class="fa-solid fa-search"></i>
                                Data rekam medis dengan kata kunci
                                "<strong><?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?></strong>"
                                tidak ditemukan.

                            <?php else: ?>

                                <i class="fa-solid fa-folder-open"></i>
                                Belum ada data rekam medis.

                            <?php endif; ?>

                        </td>
                    </tr>

                <?php endif; ?>

            </table>

        </div>

    </section>

</main>

</body>
</html>
