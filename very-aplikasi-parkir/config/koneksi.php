<?php
$conn = mysqli_connect("localhost", "root", "", "very-aplikasi-parkir");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>