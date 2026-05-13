<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';

// Proteksi tambahan: Biasanya hanya Admin yang boleh melihat Log
if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Hanya Admin yang bisa melihat log.'); window.location='../dashboard/index.php';</script>";
    exit;
}

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';
?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Log Aktivitas Sistem</h5>
        <span class="badge bg-light text-dark">Data Audit Trail</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Waktu Aktivitas</th>
                        <th width="15%">Username</th>
                        <th width="15%">Role</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    // Join dengan tb_user untuk mendapatkan nama/username yang melakukan aksi
                    $query = mysqli_query($conn, "SELECT tb_log_aktivitas.*, tb_user.username, tb_user.role 
                                                  FROM tb_log_aktivitas 
                                                  JOIN tb_user ON tb_log_aktivitas.id_user = tb_user.id_user 
                                                  ORDER BY tb_log_aktivitas.waktu_aktivitas DESC");
                    
                    if(mysqli_num_rows($query) > 0) {
                        while($row = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($row['waktu_aktivitas'])); ?></td>
                        <td><span class="text-primary fw-bold"><?= $row['username']; ?></span></td>
                        <td>
                            <?php 
                            $badge = ($row['role'] == 'admin') ? 'bg-danger' : 'bg-info';
                            echo "<span class='badge $badge'>".ucfirst($row['role'])."</span>";
                            ?>
                        </td>
                        <td><?= $row['aktivitas']; ?></td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-muted'>Belum ada rekaman aktivitas.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../template/footer.php'; ?>