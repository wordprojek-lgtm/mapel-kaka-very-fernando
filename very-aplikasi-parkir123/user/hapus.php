<?php
include '../config/koneksi.php';
include '../config/log.php';

$id = $_GET['id'];

mysqli_query($conn,"DELETE FROM tb_user WHERE id_user='$id'");
    logAktivitas($conn, "Menghapus data user");
header("Location: index.php");
?>