
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_OFF);

require_once "../koneksi.php";

$error = "";

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
    die("Gagal mengambil data dropdown: " . mysqli_error($conn));
}

/* =========================
   PROSES SIMPAN
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_pasien = filter_var(
        $_POST['id_pasien'] ?? '',
        FILTER_VALIDATE_INT
    );

    $id_dokter = filter_var(
        $_POST['id_dokter'] ?? '',
        FILTER_VALIDATE_INT
    );

    $id_obat_input = $_POST['id_obat'] ?? '';

    $id_obat = $id_obat_input === ''
        ? null
        : filter_var($id_obat_input, FILTER_VALIDATE_INT);

    $tanggal = $_POST['tanggal_periksa'] ?? '';
    $keluhan = trim($_POST['keluhan'] ?? '');
    $diagnosa = trim($_POST['diagnosa'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $resep = trim($_POST['resep'] ?? '');

    /* =========================
       VALIDASI INPUT
    ========================= */

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

        $error = "Semua data wajib diisi dengan benar, kecuali pilihan obat.";

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

            /* =========================
               CEK DATA PASIEN
            ========================= */

            $cekPasien = mysqli_prepare(
                $conn,
                "SELECT id_pasien FROM pasien WHERE id_pasien = ?"
            );

            mysqli_stmt_bind_param(
                $cekPasien,
                "i",
                $id_pasien
            );

            mysqli_stmt_execute($cekPasien);

            $hasilPasien = mysqli_stmt_get_result($cekPasien);

            $pasienValid = mysqli_num_rows($hasilPasien) > 0;

            mysqli_stmt_close($cekPasien);

            /* =========================
               CEK DATA DOKTER
            ========================= */

            $cekDokter = mysqli_prepare(
                $conn,
                "SELECT id_dokter FROM dokter WHERE id_dokter = ?"
            );

            mysqli_stmt_bind_param(
                $cekDokter,
                "i",
                $id_dokter
            );

            mysqli_stmt_execute($cekDokter);

            $hasilDokter = mysqli_stmt_get_result($cekDokter);

            $dokterValid = mysqli_num_rows($hasilDokter) > 0;

            mysqli_stmt_close($cekDokter);

            /* =========================
               CEK DATA OBAT
            ========================= */

            $obatValid = true;

            if ($id_obat !== null) {

                $cekObat = mysqli_prepare(
                    $conn,
                    "SELECT id_obat FROM obat WHERE id_obat = ?"
                );

                mysqli_stmt_bind_param(
                    $cekObat,
                    "i",
                    $id_obat
                );

                mysqli_stmt_execute($cekObat);

                $hasilObat = mysqli_stmt_get_result($cekObat);

                $obatValid = mysqli_num_rows($hasilObat) > 0;

                mysqli_stmt_close($cekObat);
            }

            if (!$pasienValid || !$dokterValid || !$obatValid) {

                $error = "Data pasien, dokter, atau obat tidak ditemukan.";

            } else {

                /* =========================
                   INSERT REKAM MEDIS
                ========================= */

                $sql = "INSERT INTO rekam_medis
                        (
                            id_pasien,
                            id_dokter,
                            id_obat,
                            tanggal_periksa,
                            keluhan,
                            diagnosa,
                            tindakan,
                            resep
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = mysqli_prepare($conn, $sql);

                if (!$stmt) {

                    $error = "Query INSERT gagal: " . mysqli_error($conn);

                } else {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "iiisssss",
                        $id_pasien,
                        $id_dokter,
                        $id_obat,
                        $tanggal,
                        $keluhan,
                        $diagnosa,
                        $tindakan,
                        $resep
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);

                        header("Location: index.php");
                        exit;

                    } else {

                        $error = "Gagal menyimpan data: " .
                            mysqli_stmt_error($stmt);

                    }

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Rekam Medis</title>

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
            border: none;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
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
        <h2>Tambah Rekam Medis</h2>
        <p>Masukkan informasi pemeriksaan pasien</p>
    </header>

    <section class="panel">

        <?php if ($error != ""): ?>

            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Pasien</label>

                <select name="id_pasien" required>

                    <option value="">-- Pilih Pasien --</option>

                    <?php while ($p = mysqli_fetch_assoc($pasien)): ?>

                        <option
                            value="<?= (int)$p['id_pasien'] ?>"
                            <?= ($_POST['id_pasien'] ?? '') == $p['id_pasien'] ? 'selected' : '' ?>
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

                    <option value="">-- Pilih Dokter --</option>

                    <?php while ($d = mysqli_fetch_assoc($dokter)): ?>

                        <option
                            value="<?= (int)$d['id_dokter'] ?>"
                            <?= ($_POST['id_dokter'] ?? '') == $d['id_dokter'] ? 'selected' : '' ?>
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
                            <?= ($_POST['id_obat'] ?? '') == $o['id_obat'] ? 'selected' : '' ?>
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
                    value="<?= htmlspecialchars($_POST['tanggal_periksa'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    max="<?= date('Y-m-d') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Keluhan</label>

                <textarea name="keluhan" required><?= htmlspecialchars($_POST['keluhan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

            </div>

            <div class="form-group">

                <label>Diagnosa</label>

                <textarea name="diagnosa" required><?= htmlspecialchars($_POST['diagnosa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

            </div>

            <div class="form-group">

                <label>Tindakan</label>

                <textarea name="tindakan" required><?= htmlspecialchars($_POST['tindakan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

            </div>

            <div class="form-group">

                <label>Resep Obat</label>

                <textarea
                    name="resep"
                    placeholder="Ketik resep obat secara manual..."
                    required
                ><?= htmlspecialchars($_POST['resep'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

            </div>

            <button type="submit" name="simpan">
                <i class="fa-solid fa-save"></i> Simpan
            </button>

            <a href="index.php" class="tombol" style="background:#777;">
                Kembali
            </a>

        </form>

    </section>

</main>

</body>
</html>
