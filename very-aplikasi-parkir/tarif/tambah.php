<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();
include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';

$error  = [];
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_kendaraan = trim(mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']));
    $tarif_per_jam   = intval($_POST['tarif_per_jam']);

    // Validasi
    if (empty($jenis_kendaraan)) $error[] = "Jenis kendaraan wajib dipilih.";
    if ($tarif_per_jam < 0)      $error[] = "Tarif per jam tidak boleh negatif.";

    if (empty($error)) {
        $sql = "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam)
                VALUES ('$jenis_kendaraan', '$tarif_per_jam')";

        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?pesan=Tarif berhasil ditambahkan!&type=success");
            exit;
        } else {
            $error[] = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
}
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">

    <div class="row justify-content-center">
      <div class="col-lg-7">

        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Tarif</a></li>
            <li class="breadcrumb-item active">Tambah Tarif</li>
          </ol>
        </nav>

        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-header rounded-top-4 py-3" style="background:linear-gradient(135deg,#1a73e8,#0d47a1);color:white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Tambah Tarif Parkir</h5>
          </div>

          <div class="card-body p-4">

            <!-- ERROR -->
            <?php if (!empty($error)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($error as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="POST" action="tambah.php">

              <!-- Jenis Kendaraan -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Jenis Kendaraan <span class="text-danger">*</span></label>
                <select name="jenis_kendaraan" class="form-select" required>
                  <option value="">-- Pilih Jenis Kendaraan --</option>
                  <?php
                  $jenis_list = ['motor','mobil','truk','bus','sepeda','lainnya'];
                  foreach ($jenis_list as $j):
                    $sel = (isset($_POST['jenis_kendaraan']) && $_POST['jenis_kendaraan'] === $j) ? 'selected' : '';
                  ?>
                  <option value="<?= $j ?>" <?= $sel ?>><?= ucfirst($j) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Tarif Per Jam -->
              <div class="mb-4">
                <label class="form-label fw-semibold">Tarif Per Jam (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" name="tarif_per_jam" class="form-control"
                         min="0" step="500" placeholder="0"
                         value="<?= isset($_POST['tarif_per_jam']) ? $_POST['tarif_per_jam'] : '' ?>"
                         required>
                </div>
                <div class="form-text">Tarif parkir per jam untuk jenis kendaraan ini.</div>
              </div>

              <!-- TOMBOL -->
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                  <i class="fas fa-save me-2"></i> Simpan
                </button>
                <a href="index.php" class="btn btn-outline-secondary px-4">
                  <i class="fas fa-arrow-left me-2"></i> Batal
                </a>
                <button type="reset" class="btn btn-outline-warning px-4">
                  <i class="fas fa-undo me-2"></i> Reset
                </button>
              </div>

            </form>
          </div><!-- /card-body -->
        </div><!-- /card -->

      </div>
    </div>
  </div>
</div>

<?php include '../template/footer.php'; ?>