error_reporting(E_ALL);
ini_set('display_errors', 1);
<?php
session_start();
include '../config/koneksi.php'; // Pastikan jalur file koneksi benar

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Mencari user berdasarkan username
    $query = mysqli_query($conn, "SELECT * FROM tb_user WHERE username='$username' AND password='$password'");
    $cek = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);

        // Menyimpan data ke dalam session
        $_SESSION['id_user']      = $data['id_user'];
        $_SESSION['username']     = $data['username'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['role']         = $data['role'];

        // Alur pengarahan berdasarkan Role
        if ($data['role'] == "admin") {
            header("location:../admin/dashboard.php");
        } else if ($data['role'] == "petugas") {
            header("location:../petugas/dashboard.php");
        } else if ($data['role'] == "owner") {
            header("location:../owner/dashboard.php");
        } else {
            // Jika role tidak dikenal
            header("location:../index.php?pesan=gagal");
        }
    } else {
        // Jika username atau password salah
        header("location:../index.php?pesan=gagal");
    }
}
?>