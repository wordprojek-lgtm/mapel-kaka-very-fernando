<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();

include '../template/header.php';
include '../template/sidebar.php';

// Ambil daftar user untuk dropdown
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

    // Cek duplikat plat nomor
    if (!empty($plat_nomor)) {
        $cek = mysqli_query($conn, "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor'");
        if (mysqli_num_rows($cek) > 0) {
            $error[] = "Plat nomor <strong>$plat_nomor</strong> sudah terdaftar!";
        }
    }

    if (empty($error)) {
        $id_user_val = $id_user > 0 ? $id_user : "NULL";
        $sql = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik, id_user)
                VALUES ('$plat_nomor', '$jenis_kendaraan', '$warna', '$pemilik', $id_user_val)";

        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?pesan=Kendaraan berhasil ditambahkan!&type=success");
            exit;
        } else {
            $error[] = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
}

function val($key, $default = '') {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : $default;
}
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">
    <div class="row justify-content-center">
      <div class="col-lg-7">

        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Kendaraan</a></li>
            <li class="breadcrumb-item active">Tambah Kendaraan</li>
          </ol>
        </nav>

        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-header rounded-top-4 py-3"
               style="background:linear-gradient(135deg,#1a73e8,#0d47a1);color:white">
            <h5 class="mb-0"><i class="fas fa-car me-2"></i> Tambah Data Kendaraan</h5>
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

            <form method="POST" action="tambah.php">

              <div class="mb-3">
                <label class="form-label fw-semibold">Plat Nomor <span class="text-danger">*</span></label>
                <input type="text" name="plat_nomor" class="form-control text-uppercase"
                       placeholder="Contoh: KT 1234 AB"
                       value="<?= val('plat_nomor') ?>" required>
                <div class="form-text">Plat nomor harus unik, tidak boleh sama.</div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Jenis Kendaraan <span class="text-danger">*</span></label>
                <select name="jenis_kendaraan" class="form-select" required>
                  <option value="">-- Pilih Jenis --</option>
                  <?php foreach (['Motor','Mobil','Truk','Bus','Sepeda'] as $j): ?>
                  <option value="<?= $j ?>" <?= val('jenis_kendaraan') === $j ? 'selected' : '' ?>><?= $j ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Warna</label>
                <input type="text" name="warna" class="form-control"
                       placeholder="Contoh: Hitam, Putih, Merah"
                       value="<?= val('warna') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Pemilik <span class="text-danger">*</span></label>
                <input type="text" name="pemilik" class="form-control"
                       placeholder="Nama lengkap pemilik kendaraan"
                       value="<?= val('pemilik') ?>" required>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold">User (Opsional)</label>
                <select name="id_user" class="form-select">
                  <option value="0">-- Pilih User --</option>
                  <?php while ($u = mysqli_fetch_assoc($user_query)): ?>
                  <option value="<?= $u['id_user'] ?>"
                    <?= val('id_user') == $u['id_user'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['nama_lengkap']) ?>
                  </option>
                  <?php endwhile; ?>
                </select>
              </div>

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
          </div>

          <div class="card-footer bg-white text-muted small text-end rounded-bottom-4">
            Aplikasi Parkir &copy; <?= date('Y') ?>
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