
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

// =========================
// MEMBUAT TABEL PENGATURAN
// =========================

$buatTabel = "CREATE TABLE IF NOT EXISTS pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_klinik VARCHAR(150) NOT NULL,
    alamat_klinik TEXT,
    telepon_klinik VARCHAR(30)
)";

if (!mysqli_query($conn, $buatTabel)) {
    die("Gagal membuat tabel pengaturan: " . mysqli_error($conn));
}


// =========================
// MEMASTIKAN DATA PENGATURAN
// =========================

$cek = mysqli_query($conn, "SELECT id FROM pengaturan LIMIT 1");

if (!$cek) {
    die("Gagal membaca pengaturan: " . mysqli_error($conn));
}

if (mysqli_num_rows($cek) === 0) {

    $insert = mysqli_prepare(
        $conn,
        "INSERT INTO pengaturan
        (nama_klinik, alamat_klinik, telepon_klinik)
        VALUES (?, ?, ?)"
    );

    $namaDefault = "Klinik Sehat";
    $alamatDefault = "";
    $teleponDefault = "";

    mysqli_stmt_bind_param(
        $insert,
        "sss",
        $namaDefault,
        $alamatDefault,
        $teleponDefault
    );

    if (!mysqli_stmt_execute($insert)) {
        die("Gagal membuat data pengaturan: " . mysqli_stmt_error($insert));
    }
}


// =========================
// MENYIMPAN PENGATURAN
// =========================

$pesan = "";
$jenisPesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama_klinik'] ?? '');
    $alamat = trim($_POST['alamat_klinik'] ?? '');
    $telepon = trim($_POST['telepon_klinik'] ?? '');

    if ($nama === '') {

        $pesan = "Nama klinik tidak boleh kosong.";
        $jenisPesan = "error";

    } elseif (strlen($nama) > 150) {

        $pesan = "Nama klinik maksimal 150 karakter.";
        $jenisPesan = "error";

    } elseif (strlen($telepon) > 30) {

        $pesan = "Nomor telepon maksimal 30 karakter.";
        $jenisPesan = "error";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE pengaturan
             SET nama_klinik = ?,
                 alamat_klinik = ?,
                 telepon_klinik = ?
             LIMIT 1"
        );

        if (!$stmt) {
            die("Query gagal: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $nama,
            $alamat,
            $telepon
        );

        if (mysqli_stmt_execute($stmt)) {

            header("Location: pengaturan.php");
            exit;

        } else {

            $pesan = "Pengaturan gagal disimpan.";
            $jenisPesan = "error";

        }
    }
}


// =========================
// MENGAMBIL DATA PENGATURAN
// =========================

$query = mysqli_query(
    $conn,
    "SELECT * FROM pengaturan LIMIT 1"
);

if (!$query) {
    die("Gagal mengambil data pengaturan: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data pengaturan tidak ditemukan.");
}

// Mempertahankan input jika terjadi kesalahan
$namaTampil = $_POST['nama_klinik'] ?? $data['nama_klinik'];
$alamatTampil = $_POST['alamat_klinik'] ?? $data['alamat_klinik'];
$teleponTampil = $_POST['telepon_klinik'] ?? $data['telepon_klinik'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Rekam Medis</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .panel {
            max-width: 750px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5deea;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #2865b5;
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .btn {
            background: #2865b5;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #1d4f91;
        }

        .pesan {
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: #e7f7ed;
            color: #247344;
        }

        .error {
            background: #fdeaea;
            color: #b42318;
        }

        .info-item {
            padding: 14px 0;
            border-bottom: 1px solid #e8edf4;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 600px) {
            .panel {
                padding: 18px;
            }

            .btn {
                width: 100%;
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

    <a href="laporan.php">
        <i class="fa-solid fa-chart-column"></i>
        Laporan
    </a>

    <a href="pengaturan.php" class="active">
        <i class="fa-solid fa-gear"></i>
        Pengaturan
    </a>

</aside>


<!-- MAIN CONTENT -->
<main class="main">

    <header>
        <h2>Pengaturan</h2>
        <p>Kelola informasi profil klinik dan aplikasi.</p>
    </header>


    <!-- PROFIL KLINIK -->
    <section class="panel">

        <h3>
            <i class="fa-solid fa-hospital"></i>
            Profil Klinik
        </h3>

        <p style="color:#8290a3; margin:10px 0 25px;">
            Atur informasi identitas klinik yang menggunakan aplikasi ini.
        </p>


        <?php if ($pesan !== ''): ?>

            <div class="pesan <?= htmlspecialchars($jenisPesan) ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>Nama Klinik</label>

                <input
                    type="text"
                    name="nama_klinik"
                    value="<?= htmlspecialchars($namaTampil, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Masukkan nama klinik"
                    maxlength="150"
                    required
                >

            </div>


            <div class="form-group">

                <label>Alamat Klinik</label>

                <textarea
                    name="alamat_klinik"
                    placeholder="Masukkan alamat klinik"
                ><?= htmlspecialchars($alamatTampil, ENT_QUOTES, 'UTF-8') ?></textarea>

            </div>


            <div class="form-group">

                <label>Nomor Telepon</label>

                <input
                    type="text"
                    name="telepon_klinik"
                    value="<?= htmlspecialchars($teleponTampil, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Masukkan nomor telepon klinik"
                    maxlength="30"
                >

            </div>


            <button type="submit" class="btn">
                <i class="fa-solid fa-floppy-disk"></i>
                Simpan Pengaturan
            </button>

        </form>

    </section>


    <!-- INFORMASI APLIKASI -->
    <section class="panel">

        <h3>
            <i class="fa-solid fa-circle-info"></i>
            Informasi Aplikasi
        </h3>

        <div style="margin-top:15px;">

            <div class="info-item">
                <strong>Nama Aplikasi:</strong>
                Sistem Informasi Rekam Medis
            </div>

            <div class="info-item">
                <strong>Versi Aplikasi:</strong>
                1.0
            </div>

            <div class="info-item">
                <strong>Bahasa Pemrograman:</strong>
                PHP
            </div>

            <div class="info-item">
                <strong>Database:</strong>
                MySQL
            </div>

            <div class="info-item">
                <strong>Status Database:</strong>

                <span style="color:#247344;">
                    <i class="fa-solid fa-circle-check"></i>
                    Terhubung
                </span>
            </div>

        </div>

    </section>

</main>

</body>
</html>
