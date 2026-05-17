<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

// Pesan notifikasi dari redirect
$pesan      = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$pesan_type = isset($_GET['type'])  ? $_GET['type']  : 'info';

// Pencarian
$cari  = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';
$where = $cari ? "WHERE nama_area LIKE '%$cari%'" : '';

// Pagination
$per_halaman   = 10;
$halaman       = isset($_GET['halaman']) ? intval($_GET['halaman']) : 1;
$offset        = ($halaman - 1) * $per_halaman;

$total_query   = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_area_parkir $where");
$total_row     = mysqli_fetch_assoc($total_query)['total'];
$total_halaman = ceil($total_row / $per_halaman);

$query = mysqli_query($conn, "
    SELECT *
    FROM tb_area_parkir
    $where
    ORDER BY id_area DESC
    LIMIT $per_halaman OFFSET $offset
");

// Statistik
$stat_total     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM tb_area_parkir"))['jml'];
$stat_kapasitas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(kapasitas) as jml FROM tb_area_parkir"))['jml'] ?? 0;
$stat_terisi    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(terisi) as jml FROM tb_area_parkir"))['jml'] ?? 0;
$stat_sisa      = $stat_kapasitas - $stat_terisi;

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="fw-bold mb-0">
          <i class="bi bi-map me-2 text-primary"></i>Manajemen Area Parkir
        </h4>
        <small class="text-muted">Kelola data area dan lokasi parkir</small>
      </div>
      <a href="tambah.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Area
      </a>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($pesan): ?>
      <div class="alert alert-<?= htmlspecialchars($pesan_type) ?> alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>
        <?= htmlspecialchars($pesan) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center py-3">
          <div class="fs-2">🏢</div>
          <div class="fw-bold fs-4"><?= $stat_total ?></div>
          <small class="text-muted">Total Area</small>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center py-3">
          <div class="fs-2">🅿️</div>
          <div class="fw-bold fs-4 text-primary"><?= number_format($stat_kapasitas) ?></div>
          <small class="text-muted">Total Kapasitas</small>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center py-3">
          <div class="fs-2">🚗</div>
          <div class="fw-bold fs-4 text-warning"><?= number_format($stat_terisi) ?></div>
          <small class="text-muted">Total Terisi</small>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center py-3">
          <div class="fs-2">✅</div>
          <div class="fw-bold fs-4 text-success"><?= number_format($stat_sisa) ?></div>
          <small class="text-muted">Sisa Slot</small>
        </div>
      </div>
    </div>

    <!-- TABEL -->
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4">
        <span class="fw-semibold">
          <i class="bi bi-list-ul me-1 text-primary"></i> Daftar Area Parkir
        </span>
        <form method="GET" class="d-flex gap-2">
          <input type="text" name="cari" class="form-control form-control-sm"
                 placeholder="Cari nama area..."
                 value="<?= htmlspecialchars($cari) ?>" style="width:260px">
          <button class="btn btn-sm btn-outline-primary">
            <i class="bi bi-search"></i>
          </button>
          <?php if ($cari): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-x-lg"></i>
            </a>
          <?php endif; ?>
        </form>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4" style="width:50px">No</th>
                <th>Nama Area</th>
                <th>Kapasitas</th>
                <th>Terisi</th>
                <th>Sisa Slot</th>
                <th class="text-center" style="width:130px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = $offset + 1;
              if ($query && mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)):
                  $terisi    = intval($row['terisi']);
                  $kapasitas = intval($row['kapasitas']);
                  $sisa      = $kapasitas - $terisi;
                  $persen    = $kapasitas > 0 ? round(($terisi / $kapasitas) * 100) : 0;
                  $bar_color = $persen >= 90 ? 'danger' : ($persen >= 60 ? 'warning' : 'success');
              ?>
              <tr>
                <td class="ps-4"><?= $no++ ?></td>
                <td>
                  <span class="fw-semibold">
                    <?= htmlspecialchars($row['nama_area']) ?>
                  </span>
                </td>
                <td><?= number_format($kapasitas) ?> slot</td>
                <td>
                  <?= $terisi ?> slot
                  <div class="progress mt-1" style="height:5px;width:80px">
                    <div class="progress-bar bg-<?= $bar_color ?>"
                         style="width:<?= $persen ?>%"></div>
                  </div>
                  <small class="text-muted"><?= $persen ?>%</small>
                </td>
                <td>
                  <span class="fw-bold text-<?= $sisa > 0 ? 'success' : 'danger' ?>">
                    <?= $sisa ?> slot
                  </span>
                </td>
                <td class="text-center">
                  <!-- ✅ FIXED: id_area (bukan d_area) -->
                  <a href="edit.php?id=<?= $row['id_area'] ?>"
                     class="btn btn-sm btn-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="hapus.php?id=<?= $row['id_area'] ?>"
                     class="btn btn-sm btn-danger" title="Hapus"
                     onclick="return confirm('Yakin ingin menghapus area <?= htmlspecialchars($row['nama_area']) ?>?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endwhile; else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-5">
                  <i class="bi bi-map fs-1 d-block mb-2 opacity-25"></i>
                  <?= $cari
                    ? "Tidak ada area dengan kata kunci \"" . htmlspecialchars($cari) . "\"."
                    : "Belum ada data area parkir." ?>
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- PAGINATION -->
      <?php if ($total_halaman > 1): ?>
      <div class="card-footer bg-white d-flex justify-content-between align-items-center rounded-bottom-4">
        <small class="text-muted">
          Menampilkan
          <strong><?= $offset + 1 ?></strong>–<strong><?= min($offset + $per_halaman, $total_row) ?></strong>
          dari <strong><?= $total_row ?></strong> data
        </small>
        <nav>
          <ul class="pagination pagination-sm mb-0">

            <!-- Tombol Prev -->
            <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
              <a class="page-link"
                 href="?halaman=<?= $halaman - 1 ?>&cari=<?= urlencode($cari) ?>">
                <i class="bi bi-chevron-left"></i>
              </a>
            </li>

            <!-- Nomor Halaman -->
            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
            <li class="page-item <?= $i == $halaman ? 'active' : '' ?>">
              <a class="page-link"
                 href="?halaman=<?= $i ?>&cari=<?= urlencode($cari) ?>">
                <?= $i ?>
              </a>
            </li>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <li class="page-item <?= $halaman >= $total_halaman ? 'disabled' : '' ?>">
              <a class="page-link"
                 href="?halaman=<?= $halaman + 1 ?>&cari=<?= urlencode($cari) ?>">
                <i class="bi bi-chevron-right"></i>
              </a>
            </li>

          </ul>
        </nav>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php include '../template/footer.php'; ?>