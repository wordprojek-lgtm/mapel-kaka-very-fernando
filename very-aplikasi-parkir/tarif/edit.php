<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php?pesan=ID tidak valid&type=danger");
    exit;
}

// Ambil data tarif dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM tb_tarif WHERE id_tarif = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: index.php?pesan=Data tidak ditemukan&type=danger");
    exit;
}

$jenis_list = ['motor', 'mobil', 'truk', 'bus', 'sepeda', 'lainnya'];
$pesan      = '';
$pesan_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis         = trim($_POST['jenis_kendaraan'] ?? '');
    $tarif_per_jam = intval($_POST['tarif_per_jam'] ?? 0);

    // Validasi
    if (!in_array($jenis, $jenis_list)) {
        $pesan      = "Jenis kendaraan tidak valid.";
        $pesan_type = "danger";
    } elseif ($tarif_per_jam < 0) {
        $pesan      = "Tarif per jam tidak boleh negatif.";
        $pesan_type = "danger";
    } else {
        // Prepared statement untuk UPDATE
        $stmt_up = mysqli_prepare($conn, "UPDATE tb_tarif SET jenis_kendaraan = ?, tarif_per_jam = ? WHERE id_tarif = ?");
        mysqli_stmt_bind_param($stmt_up, "sii", $jenis, $tarif_per_jam, $id);

        if (mysqli_stmt_execute($stmt_up)) {
            mysqli_stmt_close($stmt_up);
            header("Location: index.php?pesan=Tarif berhasil diperbarui&type=success");
            exit;
        } else {
            $mysql_err  = mysqli_stmt_error($stmt_up);
            mysqli_stmt_close($stmt_up);

            if (strpos($mysql_err, 'Data truncated') !== false) {
                $pesan = "Jenis kendaraan '$jenis' tidak diizinkan database. Perbarui struktur tabel terlebih dahulu.";
            } else {
                $pesan = "Gagal memperbarui tarif: " . $mysql_err;
            }
            $pesan_type = "danger";
        }
    }
}
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="fw-bold mb-0"><i class="fas fa-edit me-2 text-warning"></i>Edit Tarif</h4>
        <small class="text-muted">Ubah data tarif parkir</small>
      </div>
      <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
      </a>
    </div>

    <?php if ($pesan): ?>
      <div class="alert alert-<?= $pesan_type ?> alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($pesan) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header bg-white py-3 rounded-top-4">
        <span class="fw-semibold">Form Edit Tarif</span>
      </div>
      <div class="card-body p-4">
        <form method="POST">
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label fw-semibold">Jenis Kendaraan</label>
              <select name="jenis_kendaraan" class="form-select" required>
                <?php
                $icon_map = [
                  'motor'   => '🏍️',
                  'mobil'   => '🚗',
                  'truk'    => '🚛',
                  'bus'     => '🚌',
                  'sepeda'  => '🚲',
                  'lainnya' => '🚘',
                ];
                foreach ($jenis_list as $j):
                  $ic = $icon_map[$j] ?? '🚘';
                ?>
                <option value="<?= $j ?>" <?= strtolower($data['jenis_kendaraan']) === $j ? 'selected' : '' ?>>
                  <?= $ic ?> <?= ucfirst($j) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Tarif Per Jam (Rp)</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="tarif_per_jam" class="form-control"
                       value="<?= intval($data['tarif_per_jam']) ?>" min="0" step="500" required>
              </div>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
              <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Batal
              </a>
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-save me-1"></i> Simpan Perubahan
              </button>
            </div>

          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<?php include '../template/footer.php'; ?>