<style>
/* LOGO */
.logo-sidebar {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 50%;
    background: white;
    padding: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
</style>
<div class="sidebar">
<div class="d-flex align-items-center px-3 py-3">
    <img src="../logo.jpeg" class="logo-sidebar me-2">
    <h5 class="mb-0 text-white">Aplikasi Parkir</h5>
</div>

    <?php if($_SESSION['role']=='admin'){ ?>
        <a href="../dashboard/index.php"><i class="bi bi-card-text"></i> Dashboard</a>
        <a href="../user/index.php"><i class="bi bi-people"></i> User</a>
        <a href="../kendaraan/index.php"><i class="bi bi-car-front"></i> Kendaraan</a>
        <a href="../tarif/index.php"><i class="bi bi-cash-coin"></i> Tarif Parkir</a>
        <a href="../area/index.php"><i class="bi bi-bookmark"></i> Area Parkir</a>
        <a href="../log/index.php"><i class="bi bi-clock-history"></i> Log Aktivitas
    <?php if($_SESSION['role']=='owner'){ ?>
        <li class="nav-item">
        <a href="../laporan/index.php" class="nav-link">
            Laporan Transaksi
            </a>
            </li>
            <?php } ?>