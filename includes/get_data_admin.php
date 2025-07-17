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

// Beri tahu browser bahwa file ini akan mengirimkan data dalam format JSON
header('Content-Type: application/json');

// --- Logika Bisnis Parkir ---
$tarif_per_jam = 3000; // Ganti jika tarifnya berbeda
$data_parkir_siap_kirim = [];

// Perintah SQL untuk mengambil semua data parkir yang aktif
$query = "SELECT * FROM parkir_aktif ORDER BY waktu_masuk DESC";
$result = mysqli_query($koneksi, $query);

// Proses setiap baris data satu per satu
while ($row = mysqli_fetch_assoc($result)) {
    $waktu_masuk = new DateTime($row['waktu_masuk']);
    $waktu_sekarang = new DateTime();
    $durasi = $waktu_sekarang->diff($waktu_masuk);

    // Format durasi agar mudah dibaca (contoh: 1 jam, 25 mnt)
    $durasi_format = $durasi->h . ' jam, ' . $durasi->i . ' mnt';

    // Hitung total jam parkir (dibulatkan ke atas)
    $total_jam_parkir = ($durasi->days * 24) + $durasi->h + ($durasi->i > 0 ? 1 : 0);
    if ($total_jam_parkir == 0) $total_jam_parkir = 1; // Minimum bayar 1 jam
    
    $biaya = $total_jam_parkir * $tarif_per_jam;

    // Masukkan data yang sudah diolah ke dalam sebuah array
    $data_parkir_siap_kirim[] = [
        'kode_unik' => $row['kode_unik'],
        'nomor_slot' => $row['nomor_slot'],
        'waktu_masuk' => $waktu_masuk->format('d M Y, H:i'),
        'durasi' => $durasi_format,
        'biaya' => 'Rp ' . number_format($biaya)
    ];
}

// Ubah array PHP menjadi format JSON dan kirimkan sebagai respons
echo json_encode($data_parkir_siap_kirim);

mysqli_close($koneksi);
?>