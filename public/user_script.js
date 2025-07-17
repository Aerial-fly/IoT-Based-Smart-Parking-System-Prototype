function konfirmasiDanCek() {
    const inputKode = document.getElementById('input-kode').value;
    const slotTerpilih = document.querySelector('input[name="slot"]:checked');
    const divHasil = document.getElementById('hasil-cek');
    divHasil.style.display = 'block';

    if (!inputKode || !slotTerpilih) {
        divHasil.innerHTML = '<p style="text-align:center; color: #ff8a8a;">Harap masukkan kode dan pilih nomor slot Anda!</p>';
        return;
    }

    const kode = inputKode;
    const slot = slotTerpilih.value;
    divHasil.innerHTML = '<p style="text-align:center;">Mengonfirmasi slot...</p>';

    // Kirim data konfirmasi ke server
    fetch(`http://localhost/parkir-pintar/includes/konfirmasi_slot.php?kode=${kode}&slot=${slot}`)
        .then(response => response.text())
        .then(message => {
            console.log("Respons dari Server:", message); // Untuk debugging
            
            // Cek apakah ada kata "Error:" atau "Info:" dalam pesan dari server
            if (message.includes("Error:") || message.includes("Info:")) {
                // Jika ada, tampilkan pesan itu langsung
                divHasil.innerHTML = `<p style="text-align:center; color: #ffafaf;">${message}</p>`;
            } else {
                // Jika tidak ada error (berarti SUKSES), baru cek status final
                cekStatusFinal(kode); 
            }
        })
        .catch(error => {
            console.error('Error saat konfirmasi:', error);
            divHasil.innerHTML = '<p style="text-align:center; color: #ff8a8a;">Gagal terhubung ke server untuk konfirmasi.</p>';
        });
}

function cekStatusFinal(kode) {
    const divHasil = document.getElementById('hasil-cek');
    divHasil.innerHTML = '<p style="text-align:center;">Mengecek status final...</p>';
    
    fetch(`http://localhost/parkir-pintar/includes/get_data_admin.php`)
        .then(response => response.json())
        .then(data => {
            const dataPengguna = data.find(item => item.kode_unik === kode);
            if (dataPengguna && dataPengguna.nomor_slot != 0) {
                divHasil.innerHTML = `
                    <h3 style="text-align: center; margin-bottom: 1.5rem;">✅ Parkir Terkonfirmasi!</h3>
                    <p style="font-size: 1.1em;"><strong>Anda Parkir di Slot:</strong> Nomor ${dataPengguna.nomor_slot}</p>
                    <hr style="border: 0; border-top: 1px solid rgba(255, 255, 255, 0.2); margin: 1rem 0;">
                    <p><strong>Kode Unik:</strong> ${dataPengguna.kode_unik}</p>
                    <p><strong>Waktu Masuk:</strong> ${dataPengguna.waktu_masuk}</p>
                    <p><strong>Durasi Saat Ini:</strong> ${dataPengguna.durasi}</p>
                    <p><strong>Estimasi Biaya:</strong> <span style="font-weight: bold; font-size: 1.2em;">${dataPengguna.biaya}</span></p>
                `;
            } else {
                divHasil.innerHTML = '<p style="text-align:center;">Gagal menemukan data setelah konfirmasi. Mohon coba lagi.</p>';
            }
        });
}