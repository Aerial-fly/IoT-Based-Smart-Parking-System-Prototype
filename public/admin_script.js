function muatData() {
    // Ganti logika dengan fetch() untuk memanggil API PHP
    fetch('http://localhost/parkir-pintar/includes/get_data_admin.php')
        .then(response => response.json())
        .then(data => {
            let tabelBody = document.getElementById('data-parkir');
            tabelBody.innerHTML = ''; 

            if (data.length === 0) {
                 tabelBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Tidak ada kendaraan yang parkir.</td></tr>';
            }

            data.forEach(item => {
                let baris = `<tr>
                    <td>${item.kode_unik}</td>
                    <td>${item.nomor_slot}</td>
                    <td>${item.waktu_masuk}</td>
                    <td>${item.durasi}</td>
                    <td>${item.biaya}</td>
                    <td><button class="glass-button" onclick="prosesKeluar('${item.kode_unik}')" style="background-color: #e11d48;">Keluar</button></td>
                </tr>`;
                tabelBody.innerHTML += baris;
            });
        })
        .catch(error => console.error('Error memuat data:', error));
}

function prosesKeluar(kode) {
    if (confirm(`Anda yakin ingin memproses keluar untuk kode ${kode}?`)) {
        fetch(`http://localhost/parkir-pintar/includes/proses_keluar.php?kode=${kode}`)
            .then(response => response.text())
            .then(message => {
                alert(message);
                muatData(); // Muat ulang data setelah menghapus
            });
    }
}

// Muat data saat halaman pertama kali dibuka, lalu setiap 5 detik
document.addEventListener('DOMContentLoaded', muatData);
setInterval(muatData, 5000); // Real-time update