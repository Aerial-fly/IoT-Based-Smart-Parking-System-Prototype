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

header('Content-Type: application/json');

$slot_terisi = [];
// Ambil semua nomor slot yang sudah dikonfirmasi (bukan yang masih 0)
$query = "SELECT nomor_slot FROM parkir_aktif WHERE nomor_slot != 0";
$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
    // Ubah dari string ke integer sebelum dimasukkan
    $slot_terisi[] = (int)$row['nomor_slot'];
}

// Kirim hasilnya dalam format JSON, contoh: {"slot_terisi":[1]}
echo json_encode(['slot_terisi' => $slot_terisi]);

mysqli_close($koneksi);
?>