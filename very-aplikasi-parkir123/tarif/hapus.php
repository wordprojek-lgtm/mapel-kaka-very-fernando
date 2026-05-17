<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php?pesan=ID tidak valid&type=danger");
    exit;
}

$hapus = mysqli_query($conn, "DELETE FROM tb_tarif WHERE id_tarif = $id");

if ($hapus) {
    header("Location: index.php?pesan=Tarif berhasil dihapus&type=success");
} else {
    header("Location: index.php?pesan=Gagal menghapus tarif&type=danger");
}
exit;