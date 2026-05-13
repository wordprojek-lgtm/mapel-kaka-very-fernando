<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();
include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';


// ============================================================
// PROSES HAPUS (digabung langsung di index.php)
// ============================================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $hapus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($hapus_id > 0) {
        $sql_hapus = mysqli_query($conn, "DELETE FROM tb_kendaraan WHERE id_kendaraan = $hapus_id");
        if ($sql_hapus) {
            header("Location: index.php?pesan=Data kendaraan berhasil dihapus!&type=success");
        } else {
            header("Location: index.php?pesan=Gagal menghapus data!&type=danger");
        }
        exit;
    } else {
        header("Location: index.php?pesan=ID tidak valid!&type=danger");
        exit;
    }
}
// ============================================================

$pesan      = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$pesan_type = isset($_GET['type'])  ? $_GET['type']  : '';

// Pencarian
$cari  = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';
$where = $cari ? "WHERE plat_nomor LIKE '%$cari%' 
                     OR pemilik LIKE '%$cari%' 
                     OR jenis_kendaraan LIKE '%$cari%'
                     OR warna LIKE '%$cari%'" : '';

// Pagination
$per_halaman   = 10;
$halaman       = isset($_GET['halaman']) ? intval($_GET['halaman']) : 1;
$offset        = ($halaman - 1) * $per_halaman;

$total_query   = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_kendaraan $where");
$total_row     = mysqli_fetch_assoc($total_query)['total'];
$total_halaman = ceil($total_row / $per_halaman);

$query = mysqli_query($conn, "
    SELECT k.*, u.nama_lengkap
    FROM tb_kendaraan k
    LEFT JOIN tb_user u ON k.id_user = u.id_user
    $where
    ORDER BY k.id_kendaraan DESC
    LIMIT $per_halaman OFFSET $offset
");

// Statistik
$stat_semua = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM tb_kendaraan"))['jml'];
$stat_motor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM tb_kendaraan WHERE jenis_kendaraan='Motor'"))['jml'];
$stat_mobil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM tb_kendaraan WHERE jenis_kendaraan='Mobil'"))['jml'];
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="fw-bold mb-0"><i class="fas fa-car me-2 text-primary"></i>Manajemen Kendaraan</h4>
        <small class="text-muted">Kelola data kendaraan terdaftar</small>
      </div>
      <a href="tambah.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Tambah Kendaraan
      </a>
    </div>

    <?php if ($pesan): ?>
      <div class="alert alert-<?= htmlspecialchars($pesan_type) ?> alert-dismissible fade show">
        <?= htmlspecialchars($pesan) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center py-3">
          <div class="fs-3">🚘</div>
          <div class="fw-bold fs-4"><?= $stat_semua ?></div>
          <small class="text-muted">Total Kendaraan</small>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center py-3">
          <div class="fs-3">🏍️</div>
          <div class="fw-bold fs-4 text-success"><?= $stat_motor ?></div>
          <small class="text-muted">Motor</small>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center py-3">
          <div class="fs-3">🚗</div>
          <div class="fw-bold fs-4 text-primary"><?= $stat_mobil ?></div>
          <small class="text-muted">Mobil</small>
        </div>
      </div>
    </div>

    <!-- TABEL -->
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4">
        <span class="fw-semibold">Daftar Kendaraan</span>
        <form method="GET" class="d-flex gap-2">
          <input type="text" name="cari" class="form-control form-control-sm"
                 placeholder="Cari plat, pemilik, jenis..."
                 value="<?= htmlspecialchars($cari) ?>" style="width:260px">
          <button class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
          <?php if ($cari): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4" style="width:50px">No</th>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Warna</th>
                <th>Pemilik</th>
                <th>User</th>
                <th class="text-center" style="width:120px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = $offset + 1;
              if ($query && mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)):
                  $icon = ['Motor'=>'🏍️','Mobil'=>'🚗','Truk'=>'🚛','Bus'=>'🚌','Sepeda'=>'🚲'];
                  $ic   = $icon[$row['jenis_kendaraan']] ?? '🚘';
              ?>
              <tr>
                <td class="ps-4"><?= $no++ ?></td>
                <td><span class="fw-bold text-primary"><?= htmlspecialchars($row['plat_nomor'] ?? '-') ?></span></td>
                <td><?= $ic ?> <?= htmlspecialchars($row['jenis_kendaraan'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['warna'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['pemilik'] ?? '-') ?></td>
                <td><small class="text-muted"><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></small></td>
                <td class="text-center">
                  <!-- Tombol Edit -->
                  <a href="edit.php?id=<?= $row['id_kendaraan'] ?>"
                     class="btn btn-sm btn-warning text-white" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <!-- Tombol Hapus (aksi digabung di index.php) -->
                  <a href="index.php?aksi=hapus&id=<?= $row['id_kendaraan'] ?>"
                     class="btn btn-sm btn-danger" title="Hapus"
                     onclick="return confirm('Yakin ingin menghapus kendaraan <?= htmlspecialchars($row['plat_nomor'], ENT_QUOTES) ?>?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endwhile; else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="fas fa-car fa-3x mb-3 d-block opacity-25"></i>
                  <?= $cari ? "Tidak ada kendaraan dengan kata kunci \"" . htmlspecialchars($cari) . "\"." : "Belum ada data kendaraan." ?>
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php if ($total_halaman > 1): ?>
      <div class="card-footer bg-white d-flex justify-content-between align-items-center rounded-bottom-4">
        <small class="text-muted">
          Menampilkan <?= $offset + 1 ?>–<?= min($offset + $per_halaman, $total_row) ?> dari <?= $total_row ?> data
        </small>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
            <li class="page-item <?= $i == $halaman ? 'active' : '' ?>">
              <a class="page-link" href="?halaman=<?= $i ?>&cari=<?= urlencode($cari) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php include '../template/footer.php'; ?>