<?php
session_start();
include '../config/koneksi.php';
include '../auth/cek_login.php';

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';

/* DATA */
$total_kendaraan = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM tb_kendaraan"));
$total_masuk = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM tb_transaksi WHERE status='masuk'"));
$total_keluar = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM tb_transaksi WHERE status='keluar'"));
$pendapatan = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(biaya_total) as total FROM tb_transaksi"))['total'];
?>

<div class="row">

  <!-- ADMIN -->
<?php if($_SESSION['role']=='admin'){ ?>

    <div class="col-md-3">
        <div class="card bg-primary text-white p-3">
            <h6>Total Kendaraan</h6>
            <h3><?= $total_kendaraan ?></h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-dark p-3">
            <h6>Parkir Masuk</h6>
            <h3><?= $total_masuk ?></h3>
        </div>
        </div>

    <div class="col-md-3">
        <div class="card bg-danger text-white p-3">
            <h6>Parkir Keluar</h6>
            <h3><?= $total_keluar ?></h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white p-3">
            <h6>Pendapatan</h6>
            <h3>Rp <?= number_format($pendapatan) ?></h3>
        </div>
    </div>

    <?php } ?>


      <!-- petugas -->
    <?php if($_SESSION['role']=='petugas'){ ?>

    <div class="col-md-6">
        <div class="card bg-success text-white p-4">
            <h5><i class="bi bi-car-front"></i> Parkir Masuk</h5>
            <a href="../transaksi/masuk.php" class="btn btn-light mt-2">Input Masuk</a>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card bg-warning text-dark p-4">
            <h5><i class="bi bi-box-arrow-right"></i> Parkir Keluar</h5>
            <a href="../transaksi/proses_keluar.php" class="btn btn-dark mt-2">Proses Keluar</a>
        </div>
    </div>

    <?php } ?>


      <!-- owner -->
    <?php if($_SESSION['role']=='owner'){ ?>

    <div class="col-md-12">
        <div class="card p-4">
            <h5>Total Pendapatan</h5>
            <h2 class="text-success">Rp <?= number_format($pendapatan) ?></h2>
            <a href="../laporan/index.php" class="btn btn-primary mt-3">Lihat Laporan</a>
        </div>
    </div>
    <?php } ?>
</div>