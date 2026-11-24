
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

$keyword = trim($_GET['cari'] ?? '');

// QUERY DATA DOKTER
$stmt = mysqli_prepare($conn, "
    SELECT * FROM dokter
    WHERE nama_dokter LIKE ?
    OR spesialisasi LIKE ?
    ORDER BY id_dokter DESC
");

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

$search = "%$keyword%";

mysqli_stmt_bind_param($stmt, "ss", $search, $search);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Dokter</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 8px;
        }

        .search-form input {
            padding: 11px;
            border: 1px solid #d5deea;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
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

        .btn-edit {
            background: #edf4ff;
            color: #2865b5;
        }

        .btn-danger {
            background: #ffecec;
            color: #c0392b;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e8edf4;
        }

        th {
            background: #edf4ff;
            color: #263449;
        }

        .aksi {
            display: flex;
            gap: 7px;
        }

        .pesan {
            padding: 13px;
            background: #e7f7ed;
            color: #247344;
            border-radius: 8px;
            margin-bottom: 18px;
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

    <!-- MAIN -->
    <main class="main">

        <header>
            <h2>Data Dokter</h2>
            <p>Kelola informasi dokter yang bertugas di klinik.</p>
        </header>

        <section class="panel">

            <div class="toolbar">

                <!-- TOMBOL TAMBAH -->
                <a href="tambah.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Dokter
                </a>

                <!-- FORM PENCARIAN -->
                <form method="GET" class="search-form">

                    <input
                        type="text"
                        name="cari"
                        placeholder="Cari nama atau spesialisasi..."
                        value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-search"></i>
                        Cari
                    </button>

                </form>

            </div>

            <!-- TABEL DOKTER -->
            <div class="table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Dokter</th>
                            <th>Spesialisasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($result) > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                <tr>

                                    <td><?= $no++ ?></td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $row['nama_dokter'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $row['spesialisasi'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>

                                        <div class="aksi">

                                            <!-- EDIT -->
                                            <a
                                                class="btn btn-edit"
                                                href="edit.php?id=<?= (int)$row['id_dokter'] ?>"
                                            >
                                                <i class="fa-solid fa-pen"></i>
                                                Edit
                                            </a>

                                            <!-- HAPUS -->
                                            <a
                                                class="btn btn-danger"
                                                href="hapus.php?id=<?= (int)$row['id_dokter'] ?>"
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
                                <td colspan="4" style="text-align:center;">
                                    <?= $keyword !== ''
                                        ? 'Data dokter tidak ditemukan.'
                                        : 'Belum ada data dokter.' ?>
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</body>
</html>
