<?php
/**
 * Fungsi untuk mencatat aktivitas pengguna ke database
 * @param mysqli $conn Koneksi database
 * @param string $pesan Pesan aktivitas yang ingin dicatat
 */
function logAktivitas($conn, $pesan) {
    // Ambil ID User dari session jika sudah login
    // Jika belum login (misal saat percobaan login gagal), set ke null atau 0
    $id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0;

    // Bersihkan pesan dari karakter aneh
    $pesan_aman = mysqli_real_escape_string($conn, $pesan);

    // Query untuk memasukkan data ke tabel log
    $query = "INSERT INTO log_aktivitas (id_user, pesan) VALUES ('$id_user', '$pesan_aman')";
    
    // Jalankan query
    mysqli_query($conn, $query);
}
?>