<?php
/**
 * Fungsi untuk mencatat aktivitas pengguna ke database
 * @param mysqli $conn Koneksi database
 * @param string $pesan Pesan aktivitas yang ingin dicatat
 */
function logAktivitas($conn, $pesan) {
    // ✅ FIX: Pastikan session sudah ada dan id_user valid (> 0)
    // Jika tidak ada session atau id_user = 0, batalkan pencatatan
    if (!isset($_SESSION['id_user']) || intval($_SESSION['id_user']) <= 0) {
        return; // Jangan insert jika user tidak dikenali
    }

    $id_user = (int)$_SESSION['id_user'];

    // Bersihkan pesan dari karakter berbahaya
    $pesan_aman = mysqli_real_escape_string($conn, $pesan);

    // Nama tabel = tb_log_aktivitas, kolom = aktivitas
    // waktu_aktivitas pakai NOW() agar otomatis terisi
    $query = "INSERT INTO tb_log_aktivitas (id_user, aktivitas, waktu_aktivitas) 
              VALUES ('$id_user', '$pesan_aman', NOW())";

    mysqli_query($conn, $query);
}
?>