CREATE DATABASE db_rekam_medis

CREATE TABLE pasien (
    id_pasien INT AUTO_INCREMENT PRIMARY KEY,
    no_rm VARCHAR (20) NOT NULL,
    nama_pasien VARCHAR (100) NOT NULL,
    jenis_kelamin ENUM('L','P') NOT NULL,
    tanggal_lahir DATE NOT NULL,
    alamat TEXT
);

CREATE TABLE dokter (
    id_dokter INT AUTO_INCREMENT PRIMARY KEY,
    nama_dokter VARCHAR (100) NOT NULL,
    spesialisasi VARCHAR (50) NOT NULL
);

CREATE TABLE obat (
    id_obat INT AUTO_INCREMENT PRIMARY KEY,
    nama_obat VARCHAR (100) NOT NULL,
    satuan VARCHAR (30) NOT NULL,
    stok INT NOT NULL
);

CREATE TABLE rekam_medis (
    id_rekam INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    id_dokter INT NOT NULL,
    id_obat INT NOT NULL,
    tanggal_periksa DATE NOT NULL,
    keluhan TEXT NOT NULL,
    diagnosa TEXT NOT NULL,
    tindakan TEXT NOT NULL,
    resep VARCHAR (100) NOT NULL
    FOREIGN KEY (id_pasien) REFERENCES pasien(id_pasien),
    FOREIGN KEY (id_dokter) REFERENCES dokter(id_dokter),
    FOREIGN KEY (id_obat) REFERENCES obat(id_obat)
);

INSERT INTO pasien (no_rm, nama_pasien, jenis_kelamin, tanggal_lahir, alamat)
VALUES ('RM001', 'Andi Saputra', 'L', '2000-05-12', 'Jl. Mawar No. 10, Jakarta'),
('RM002', 'Siti Aminah', 'P', '1998-08-20', 'Jl. Melati No. 5, Semarang'),
('RM003', 'Budi Santoso', 'L', '2002-03-15', 'Jl. Kenanga No. 8, Surabaya');

INSERT INTO dokter (nama_dokter, spesialisasi)
VALUES ('dr. Ahmad Fauzi', 'Dokter Umum'),
('dr. Siti Rahmawati', 'Dokter Gigi');

INSERT INTO obat (nama_obat, satuan, stok)
VALUES ('Paracetamol 500 mg', 'Tablet', 100),
('Amoxicillin 500 mg', 'Kapsul', 75),
('Vitamin C 500 mg', 'Tablet', 50);