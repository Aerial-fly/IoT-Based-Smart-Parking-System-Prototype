<?php
// --- Detail Koneksi ke Database Lokal Anda ---
$host = "localhost";                                   // Alamat server database, untuk XAMPP selalu 'localhost'
$user = "root";                                       // Username default database XAMPP
$pass = "";         // Password default database XAMPP adalah kosong
$db   = "db_parkir";                                // Nama database yang sudah Anda buat

// --- Membuat Koneksi ---
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek jika koneksi gagal
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Atur zona waktu server ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');
?>