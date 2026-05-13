<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();

include '../config/koneksi.php';
include '../template/header.php';
include '../template/sidebar.php';

// Ambil ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: index.php?pesan=ID tidak valid!&type=danger");
    exit;
}

// Ambil data kendaraan
$q = mysqli_query($conn, "SELECT * FROM tb_kendaraan WHERE id_kendaraan = $id");
if (!$q || mysqli_num_rows($q) == 0) {
    header("Location: index.php?pesan=Data kendaraan tidak ditemukan!&type=danger");
    exit;
}
$data = mysqli_fetch_assoc($q);

// Dropdown user
$user_query = mysqli_query($conn, "SELECT id_user, nama_lengkap FROM tb_user ORDER BY nama_lengkap ASC");

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plat_nomor      = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['plat_nomor'])));
    $jenis_kendaraan = trim(mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']));
    $warna           = trim(mysqli_real_escape_string($conn, $_POST['warna']));
    $pemilik         = trim(mysqli_real_escape_string($conn, $_POST['pemilik']));
    $id_user         = intval($_POST['id_user']);

    // Validasi
    if (empty($plat_nomor))      $error[] = "Plat nomor wajib diisi.";
    if (empty($jenis_kendaraan)) $error[] = "Jenis kendaraan wajib dipilih.";
    if (empty($pemilik))         $error[] = "Nama pemilik wajib diisi.";

    // Cek duplikat plat nomor (kecuali ID sendiri)
    if (!empty($plat_nomor)) {
        $cek = mysqli_query($conn, "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor' AND id_kendaraan != $id");
        if (mysqli_num_rows($cek) > 0) {
            $error[] = "Plat nomor <strong>$plat_nomor</strong> sudah digunakan kendaraan lain!";
        }
    }

    if (empty($error)) {
        $id_user_val = $id_user > 0 ? $id_user : "NULL";
        $sql = "UPDATE tb_kendaraan SET
                    plat_nomor      = '$plat_nomor',
                    jenis_kendaraan = '$jenis_kendaraan',
                    warna           = '$warna',
                    pemilik         = '$pemilik',
                    id_user         = $id_user_val
                WHERE id_kendaraan = $id";

        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?pesan=Data kendaraan berhasil diperbarui!&type=success");
            exit;
        } else {
            $error[] = "Gagal memperbarui data: " . mysqli_error($conn);
        }
    }

    // Jika error, gunakan nilai POST
    $data['plat_nomor']      = $_POST['plat_nomor'];
    $data['jenis_kendaraan'] = $_POST['jenis_kendaraan'];
    $data['warna']           = $_POST['warna'];
    $data['pemilik']         = $_POST['pemilik'];
    $data['id_user']         = $_POST['id_user'];
}
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">
    <div class="row justify-content-center">
      <div class="col-lg-7">

        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Kendaraan</a></li>
            <li class="breadcrumb-item active">Edit Kendaraan</li>
          </ol>
        </nav>

        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-header rounded-top-4 py-3"
               style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white">
            <h5 class="mb-0">
              <i class="fas fa-edit me-2"></i> Edit Data Kendaraan
              <span class="badge bg-white text-warning ms-2"># <?= $id ?></span>
            </h5>
          </div>

          <div class="card-body p-4">

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0 mt-1">
                  <?php foreach ($error as $e): echo "<li>$e</li>"; endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <form method="POST" action="edit.php?id=<?= $id ?>">

              <div class="mb-3">
                <label class="form-label fw-semibold">Plat Nomor <span class="text-danger">*</span></label>
                <input type="text" name="plat_nomor" class="form-control text-uppercase"
                       value="<?= htmlspecialchars($data['plat_nomor']) ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Jenis Kendaraan <span class="text-danger">*</span></label>
                <select name="jenis_kendaraan" class="form-select" required>
                  <option value="">-- Pilih Jenis --</option>
                  <?php foreach (['Motor','Mobil','Truk','Bus','Sepeda'] as $j): ?>
                  <option value="<?= $j ?>" <?= $data['jenis_kendaraan'] === $j ? 'selected' : '' ?>><?= $j ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Warna</label>
                <input type="text" name="warna" class="form-control"
                       value="<?= htmlspecialchars($data['warna']) ?>">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Pemilik <span class="text-danger">*</span></label>
                <input type="text" name="pemilik" class="form-control"
                       value="<?= htmlspecialchars($data['pemilik']) ?>" required>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold">User (Opsional)</label>
                <select name="id_user" class="form-select">
                  <option value="0">-- Pilih User --</option>
                  <?php while ($u = mysqli_fetch_assoc($user_query)): ?>
                  <option value="<?= $u['id_user'] ?>"
                    <?= $data['id_user'] == $u['id_user'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['nama_lengkap']) ?>
                  </option>
                  <?php endwhile; ?>
                </select>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning text-white px-4">
                  <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
                <a href="index.php" class="btn btn-outline-secondary px-4">
                  <i class="fas fa-arrow-left me-2"></i> Batal
                </a>
              </div>

            </form>
          </div>

          <div class="card-footer bg-white text-muted small text-end rounded-bottom-4">
            ID Kendaraan: #<?= $id ?> &nbsp;|&nbsp; Aplikasi Parkir &copy; <?= date('Y') ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelector('[name="plat_nomor"]').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php include '../template/footer.php'; ?>