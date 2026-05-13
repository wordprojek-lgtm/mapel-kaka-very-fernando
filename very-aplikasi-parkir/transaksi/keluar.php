<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';

$id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = null;

if ($id > 0) {
    $stmt = mysqli_prepare($conn,
        "SELECT p.*, k.no_plat, k.jenis_kendaraan,
                t.tarif_per_jam, a.nama_area
         FROM tb_area_parkir
         JOIN tb_kendaraan k ON p.id_kendaraan = k.id_kendaraan
         JOIN tb_tarif     t ON p.id_tarif     = t.id_tarif
         LEFT JOIN tb_area_parkir ON p.id_area      = a.id_area
         WHERE p.id_parkir = ? AND p.status = 'masuk'
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

// Hitung estimasi biaya real-time (belum final, hanya tampilan)
$estimasi_biaya = 0;
$estimasi_durasi = 0;
if ($data) {
    $waktu_masuk  = new DateTime($data['waktu_masuk']);
    $waktu_keluar = new DateTime();
    $selisih      = $waktu_masuk->diff($waktu_keluar);
    // Durasi minimal 1 jam, pembulatan ke atas
    $total_menit  = ($selisih->days * 24 * 60) + ($selisih->h * 60) + $selisih->i;
    $estimasi_durasi = max(1, ceil($total_menit / 60));
    $estimasi_biaya  = $estimasi_durasi * $data['tarif_per_jam'];
}
?>

<div class="content-wrapper">
<div class="container-fluid py-4">

  <!-- BREADCRUMB -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Transaksi Parkir</a></li>
      <li class="breadcrumb-item active">Proses Keluar</li>
    </ol>
  </nav>

  <?php if (!$data): ?>
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Data transaksi tidak ditemukan atau kendaraan sudah keluar.
    <a href="index.php" class="btn btn-sm btn-secondary ms-3">Kembali</a>
  </div>

  <?php else: ?>

  <div class="row g-4">

    <!-- Form Konfirmasi -->
    <div class="col-lg-7">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header rounded-top-4 py-3"
             style="background:linear-gradient(135deg,#e53935,#b71c1c);color:white">
          <h5 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i>Konfirmasi Parkir Keluar</h5>
        </div>
        <div class="card-body p-4">

          <form action="proses_keluar.php" method="POST">
            <input type="hidden" name="id_parkir" value="<?= $data['id_parkir'] ?>">

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label fw-semibold">Nomor Plat</label>
                <input type="text" class="form-control fw-bold text-uppercase"
                       value="<?= htmlspecialchars($data['no_plat']) ?>" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Jenis Kendaraan</label>
                <input type="text" class="form-control"
                       value="<?= htmlspecialchars(ucfirst($data['jenis_kendaraan'])) ?>" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Area Parkir</label>
                <input type="text" class="form-control"
                       value="<?= htmlspecialchars($data['nama_area'] ?? '-') ?>" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Tarif per Jam</label>
                <input type="text" class="form-control"
                       value="Rp <?= number_format($data['tarif_per_jam'], 0, ',', '.') ?>" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Waktu Masuk</label>
                <input type="text" class="form-control"
                       value="<?= date('d/m/Y H:i', strtotime($data['waktu_masuk'])) ?>" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Waktu Keluar (Sekarang)</label>
                <input type="text" class="form-control" id="waktu_keluar_display"
                       value="<?= date('d/m/Y H:i:s') ?>" readonly>
              </div>

            </div>

            <hr>

            <div class="d-flex gap-2 mt-3">
              <button type="submit" name="proses_bayar" class="btn btn-danger px-4">
                <i class="fas fa-cash-register me-2"></i> Proses Keluar & Bayar
              </button>
              <a href="index.php" class="btn btn-outline-secondary px-4">
                <i class="fas fa-times me-2"></i> Batal
              </a>
            </div>

          </form>
        </div>
      </div>
    </div>

    <!-- Ringkasan Biaya -->
    <div class="col-lg-5">
      <div class="card shadow-sm border-0 rounded-4 border-warning">
        <div class="card-header bg-warning py-3 rounded-top-4">
          <h5 class="mb-0 fw-bold"><i class="fas fa-calculator me-2"></i>Estimasi Biaya</h5>
        </div>
        <div class="card-body p-4">
          <table class="table table-borderless mb-0">
            <tr>
              <td class="text-muted">Durasi Parkir</td>
              <td class="fw-semibold text-end" id="est_durasi"><?= $estimasi_durasi ?> jam</td>
            </tr>
            <tr>
              <td class="text-muted">Tarif/Jam</td>
              <td class="fw-semibold text-end">Rp <?= number_format($data['tarif_per_jam'], 0, ',', '.') ?></td>
            </tr>
            <tr class="border-top">
              <td class="fw-bold fs-5">Total</td>
              <td class="fw-bold fs-5 text-danger text-end" id="est_biaya">
                Rp <?= number_format($estimasi_biaya, 0, ',', '.') ?>
              </td>
            </tr>
          </table>
          <div class="alert alert-info mt-3 mb-0 py-2 small">
            <i class="fas fa-info-circle me-1"></i>
            Biaya dihitung otomatis. Durasi minimal 1 jam, dibulatkan ke atas.
          </div>
        </div>
      </div>
    </div>

  </div><!-- /row -->
  <?php endif; ?>

</div>
</div>

<script>
// Update jam keluar real-time setiap detik
setInterval(function() {
    const now = new Date();
    const pad = n => String(n).padStart(2,'0');
    const str = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    const el = document.getElementById('waktu_keluar_display');
    if (el) el.value = str;
}, 1000);
</script>

<?php include '../template/footer.php'; ?>