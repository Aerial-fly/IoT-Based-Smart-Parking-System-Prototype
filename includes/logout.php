<?php
// Memulai sesi untuk bisa mengaksesnya
session_start();

// Menghapus semua data variabel di dalam sesi
$_SESSION = array();

// Menghancurkan sesi itu sendiri
session_destroy();

// Mengarahkan pengguna kembali ke halaman login
header("location: ../public/login.html");
exit;
?>