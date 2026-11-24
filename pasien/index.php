
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

/* =========================
   AMBIL DATA PASIEN
========================= */

if ($cari !== '') {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM pasien
         WHERE nama_pasien LIKE ?
         OR no_rm LIKE ?
         ORDER BY id_pasien DESC"
    );

    if (!$stmt) {
        die("Query gagal: " . mysqli_error($conn));
    }

    $keyword = "%" . $cari . "%";

    mysqli_stmt_bind_param($stmt, "ss", $keyword, $keyword);
    mysqli_stmt_execute($stmt);

    $data = mysqli_stmt_get_result($stmt);

} else {

    $data = mysqli_query(
        $conn,
        "SELECT * FROM pasien ORDER BY id_pasien DESC"
    );

}

if (!$data) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Pasien</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 11px 17px;
            border: 0;
            border-radius: 8px;
            background: #2865b5;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-danger {
            background: #dc3545;
        }

        .search {
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .table-wrap {
            background: white;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #edf4ff;
        }

        .aksi {
            display: flex;
            gap: 7px;
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
        <h2>Data Pasien</h2>
        <p>Kelola seluruh data pasien klinik.</p>
    </header>

    <section class="panel">

        <div class="toolbar">

            <a href="tambah.php" class="btn">
                <i class="fa-solid fa-plus"></i>
                Tambah Pasien
            </a>

            <form method="GET">

                <input
                    class="search"
                    type="text"
                    name="cari"
                    placeholder="Cari nama atau nomor RM..."
                    value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>"
                >

                <button class="btn" type="submit">
                    <i class="fa-solid fa-search"></i>
                    Cari
                </button>

                <?php if ($cari !== ''): ?>
                    <a href="index.php" class="btn" style="background:#777;">
                        Reset
                    </a>
                <?php endif; ?>

            </form>

        </div>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>
                        <th>No.</th>
                        <th>No. Rekam Medis</th>
                        <th>Nama Pasien</th>
                        <th>Tanggal Lahir</th>
                        <th>Jenis Kelamin</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if (mysqli_num_rows($data) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)): ?>

                            <tr>

                                <td>
                                    <?= $no++ ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['no_rm'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_pasien'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['tanggal_lahir'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?php
                                    if ($row['jenis_kelamin'] === 'L') {
                                        echo "Laki-laki";
                                    } elseif ($row['jenis_kelamin'] === 'P') {
                                        echo "Perempuan";
                                    } else {
                                        echo htmlspecialchars($row['jenis_kelamin'], ENT_QUOTES, 'UTF-8');
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['alamat'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>

                                    <div class="aksi">

                                        <a
                                            class="btn"
                                            href="edit.php?id=<?= (int)$row['id_pasien'] ?>"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                            Edit
                                        </a>

                                        <a
                                            href="hapus.php?id=<?= (int)$row['id_pasien'] ?>"
                                            class="btn btn-danger"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="7" style="text-align:center;">

                                <?php if ($cari !== ''): ?>
                                    Data pasien tidak ditemukan.
                                <?php else: ?>
                                    Belum ada data pasien.
                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>

</body>
</html>
