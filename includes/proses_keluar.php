<?php
session_start(); // Mulai sesi

// Jika user belum login, tendang ke halaman login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.html");
    exit;
}
?>

<?php
include 'koneksi.php';

// Ambil 'kode' dari URL yang dikirim oleh JavaScript
if (isset($_GET['kode'])) {
    $kode = $_GET['kode'];

    // Perintah SQL untuk menghapus data
    $query = "DELETE FROM parkir_aktif WHERE kode_unik = '$kode'";

    // Jalankan perintah dan berikan respons
    if (mysqli_query($koneksi, $query)) {
        echo "Data untuk kode " . $kode . " berhasil dihapus. Kendaraan sudah keluar.";
    } else {
        echo "Error: Gagal menghapus data. " . mysqli_error($koneksi);
    }
} else {
    echo "Error: Kode unik tidak ditemukan.";
}

mysqli_close($koneksi);
?>