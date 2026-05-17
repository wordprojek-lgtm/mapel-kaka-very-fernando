<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../config/log.php';

$error = [];

// ── Proses Simpan ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    $plat_nomor   = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['plat_nomor'])));
    $id_tarif     = intval($_POST['id_tarif'] ?? 0);
    $id_area      = intval($_POST['id_area']  ?? 0);
    $id_user      = $_SESSION['id_user'] ?? null;
    $waktu_masuk  = date('Y-m-d H:i:s');

    // --- Validasi ---
    if (empty($plat_nomor)) $error[] = "Nomor plat wajib diisi.";
    if ($id_tarif <= 0)     $error[] = "Jenis kendaraan / tarif wajib dipilih.";
    if ($id_area  <= 0)     $error[] = "Area parkir wajib dipilih.";

    // Cek apakah plat_nomor sedang parkir (status masuk)
    if (empty($error)) {
        $cek = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT p.id_parkir FROM tb_transaksi p
             JOIN tb_kendaraan k ON p.id_kendaraan = k.id_kendaraan
             WHERE k.plat_nomor = '$plat_nomor' AND p.status = 'masuk'
             LIMIT 1"
        ));
        if ($cek) {
            $error[] = "Kendaraan dengan plat <strong>$plat_nomor</strong> sedang dalam parkir. Proses keluar dulu.";
        }
    }

    if (empty($error)) {
        // Ambil / buat id_kendaraan
        $kendaraan = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor' LIMIT 1"
        ));

        if ($kendaraan) {
            $id_kendaraan = $kendaraan['id_kendaraan'];
        } else {
            // Ambil jenis_kendaraan dari tarif yang dipilih
            $tarif_data = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT jenis_kendaraan FROM tb_tarif WHERE id_tarif = $id_tarif"
            ));
            $jenis = mysqli_real_escape_string($conn, $tarif_data['jenis_kendaraan'] ?? 'lainnya');

            mysqli_query($conn,
                "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan) VALUES ('$plat_nomor', '$jenis')"
            );
            $id_kendaraan = mysqli_insert_id($conn);
        }

        // Insert ke tb_transaksi
        $stmt = mysqli_prepare($conn,
            "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area)
             VALUES (?, ?, ?, 'masuk', ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "issii", $id_kendaraan, $waktu_masuk, $id_tarif, $id_user, $id_area);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            logAktivitas($conn, "Kendaraan masuk: $plat_nomor");
            header("Location: index.php?pesan=Kendaraan $plat_nomor berhasil dicatat masuk&type=success");
            exit;
        } else {
            $error[] = "Gagal menyimpan: " . mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

// ── Ambil data untuk form ──────────────────────────────────────────────────────
$tarif_list = mysqli_query($conn, "SELECT * FROM tb_tarif ORDER BY jenis_kendaraan");
$area_list  = mysqli_query($conn, "SELECT * FROM tb_area_parkir ORDER BY nama_area");

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';
?>

<div class="content-wrapper">
<div class="container-fluid py-4">
<div class="row justify-content-center">
<div class="col-lg-8">

  <!-- BREADCRUMB -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Transaksi Parkir</a></li>
      <li class="breadcrumb-item active">Parkir Masuk</li>
    </ol>
  </nav>

  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-header rounded-top-4 py-3"
         style="background:linear-gradient(135deg,#1a73e8,#0d47a1);color:white">
      <h5 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Form Parkir Masuk</h5>
    </div>

    <div class="card-body p-4">

      <!-- ERROR -->
      <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($error as $e): ?><li><?= $e ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <form method="POST" action="masuk.php">

        <div class="row g-3">

          <!-- No. Plat -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nomor Plat Kendaraan <span class="text-danger">*</span></label>
            <input type="text" name="plat_nomor" class="form-control text-uppercase fw-bold"
                   placeholder="Contoh: B 1234 ABC"
                   value="<?= htmlspecialchars($_POST['plat_nomor'] ?? '') ?>"
                   required autofocus>
            <div class="form-text">Huruf otomatis menjadi kapital.</div>
          </div>

          <!-- Area Parkir -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Area Parkir <span class="text-danger">*</span></label>
            <select name="id_area" class="form-select" required>
              <option value="">-- Pilih Area --</option>
              <?php while ($a = mysqli_fetch_assoc($area_list)): ?>
              <option value="<?= $a['id_area'] ?>"
                <?= (isset($_POST['id_area']) && $_POST['id_area'] == $a['id_area']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['nama_area']) ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Tarif / Jenis Kendaraan -->
          <div class="col-12">
            <label class="form-label fw-semibold">Jenis Kendaraan & Tarif <span class="text-danger">*</span></label>
            <div class="row g-2">
              <?php
              $icon_map = ['motor'=>'🏍️','mobil'=>'🚗','truk'=>'🚛','bus'=>'🚌','sepeda'=>'🚲','lainnya'=>'🚘'];
              mysqli_data_seek($tarif_list, 0);
              while ($t = mysqli_fetch_assoc($tarif_list)):
                $ic      = $icon_map[strtolower($t['jenis_kendaraan'])] ?? '🚘';
                $checked = (isset($_POST['id_tarif']) && $_POST['id_tarif'] == $t['id_tarif']) ? 'checked' : '';
              ?>
              <div class="col-6 col-md-4 col-lg-3">
                <input type="radio" class="btn-check" name="id_tarif"
                       id="tarif_<?= $t['id_tarif'] ?>" value="<?= $t['id_tarif'] ?>"
                       required <?= $checked ?>>
                <label class="btn btn-outline-primary w-100 py-3 text-center"
                       for="tarif_<?= $t['id_tarif'] ?>">
                  <span style="font-size:1.8rem"><?= $ic ?></span><br>
                  <strong><?= ucfirst(htmlspecialchars($t['jenis_kendaraan'])) ?></strong><br>
                  <small>Rp <?= number_format($t['tarif_per_jam'], 0, ',', '.') ?>/jam</small>
                </label>
              </div>
              <?php endwhile; ?>
            </div>
          </div>

          <!-- Waktu masuk (info saja) -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Waktu Masuk</label>
            <input type="text" class="form-control" value="<?= date('d/m/Y H:i:s') ?>" readonly>
            <div class="form-text">Dicatat otomatis saat simpan.</div>
          </div>

        </div><!-- /row -->

        <hr class="my-4">

        <div class="d-flex gap-2">
          <button type="submit" name="simpan" class="btn btn-success px-4">
            <i class="fas fa-save me-2"></i> Simpan & Catat Masuk
          </button>
          <a href="index.php" class="btn btn-outline-secondary px-4">
            <i class="fas fa-arrow-left me-2"></i> Batal
          </a>
        </div>

      </form>
    </div>
  </div>

</div>
</div>
</div>
</div>

<?php include '../template/footer.php'; ?>