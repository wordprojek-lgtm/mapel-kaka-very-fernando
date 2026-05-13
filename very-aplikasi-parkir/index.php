<?php
session_start();
include 'config/koneksi.php';

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "
        SELECT * FROM tb_user
        WHERE username='$username'
        AND password='$password'
        AND status_aktif=1
    ");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['role']    = $data['role'];
        $_SESSION['nama']    = $data['nama_lengkap'];
        include 'config/log.php';
        logAktivitas($conn, "Login ke sistem");

        header("Location: dashboard/index.php");
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Aplikasi Parkir</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
   <style>
  body {
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
        height: 100vh;
    }
    .logo-login {
        width: 120px;
        height: 120px;
        object-fit: contain;
        border-radius: 50%;
        background: white;
        padding: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .login-card {
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    .form-control {
        border-radius: 10px;
}
    .btn-login {
        border-radius: 10px;
}
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="card login-card p-4" style="width:350px;">

        <div class="text-center mb-3">
            <img src="logo.jpeg" 
            alt="Logo Parkir App" 
            class="logo-login mb-2">
            <h3>Aplikasi Parkir</h3>
            <p class="text-muted">
            Silahkan Login
            </p>
        </div>

        <?php if(isset($error)) { ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php } ?>

<form method="POST">
    
            <div class="mb-3">
                <label>Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <button name="login" class="btn btn-primary w-100 btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
            <div class="text-center mb-3">
                <small class="text-muted">
                Akun hanya dapat dibuat oleh admin. Silahkan hubungi admin jika Anda belum memiliki akun.
                </small>
            </div>
            </form>

<?php include 'template/footer.php'; ?>
</div>

</div>

</body>
</html>