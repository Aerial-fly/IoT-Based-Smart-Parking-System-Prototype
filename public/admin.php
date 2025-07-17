<?php
// Memulai sesi dan memeriksa apakah admin sudah login
session_start();

// Jika sesi 'loggedin' tidak ada atau tidak bernilai true, tendang ke halaman login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dasbor Admin Parkir</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Dasbor Parkir Pintar</h1>

        <div style="text-align: right; margin-bottom: 1rem;">
            Selamat datang, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>!
            
            <a href="../includes/logout.php" class="glass-button" style="text-decoration: none; margin-left: 15px;">Logout</a>
        </div>

        <div class="glass-card">
            <table>
                <thead>
                    <tr>
                        <th>Kode Unik</th>
                        <th>Nomor Slot</th>
                        <th>Waktu Masuk</th>
                        <th>Durasi Parkir</th>
                        <th>Estimasi Biaya</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="data-parkir">
                    </tbody>
            </table>
        </div>
    </div>

    <script src="admin_script.js"></script>
</body>
</html>