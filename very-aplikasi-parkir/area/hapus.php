<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();

// Ambil & validasi ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php?pesan=ID tidak valid!&type=danger");
    exit;
}

// Cek apakah area ada
$cek = mysqli_query($conn, "SELECT d_area, nama_area, terisi FROM tb_area_parkir WHERE d_area = $id");
if (!$cek || mysqli_num_rows($cek) == 0) {
    header("Location: index.php?pesan=Data area tidak ditemukan!&type=danger");
    exit;
}
$data      = mysqli_fetch_assoc($cek);
$nama_area = $data['nama_area'];
$terisi    = intval($data['terisi']);

// Cek apakah masih ada kendaraan terisi
if ($terisi > 0) {
    header("Location: index.php?pesan=Area '$nama_area' tidak bisa dihapus karena masih terisi $terisi kendaraan!&type=warning");
    exit;
}

// Proses hapus
if (mysqli_query($conn, "DELETE FROM tb_area_parkir WHERE d_area = $id")) {
    logAktivitas($conn, "Menghapus area parkir: $nama_area");
    header("Location: index.php?pesan=Area '$nama_area' berhasil dihapus!&type=success");
} else {
    header("Location: index.php?pesan=Gagal menghapus area: " . mysqli_error($conn) . "&type=danger");
}
exit;
?>