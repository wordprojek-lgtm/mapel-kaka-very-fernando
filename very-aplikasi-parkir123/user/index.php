<?php
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/koneksi.php';

onlyAdmin();

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';
?>

<div class="card p-3">
    <div class="d-flex justify-content-between mb-3">
        <h5>Data User</h5>
        <a href="tambah.php" class="btn btn-primary">+ Tambah User</a>
    </div>

    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Role</th>
               <th>Status</th>
               <th></th>
               <th>Aksi</th>
               </tr>
               </thead>
                       <tbody>
        <?php
        $no=1;
        $q = mysqli_query($conn,"SELECT * FROM tb_user");

        while($d = mysqli_fetch_assoc($q)){
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $d['nama_lengkap'] ?></td>
                <td><?= $d['username'] ?></td>
                <td><span class="badge bg-info"><?= $d['role'] ?></span></td>
                <td>
                    <?= $d['status_aktif'] ? 
                        '<span class="badge bg-success">Aktif</span>' : 
                        '<span class="badge bg-danger">Nonaktif</span>' ?>
                </td>
                <td>
                    <a href="edit.php?id=<?= $d['id_user'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="hapus.php?id=<?= $d['id_user'] ?>" 
                       onclick="return confirm('Hapus data?')"
                       class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
        </table>
        </div>
        <?php include '../template/footer.php'; ?>