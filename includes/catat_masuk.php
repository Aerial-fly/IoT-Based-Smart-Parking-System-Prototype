<?php
// Panggil file koneksi untuk bisa terhubung ke database
include 'koneksi.php';

// Ambil data 'kode' dan 'slot' dari URL yang dikirim ESP8266
if (isset($_GET['kode']) && isset($_GET['slot'])) {
    $kode = $_GET['kode'];
    $slot = $_GET['slot'];
    $waktu_masuk = date("Y-m-d H:i:s"); // Ambil waktu server saat ini (WIB)

    // Perintah SQL untuk memasukkan data baru
    $query = "INSERT INTO parkir_aktif (kode_unik, nomor_slot, waktu_masuk) VALUES ('$kode', '$slot', '$waktu_masuk')";

    // Jalankan perintah dan berikan respons
    if (mysqli_query($koneksi, $query)) {
        echo "Sukses: Data parkir untuk kode $kode berhasil dicatat.";
    } else {
        echo "Error: Gagal mencatat data. " . mysqli_error($koneksi);
    }
} else {
    echo "Error: Parameter 'kode' atau 'slot' tidak ditemukan di URL.";
}

// Tutup koneksi ke database
mysqli_close($koneksi);
?>