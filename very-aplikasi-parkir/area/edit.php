<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();

// Ambil & validasi ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: index.php?pesan=ID tidak valid!&type=danger");
    exit;
}

// Ambil data area
$q = mysqli_query($conn, "SELECT * FROM tb_area_parkir WHERE d_area = $id");
if (!$q || mysqli_num_rows($q) == 0) {
    header("Location: index.php?pesan=Data area tidak ditemukan!&type=danger");
    exit;
}
$data = mysqli_fetch_assoc($q);

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_area = trim(mysqli_real_escape_string($conn, $_POST['nama_area']));
    $kapasitas = intval($_POST['kapasitas']);
    $terisi    = intval($_POST['terisi']);

    // Validasi
    if (empty($nama_area))    $error[] = "Nama area wajib diisi.";
    if ($kapasitas <= 0)      $error[] = "Kapasitas harus lebih dari 0.";
    if ($terisi < 0)          $error[] = "Terisi tidak boleh negatif.";
    if ($terisi > $kapasitas) $error[] = "Jumlah terisi tidak boleh melebihi kapasitas.";

    if (!empty($nama_area)) {
        $cek = mysqli_query($conn, "SELECT d_area FROM tb_area_parkir WHERE nama_area = '$nama_area' AND d_area != $id");
        if (mysqli_num_rows($cek) > 0) {
            $error[] = "Nama area <strong>$nama_area</strong> sudah digunakan area lain!";
        }
    }

    if (empty($error)) {
        $sql = "UPDATE tb_area_parkir SET
                    nama_area = '$nama_area',
                    kapasitas = '$kapasitas',
                    terisi    = '$terisi'
                WHERE d_area = $id";

        if (mysqli_query($conn, $sql)) {
            logAktivitas($conn, "Mengubah area parkir: $nama_area");
            header("Location: index.php?pesan=Area parkir berhasil diperbarui!&type=success");
            exit;
        } else {
            $error[] = "Gagal memperbarui data: " . mysqli_error($conn);
        }
    }

    // Jika error, tampilkan nilai POST
    $data['nama_area'] = $_POST['nama_area'];
    $data['kapasitas'] = $_POST['kapasitas'];
    $data['terisi']    = $_POST['terisi'];
}

$terisi    = intval($data['terisi']);
$kapasitas = intval($data['kapasitas']);
$persen    = $kapasitas > 0 ? round(($terisi / $kapasitas) * 100) : 0;
$bar_color = $persen >= 90 ? 'danger' : ($persen >= 60 ? 'warning' : 'success');

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
            <li class="breadcrumb-item active">Edit Area</li>
          </ol>
        </nav>

        <!-- INFO KAPASITAS REALTIME -->
        <div class="card border-0 shadow-sm rounded-4 mb-3">
          <div class="card-body py-3 px-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-semibold">Kondisi Area Saat Ini</span>
              <span class="badge bg-<?= $bar_color ?>"><?= $persen ?>% Terisi</span>
            </div>
            <div class="progress" style="height:10px">
              <div class="progress-bar bg-<?= $bar_color ?> rounded" style="width:<?= $persen ?>%"></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
              <small class="text-muted">Terisi: <strong><?= $terisi ?></strong> slot</small>
              <small class="text-muted">Sisa: <strong><?= $kapasitas - $terisi ?></strong> slot</small>
              <small class="text-muted">Kapasitas: <strong><?= $kapasitas ?></strong> slot</small>
            </div>
          </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-header rounded-top-4 py-3"
               style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white">
            <h5 class="mb-0">
              <i class="bi bi-pencil me-2"></i> Edit Area Parkir
              <span class="badge bg-white text-warning ms-2"># <?= $id ?></span>
            </h5>
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

            <form method="POST" action="edit.php?id=<?= $id ?>">

              <div class="mb-3">
                <label class="form-label fw-semibold">Nama Area <span class="text-danger">*</span></label>
                <input type="text" name="nama_area" class="form-control"
                       value="<?= htmlspecialchars($data['nama_area']) ?>" required>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Kapasitas (Slot) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="number" name="kapasitas" class="form-control"
                           min="1"
                           value="<?= htmlspecialchars($data['kapasitas']) ?>" required>
                    <span class="input-group-text">slot</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Terisi</label>
                  <div class="input-group">
                    <input type="number" name="terisi" class="form-control"
                           min="0"
                           value="<?= htmlspecialchars($data['terisi']) ?>">
                    <span class="input-group-text">slot</span>
                  </div>
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning text-white px-4">
                  <i class="bi bi-save me-2"></i> Simpan Perubahan
                </button>
                <a href="index.php" class="btn btn-outline-secondary px-4">
                  <i class="bi bi-arrow-left me-2"></i> Batal
                </a>
              </div>

            </form>
          </div>

          <div class="card-footer bg-white text-muted small text-end rounded-bottom-4">
            ID Area: #<?= $id ?> &nbsp;|&nbsp; Aplikasi Parkir &copy; <?= date('Y') ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../template/footer.php'; ?>