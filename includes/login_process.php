<?php
session_start(); // Wajib ada untuk memulai sesi
include 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Cari user di database
$query = "SELECT * FROM admin_users WHERE username = '$username'";
$result = mysqli_query($koneksi, $query);

if ($result && mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    // Verifikasi password yang diinput dengan hash di database
    if (password_verify($password, $user['password'])) {
        // Jika password cocok, simpan data ke session
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        // Arahkan ke halaman admin
        header("location: ../public/admin.php");
    } else {
        // Jika password salah
        // BARU (Benar)
        echo "Password salah. <a href='../public/login.html'>Coba lagi</a>.";
    }
} else {
    // Jika username tidak ditemukan
    // BARU (Benar)
    echo "Username tidak ditemukan. <a href='../public/login.html'>Coba lagi</a>.";
}
?>