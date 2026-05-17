<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM tb_user WHERE id_user='$id'"));

if(isset($_POST['update'])){
    $nama = $_POST['nama'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    mysqli_query($conn,"
        UPDATE tb_user SET
        nama_lengkap='$nama',
        role='$role',
        status_aktif='$status'
        WHERE id_user='$id'
    ");
    logAktivitas($conn, "Mengubah data user");
    header("Location: index.php");
}
?>
<?php include '../template/header.php'; ?>
<?php include '../template/sidebar.php'; ?>
<?php include '../template/navbar.php'; ?>

<div class="card p-3">
    <h5>Edit User</h5>

    <form method="POST">
        <input type="text" name="nama" value="<?= $data['nama_lengkap'] ?>" class="form-control mb-2">

        <select name="role" class="form-control mb-2">
            <option value="admin">Admin</option>
            <option value="petugas">Petugas</option>
            <option value="owner">Owner</option>
        </select>

        <select name="status" class="form-control mb-2">
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
        </select>

        <button name="update" class="btn btn-primary">Update</button>
    </form>
</div>
<?php include '../template/footer.php'; ?>