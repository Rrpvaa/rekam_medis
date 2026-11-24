
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

$error = "";

/* =========================
   CEK ID REKAM MEDIS
========================= */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: index.php");
    exit;
}

/* =========================
   AMBIL DATA REKAM MEDIS
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM rekam_medis WHERE id_rekam = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    die("Data rekam medis tidak ditemukan.");
}

/* =========================
   AMBIL DATA DROPDOWN
========================= */

$pasien = mysqli_query(
    $conn,
    "SELECT * FROM pasien ORDER BY nama_pasien"
);

$dokter = mysqli_query(
    $conn,
    "SELECT * FROM dokter ORDER BY nama_dokter"
);

$obat = mysqli_query(
    $conn,
    "SELECT * FROM obat ORDER BY nama_obat"
);

if (!$pasien || !$dokter || !$obat) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

/* =========================
   PROSES UPDATE
========================= */

if (isset($_POST['update'])) {

    $id_pasien = filter_var(
        $_POST['id_pasien'] ?? '',
        FILTER_VALIDATE_INT
    );

    $id_dokter = filter_var(
        $_POST['id_dokter'] ?? '',
        FILTER_VALIDATE_INT
    );

    $id_obat_input = $_POST['id_obat'] ?? '';
    $id_obat = $id_obat_input !== ''
        ? filter_var($id_obat_input, FILTER_VALIDATE_INT)
        : null;

    $tanggal = $_POST['tanggal_periksa'] ?? '';
    $keluhan = trim($_POST['keluhan'] ?? '');
    $diagnosa = trim($_POST['diagnosa'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $resep = trim($_POST['resep'] ?? '');

    // Validasi input
    if (
        !$id_pasien ||
        !$id_dokter ||
        ($id_obat_input !== '' && !$id_obat) ||
        $tanggal === '' ||
        $keluhan === '' ||
        $diagnosa === '' ||
        $tindakan === '' ||
        $resep === ''
    ) {

        $error = "Semua data wajib diisi dengan benar, kecuali obat.";

    } else {

        // Validasi tanggal
        $tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);

        if (
            !$tanggalObj ||
            $tanggalObj->format('Y-m-d') !== $tanggal
        ) {

            $error = "Tanggal pemeriksaan tidak valid.";

        } elseif ($tanggal > date('Y-m-d')) {

            $error = "Tanggal pemeriksaan tidak boleh melebihi hari ini.";

        } else {

            // Cek pasien
            $cekPasien = mysqli_prepare(
                $conn,
                "SELECT id_pasien FROM pasien WHERE id_pasien = ?"
            );

            mysqli_stmt_bind_param($cekPasien, "i", $id_pasien);
            mysqli_stmt_execute($cekPasien);

            $hasilPasien = mysqli_stmt_get_result($cekPasien);
            $pasienValid = mysqli_num_rows($hasilPasien) > 0;

            mysqli_stmt_close($cekPasien);

            // Cek dokter
            $cekDokter = mysqli_prepare(
                $conn,
                "SELECT id_dokter FROM dokter WHERE id_dokter = ?"
            );

            mysqli_stmt_bind_param($cekDokter, "i", $id_dokter);
            mysqli_stmt_execute($cekDokter);

            $hasilDokter = mysqli_stmt_get_result($cekDokter);
            $dokterValid = mysqli_num_rows($hasilDokter) > 0;

            mysqli_stmt_close($cekDokter);

            // Cek obat jika dipilih
            $obatValid = true;

            if ($id_obat !== null) {

                $cekObat = mysqli_prepare(
                    $conn,
                    "SELECT id_obat FROM obat WHERE id_obat = ?"
                );

                mysqli_stmt_bind_param($cekObat, "i", $id_obat);
                mysqli_stmt_execute($cekObat);

                $hasilObat = mysqli_stmt_get_result($cekObat);
                $obatValid = mysqli_num_rows($hasilObat) > 0;

                mysqli_stmt_close($cekObat);
            }

            if (!$pasienValid || !$dokterValid || !$obatValid) {

                $error = "Data pasien, dokter, atau obat tidak ditemukan.";

            } else {

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE rekam_medis SET
                        id_pasien = ?,
                        id_dokter = ?,
                        id_obat = ?,
                        tanggal_periksa = ?,
                        keluhan = ?,
                        diagnosa = ?,
                        tindakan = ?,
                        resep = ?
                    WHERE id_rekam = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iiisssssi",
                    $id_pasien,
                    $id_dokter,
                    $id_obat,
                    $tanggal,
                    $keluhan,
                    $diagnosa,
                    $tindakan,
                    $resep,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: index.php");
                    exit;

                } else {

                    $error = "Gagal memperbarui data: " .
                        mysqli_stmt_error($stmt);

                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

/* =========================
   FUNGSI NILAI FORM
========================= */

function nilai($nama, $data)
{
    return htmlspecialchars(
        $_POST[$nama] ?? $data[$nama] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

// Nilai dropdown
$pasien_terpilih = $_POST['id_pasien'] ?? $data['id_pasien'];
$dokter_terpilih = $_POST['id_dokter'] ?? $data['id_dokter'];
$obat_terpilih = $_POST['id_obat'] ?? $data['id_obat'];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Rekam Medis</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 7px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button,
        .tombol {
            display: inline-block;
            padding: 11px 18px;
            background: #2865b5;
            color: white;
            border: 0;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
        }

        .error {
            color: #dc3545;
            background: #fee2e2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
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
        <h2>Edit Rekam Medis</h2>
        <p>Perbarui informasi pemeriksaan pasien.</p>
    </header>

    <section class="panel">

        <?php if ($error): ?>

            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Pasien</label>

                <select name="id_pasien" required>

                    <option value="">Pilih Pasien</option>

                    <?php while ($p = mysqli_fetch_assoc($pasien)): ?>

                        <option
                            value="<?= (int)$p['id_pasien'] ?>"
                            <?= $pasien_terpilih == $p['id_pasien'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars(
                                $p['no_rm'] . " - " . $p['nama_pasien'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>

            <div class="form-group">

                <label>Dokter</label>

                <select name="id_dokter" required>

                    <option value="">Pilih Dokter</option>

                    <?php while ($d = mysqli_fetch_assoc($dokter)): ?>

                        <option
                            value="<?= (int)$d['id_dokter'] ?>"
                            <?= $dokter_terpilih == $d['id_dokter'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars(
                                $d['nama_dokter'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>

            <div class="form-group">

                <label>Obat (opsional)</label>

                <select name="id_obat">

                    <option value="">-- Tidak memilih obat --</option>

                    <?php while ($o = mysqli_fetch_assoc($obat)): ?>

                        <option
                            value="<?= (int)$o['id_obat'] ?>"
                            <?= $obat_terpilih == $o['id_obat'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars(
                                $o['nama_obat'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>

            <div class="form-group">

                <label>Tanggal Pemeriksaan</label>

                <input
                    type="date"
                    name="tanggal_periksa"
                    value="<?= nilai('tanggal_periksa', $data) ?>"
                    max="<?= date('Y-m-d') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Keluhan</label>

                <textarea name="keluhan" required><?= nilai('keluhan', $data) ?></textarea>

            </div>

            <div class="form-group">

                <label>Diagnosa</label>

                <textarea name="diagnosa" required><?= nilai('diagnosa', $data) ?></textarea>

            </div>

            <div class="form-group">

                <label>Tindakan</label>

                <textarea name="tindakan" required><?= nilai('tindakan', $data) ?></textarea>

            </div>

            <div class="form-group">

                <label>Resep Obat</label>

                <textarea name="resep" required><?= nilai('resep', $data) ?></textarea>

            </div>

            <button type="submit" name="update">
                <i class="fa-solid fa-save"></i> Update
            </button>

            <a href="index.php" class="tombol" style="background:#777;">
                Kembali
            </a>

        </form>

    </section>

</main>

</body>
</html>
