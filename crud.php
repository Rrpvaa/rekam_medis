<?php
include_once __DIR__ . '/koneksi.php';
include_once __DIR__ . '/notifikasi.php';

$modul = $_GET['modul'] ?? '';
$aksi = $_GET['aksi'] ?? 'index';
$id = (int)($_GET['id'] ?? 0);

$config = [

    'pasien' => [
        'judul' => 'Data Pasien',
        'tabel' => 'pasien',
        'primary' => 'id_pasien',
        'fields' => [
            'no_rm' => 'Nomor Rekam Medis',
            'nama_pasien' => 'Nama Pasien',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'alamat' => 'Alamat'
        ]
    ],

    'dokter' => [
        'judul' => 'Data Dokter',
        'tabel' => 'dokter',
        'primary' => 'id_dokter',
        'fields' => [
            'nama_dokter' => 'Nama Dokter',
            'spesialisasi' => 'Spesialisasi',
            'no_sip' => 'Nomor SIP'
        ]
    ],

    'obat' => [
        'judul' => 'Data Obat',
        'tabel' => 'obat',
        'primary' => 'id_obat',
        'fields' => [
            'nama_obat' => 'Nama Obat',
            'kategori' => 'Kategori',
            'stok' => 'Stok'
        ]
    ],

    'rekam_medis' => [
        'judul' => 'Data Rekam Medis',
        'tabel' => 'rekam_medis',
        'primary' => 'id_rekam',
        'fields' => [
            'id_pasien' => 'Pasien',
            'id_dokter' => 'Dokter',
            'id_obat' => 'Obat',
            'tanggal_periksa' => 'Tanggal Periksa',
            'keluhan' => 'Keluhan',
            'diagnosa' => 'Diagnosa'
        ]
    ]

];

if (!isset($config[$modul])) {
    die('Modul tidak ditemukan!');
}

$c = $config[$modul];
$tabel = $c['tabel'];
$primary = $c['primary'];
$fields = $c['fields'];

$base = 'index.php?modul=' . urlencode($modul);
$data = [];

/* MENGAMBIL DATA UNTUK EDIT */

if (in_array($aksi, ['edit','hapus']) && $id > 0) {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM `$tabel` WHERE `$primary`=?"
    );

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        die('Data tidak ditemukan!');
    }
}

/* PROSES SIMPAN DAN UPDATE */

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    in_array($aksi, ['tambah','edit'])) {

    $values = [];

    foreach ($fields as $field => $label) {
        $values[$field] = trim($_POST[$field] ?? '');
    }

    if (in_array('', $values, true)) {

        set_notifikasi('Semua kolom wajib diisi!', 'error');

    } else {

        if ($aksi === 'edit') {

            $set = [];

            foreach ($fields as $field => $label) {
                $set[] = "`$field`=?";
            }

            $sql = "UPDATE `$tabel` SET "
                . implode(',', $set)
                . " WHERE `$primary`=?";

            $stmt = mysqli_prepare($koneksi, $sql);

            $params = array_values($values);
            $params[] = $id;

            $types = str_repeat('s', count($fields)) . 'i';

            mysqli_stmt_bind_param(
                $stmt,
                $types,
                ...$params
            );

        } else {

            $columns = implode(
                ',',
                array_map(fn($field) => "`$field`", array_keys($fields))
            );

            $marks = implode(
                ',',
                array_fill(0, count($fields), '?')
            );

            $sql = "INSERT INTO `$tabel` ($columns)
                    VALUES ($marks)";

            $stmt = mysqli_prepare($koneksi, $sql);

            $params = array_values($values);

            mysqli_stmt_bind_param(
                $stmt,
                str_repeat('s', count($fields)),
                ...$params
            );
        }

        if (mysqli_stmt_execute($stmt)) {
            set_notifikasi(
                $aksi === 'edit'
                    ? 'Data berhasil diperbarui!'
                    : 'Data berhasil ditambahkan!'
            );
        } else {
            set_notifikasi(
                'Data gagal disimpan: ' . mysqli_stmt_error($stmt),
                'error'
            );
        }
    }

    header("Location: " . $base);
    exit;
}

/* PROSES HAPUS */

if ($aksi === 'hapus' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if (($_POST['konfirmasi'] ?? '') === 'ya') {

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM `$tabel` WHERE `$primary`=?"
        );

        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            set_notifikasi('Data berhasil dihapus!');
        } else {
            set_notifikasi(
                'Data tidak dapat dihapus karena masih digunakan!',
                'error'
            );
        }

    }

    header("Location: " . $base);
    exit;
}

/* AMBIL DATA TABEL */

if ($modul === 'rekam_medis') {

    $result = mysqli_query($koneksi, "
        SELECT r.*,
        p.nama_pasien,
        d.nama_dokter,
        o.nama_obat
        FROM rekam_medis r
        JOIN pasien p ON r.id_pasien=p.id_pasien
        JOIN dokter d ON r.id_dokter=d.id_dokter
        JOIN obat o ON r.id_obat=o.id_obat
        ORDER BY r.id_rekam DESC
    ");

} else {

    $result = mysqli_query(
        $koneksi,
        "SELECT * FROM `$tabel` ORDER BY `$primary` DESC"
    );
}

/* FUNGSI FORM */

function input_form($field, $label, $data, $koneksi) {

    $value = $data[$field] ?? '';

    echo '<div class="form-group">';
    echo '<label>' . htmlspecialchars($label) . ' *</label>';

    if ($field === 'jenis_kelamin') {

        echo '<select name="jenis_kelamin" required>';
        echo '<option value="">-- Pilih Jenis Kelamin --</option>';

        foreach (['Laki-laki','Perempuan'] as $jenis) {
            $selected = $value === $jenis ? 'selected' : '';
            echo "<option value='$jenis' $selected>$jenis</option>";
        }

        echo '</select>';

    } elseif (in_array($field, ['id_pasien','id_dokter','id_obat'])) {

        $options = [
            'id_pasien' => ['pasien','id_pasien','nama_pasien'],
            'id_dokter' => ['dokter','id_dokter','nama_dokter'],
            'id_obat' => ['obat','id_obat','nama_obat']
        ];

        [$table,$id_column,$name_column] = $options[$field];

        $query = mysqli_query(
            $koneksi,
            "SELECT * FROM `$table` ORDER BY `$name_column`"
        );

        echo '<select name="' . $field . '" required>';
        echo '<option value="">-- Pilih ' . $label . ' --</option>';

        while ($row = mysqli_fetch_assoc($query)) {

            $selected = $value == $row[$id_column] ? 'selected' : '';

            echo '<option value="' . $row[$id_column] . '" ' . $selected . '>';
            echo htmlspecialchars($row[$name_column]);
            echo '</option>';
        }

        echo '</select>';

    } elseif (in_array($field, ['alamat','keluhan','diagnosa'])) {

        echo '<textarea name="' . $field . '" required>';
        echo htmlspecialchars($value);
        echo '</textarea>';

    } else {

        $type = 'text';

        if (in_array($field, ['tanggal_lahir','tanggal_periksa'])) {
            $type = 'date';
        }

        if ($field === 'stok') {
            $type = 'number';
        }

        echo '<input type="' . $type . '" name="' . $field . '" value="' .
            htmlspecialchars($value) . '" required>';
    }

    echo '</div>';
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($c['judul']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php tampil_notifikasi(); ?>

<div class="sidebar">

    <div class="logo">
        <div class="logo-icon">✚</div>
        <h2>Rekam Medis</h2>
        <p>RS Sehat Bersama</p>
    </div>

    <p class="nav-title">MENU UTAMA</p>

    <a href="../index.php">▦ Dashboard</a>
    <a href="../pasien/index.php" class="<?= $modul==='pasien'?'active':'' ?>">♙ Data Pasien</a>
    <a href="../dokter/index.php" class="<?= $modul==='dokter'?'active':'' ?>">⚕ Data Dokter</a>
    <a href="../obat/index.php" class="<?= $modul==='obat'?'active':'' ?>">✚ Data Obat</a>
    <a href="../rekam_medis/index.php" class="<?= $modul==='rekam_medis'?'active':'' ?>">▤ Rekam Medis</a>

    <div class="side-footer">© 2026 Rekam Medis</div>

</div>

<div class="main">

    <div class="navbar">
        <span class="menu-icon">☰</span>

        <div class="admin">
            🔔
            <span class="avatar">A</span>
            admin⌄
        </div>
    </div>

    <div class="content">

        <?php if ($aksi === 'tambah' || $aksi === 'edit'): ?>

            <div class="page-heading">
                <div>
                    <h1>
                        <?= $aksi === 'edit' ? 'Edit ' : 'Tambah ' ?>
                        <?= htmlspecialchars($c['judul']) ?>
                    </h1>
                    <p>Masukkan informasi data dengan benar.</p>
                </div>
            </div>

            <div class="panel">

                <form method="POST">

                    <div class="form-grid">

                        <?php foreach ($fields as $field => $label): ?>
                            <?php input_form($field,$label,$data,$koneksi); ?>
                        <?php endforeach; ?>

                    </div>

                    <div class="form-actions">
                        <a href="<?= htmlspecialchars($base) ?>" class="btn-light">
                            Batal
                        </a>

                        <button type="submit" class="btn-primary">
                            <?= $aksi === 'edit' ? 'Update' : 'Simpan' ?>
                        </button>
                    </div>

                </form>

            </div>

        <?php elseif ($aksi === 'hapus'): ?>

            <div class="page-heading">
                <div>
                    <h1>Hapus <?= htmlspecialchars($c['judul']) ?></h1>
                    <p>Konfirmasi penghapusan data.</p>
                </div>
            </div>

            <div class="delete-box">

                <div class="delete-icon">▤</div>

                <h2>Hapus Data Ini?</h2>

                <p>
                    Data yang sudah dihapus tidak dapat dikembalikan.
                    Apakah kamu yakin ingin menghapus data ini?
                </p>

                <div class="panel" style="margin-top:20px;text-align:left">

                    <?php foreach ($fields as $field => $label): ?>
                        <p style="padding:5px 0">
                            <b><?= htmlspecialchars($label) ?>:</b>

                            <?php
                            if ($field === 'id_pasien') {
                                $v = mysqli_fetch_assoc(mysqli_query(
                                    $koneksi,
                                    "SELECT nama_pasien FROM pasien WHERE id_pasien=".(int)$data[$field]
                                ));
                                echo htmlspecialchars($v['nama_pasien'] ?? '');

                            } elseif ($field === 'id_dokter') {
                                $v = mysqli_fetch_assoc(mysqli_query(
                                    $koneksi,
                                    "SELECT nama_dokter FROM dokter WHERE id_dokter=".(int)$data[$field]
                                ));
                                echo htmlspecialchars($v['nama_dokter'] ?? '');

                            } elseif ($field === 'id_obat') {
                                $v = mysqli_fetch_assoc(mysqli_query(
                                    $koneksi,
                                    "SELECT nama_obat FROM obat WHERE id_obat=".(int)$data[$field]
                                ));
                                echo htmlspecialchars($v['nama_obat'] ?? '');

                            } else {
                                echo htmlspecialchars($data[$field]);
                            }
                            ?>
                        </p>
                    <?php endforeach; ?>

                </div>

                <form method="POST" class="form-actions">
                    <a href="<?= htmlspecialchars($base) ?>" class="btn-light">
                        Batal
                    </a>

                    <button name="konfirmasi" value="ya"
                        class="btn-delete"
                        style="border:0;padding:11px 20px;cursor:pointer">
                        Hapus
                    </button>
                </form>

            </div>

        <?php else: ?>

            <div class="page-heading">
                <div>
                    <h1><?= htmlspecialchars($c['judul']) ?></h1>
                    <p>Kelola <?= htmlspecialchars($c['judul']) ?>.</p>
                </div>

                <a href="tambah.php" class="btn-primary">
                    + Tambah <?= htmlspecialchars($modul) ?>
                </a>
            </div>

            <div class="panel">

                <input type="text" class="search"
                    id="search" placeholder="⌕ Cari data...">

                <div class="table-container">

                    <table id="dataTable">

                        <thead>
                            <tr>
                                <th>No</th>

                                <?php foreach ($fields as $field => $label): ?>
                                    <th><?= htmlspecialchars($label) ?></th>
                                <?php endforeach; ?>

                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $no=1; ?>

                        <?php while ($row=mysqli_fetch_assoc($result)): ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <?php foreach ($fields as $field => $label): ?>

                                    <td>
                                        <?php
                                        if ($field === 'id_pasien') {
                                            echo htmlspecialchars($row['nama_pasien']);

                                        } elseif ($field === 'id_dokter') {
                                            echo htmlspecialchars($row['nama_dokter']);

                                        } elseif ($field === 'id_obat') {
                                            echo htmlspecialchars($row['nama_obat']);

                                        } else {
                                            echo htmlspecialchars($row[$field]);
                                        }
                                        ?>
                                    </td>

                                <?php endforeach; ?>

                                <td>
                                    <a class="btn-edit"
                                        href="edit.php?id=<?= $row[$primary] ?>">
                                        Edit
                                    </a>

                                    <a class="btn-delete"
                                        href="hapus.php?id=<?= $row[$primary] ?>">
                                        Hapus
                                    </a>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

                <p style="margin-top:15px;font-size:10px;color:#8196b1">
                    Menampilkan data <?= $no-1 ?> data.
                </p>

            </div>

        <?php endif; ?>

    </div>
</div>

<script>
const search = document.getElementById('search');

if (search) {
    search.addEventListener('input', function() {

        const keyword = this.value.toLowerCase();

        document.querySelectorAll('#dataTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(keyword)
                ? ''
                : 'none';
        });

    });
}
</script>

</body>
</html>