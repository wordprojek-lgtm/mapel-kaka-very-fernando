<?php
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/koneksi.php';
include '../config/log.php';

onlyAdmin();

if(isset($_POST['simpan'])){
    $nama = $_POST['nama'];
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    mysqli_query($conn,"
        INSERT INTO tb_user
        (nama_lengkap, username, password, role, status_aktif)
        VALUES ('$nama','$user','$pass','$role',1)
    ");
    logAktivitas($conn, "Menambahkan user baru");
    header("Location: index.php");
}
?>
<?php include '../template/header.php'; ?>
<?php include '../template/sidebar.php'; ?>
<?php include '../template/navbar.php'; ?>

<div class="card p-3">
    <h5>Tambah User</h5>

    <form method="POST">
        <div class="mb-2">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
                <div class="mb-2">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="admin">Admin</option>
                <option value="petugas">Petugas</option>
                <option value="owner">Owner</option>
            </select>
        </div>

        <button name="simpan" class="btn btn-success">Simpan</button>
    </form>
</div>

<?php include '../template/footer.php'; ?>