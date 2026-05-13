<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';

// Pesan dari redirect
$pesan      = isset($_GET['pesan'])  ? $_GET['pesan']  : '';
$pesan_type = isset($_GET['type'])   ? $_GET['type']   : '';

// Pencarian
$cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';
$where = $cari ? "WHERE jenis_kendaraan LIKE '%$cari%'" : '';

// Pagination
$per_halaman = 10;
$halaman     = isset($_GET['halaman']) ? intval($_GET['halaman']) : 1;
$offset      = ($halaman - 1) * $per_halaman;

$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_tarif $where");
$total_row   = $total_query ? mysqli_fetch_assoc($total_query)['total'] : 0;
$total_halaman = ceil($total_row / $per_halaman);

$query = mysqli_query($conn, "SELECT * FROM tb_tarif $where ORDER BY id_tarif DESC LIMIT $per_halaman OFFSET $offset");
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="fw-bold mb-0"><i class="fas fa-tags me-2 text-primary"></i>Manajemen Tarif</h4>
        <small class="text-muted">Kelola tarif parkir kendaraan</small>
      </div>
      <a href="tambah.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Tambah Tarif
      </a>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($pesan): ?>
      <div class="alert alert-<?= $pesan_type ?> alert-dismissible fade show">
        <i class="fas fa-<?= $pesan_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
        <?= htmlspecialchars($pesan) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- CARD TABEL -->
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4">
        <span class="fw-semibold">Daftar Tarif Parkir</span>
        <!-- Form Pencarian -->
        <form method="GET" class="d-flex gap-2">
          <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari jenis kendaraan..." value="<?= htmlspecialchars($cari) ?>" style="width:220px">
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
                <th>Jenis Kendaraan</th>
                <th>Tarif Per Jam (Rp)</th>
                <th class="text-center" style="width:140px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = $offset + 1;
              if ($query && mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)):
              ?>
              <tr>
                <td class="ps-4"><?= $no++ ?></td>
                <td>
                  <?php
                  $icon = ['motor'=>'🏍️','mobil'=>'🚗','truk'=>'🚛','bus'=>'🚌','sepeda'=>'🚲','lainnya'=>'🚘'];
                  $ic = $icon[strtolower($row['jenis_kendaraan'])] ?? '🚘';
                  ?>
                  <?= $ic ?> <?= htmlspecialchars($row['jenis_kendaraan']) ?>
                </td>
                <td>Rp <?= number_format($row['tarif_per_jam'], 0, ',', '.') ?></td>
                <td class="text-center">
                  <a href="edit.php?id=<?= $row['id_tarif'] ?>" class="btn btn-sm btn-warning" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="hapus.php?id=<?= $row['id_tarif'] ?>"
                     class="btn btn-sm btn-danger"
                     title="Hapus"
                     onclick="return confirm('Yakin hapus tarif ini?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endwhile; else: ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                  Belum ada data tarif.
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
        <small class="text-muted">Menampilkan <?= $offset+1 ?>–<?= min($offset+$per_halaman, $total_row) ?> dari <?= $total_row ?> data</small>
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

    </div><!-- /card -->
  </div>
</div>

<?php include '../template/footer.php'; ?>