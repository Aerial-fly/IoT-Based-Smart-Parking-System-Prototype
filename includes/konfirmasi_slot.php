<?php
// File: konfirmasi_slot.php
// Versi final dengan validasi penuh

include 'koneksi.php';

// Atur header agar browser tahu ini adalah teks biasa
header('Content-Type: text/plain');

if (isset($_GET['kode']) && isset($_GET['slot'])) {
    // 1. Ambil dan bersihkan data input
    $kode = mysqli_real_escape_string($koneksi, $_GET['kode']);
    $slot = (int)$_GET['slot']; // Jadikan integer untuk keamanan

    // 2. Cek apakah slot yang dituju (misal Slot 1) sudah diklaim oleh KODE LAIN
    $cek_query = "SELECT * FROM parkir_aktif WHERE nomor_slot = '$slot' AND kode_unik != '$kode'";
    $cek_result = mysqli_query($koneksi, $cek_query);

    // Jika query berhasil dan menemukan ada baris data lain
    if ($cek_result && mysqli_num_rows($cek_result) > 0) {
        // Artinya slot sudah diklaim, kirim pesan error dan hentikan proses
        echo "Error: Maaf, Slot $slot sudah dikonfirmasi oleh pengguna lain. Silakan pilih slot yang berbeda.";
    
    } else {
        // 3. Jika slot aman (tidak diklaim orang lain), lanjutkan proses UPDATE
        // Perbarui record HANYA jika kode tersebut ada dan slotnya masih 0 (belum dikonfirmasi)
        $update_query = "UPDATE parkir_aktif SET nomor_slot = '$slot' WHERE kode_unik = '$kode' AND nomor_slot = 0";
        
        if (mysqli_query($koneksi, $update_query)) {
            // Cek apakah ada baris yang benar-benar diperbarui
            if (mysqli_affected_rows($koneksi) > 0) {
                echo "Sukses: Slot $slot berhasil dikonfirmasi untuk kode $kode.";
            } else {
                echo "Info: Kode Anda sudah pernah dikonfirmasi sebelumnya atau kode tidak ditemukan.";
            }
        } else {
            echo "Error: Terjadi kesalahan pada server saat mencoba konfirmasi.";
        }
    }
} else {
    echo "Error: Parameter tidak lengkap. Pastikan Anda mengirim kode dan slot.";
}

mysqli_close($koneksi);
?>