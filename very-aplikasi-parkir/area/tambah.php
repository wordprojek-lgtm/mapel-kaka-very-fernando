<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_area = trim(mysqli_real_escape_string($conn, $_POST['nama_area']));
    $kapasitas = intval($_POST['kapasitas']);
    $terisi    = intval($_POST['terisi']);

    if (empty($nama_area)) $error[] = "Nama area wajib diisi.";
    if ($kapasitas <= 0)   $error[] = "Kapasitas harus lebih dari 0.";
    if ($terisi < 0)       $error[] = "Terisi tidak boleh negatif.";
    if ($terisi > $kapasitas) $error[] = "Jumlah terisi tidak boleh melebihi kapasitas.";

    if (!empty($nama_area)) {
        $cek = mysqli_query($conn, "SELECT d_area FROM tb_area_parkir WHERE nama_area = '$nama_area'");
        if (mysqli_num_rows($cek) > 0) {
            $error[] = "Nama area <strong>$nama_area</strong> sudah terdaftar!";
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO tb_area_parkir (nama_area, kapasitas, terisi)
                VALUES ('$nama_area', '$kapasitas', '$terisi')";
        if (mysqli_query($conn, $sql)) {
            logAktivitas($conn, "Menambahkan area parkir: $nama_area");
            header("Location: index.php?pesan=Area parkir berhasil ditambahkan!&type=success");
            exit;
        } else {
            $error[] = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
}

function val($key, $default = '') {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : $default;
}

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">
    <div class="row justify-content-center">
      <div class="col-lg-7">

        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Area Parkir</a></li>
            <li class="breadcrumb-item active">Tambah Area</li>
          </ol>
        </nav>

        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-header rounded-top-4 py-3"
               style="background:linear-gradient(135deg,#1a73e8,#0d47a1);color:white">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Tambah Area Parkir</h5>
          </div>

          <div class="card-body p-4">

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0 mt-1">
                  <?php foreach ($error as $e): echo "<li>$e</li>"; endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <form method="POST" action="tambah.php">

              <div class="mb-3">
                <label class="form-label fw-semibold">Nama Area <span class="text-danger">*</span></label>
                <input type="text" name="nama_area" class="form-control"
                       placeholder="Contoh: Area A, Lantai 1, Parkir Utara"
                       value="<?= val('nama_area') ?>" required>
                <div class="form-text">Nama harus unik, tidak boleh sama dengan area lain.</div>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Kapasitas (Slot) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="number" name="kapasitas" class="form-control"
                           min="1" placeholder="0" value="<?= val('kapasitas') ?>" required>
                    <span class="input-group-text">slot</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Terisi</label>
                  <div class="input-group">
                    <input type="number" name="terisi" class="form-control"
                           min="0" placeholder="0" value="<?= val('terisi', '0') ?>">
                    <span class="input-group-text">slot</span>
                  </div>
                </div>
              </div>

              <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-save me-2"></i> Simpan
                </button>
                <a href="index.php" class="btn btn-outline-secondary px-4">
                  <i class="bi bi-arrow-left me-2"></i> Batal
                </a>
                <button type="reset" class="btn btn-outline-warning px-4">
                  <i class="bi bi-arrow-counterclockwise me-2"></i> Reset
                </button>
              </div>

            </form>
          </div>

          <div class="card-footer bg-white text-muted small text-end rounded-bottom-4">
            Aplikasi Parkir &copy; <?= date('Y') ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../template/footer.php'; ?>