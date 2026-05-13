<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';

// Pesan notifikasi dari redirect
$pesan      = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$pesan_type = isset($_GET['type'])  ? $_GET['type']  : 'info';

// Pencarian
$cari  = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';
$where = $cari
    ? "WHERE k.plat_nomor LIKE '%$cari%' OR k.jenis_kendaraan LIKE '%$cari%'"
    : '';

// Pagination
$per_halaman   = 10;
$halaman       = isset($_GET['halaman']) ? max(1, intval($_GET['halaman'])) : 1;
$offset        = ($halaman - 1) * $per_halaman;

// Query total untuk pagination
$q_total = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM tb_transaksi p
     JOIN tb_kendaraan k ON p.id_kendaraan = k.id_kendaraan
     $where"
);
if (!$q_total) {
    die("<div class='alert alert-danger m-4'><strong>Error database:</strong> "
        . mysqli_error($conn)
        . "<br><small>Pastikan tabel <code>tb_transaksi</code> dan <code>tb_kendaraan</code> sudah ada.</small></div>");
}
$total_row     = mysqli_fetch_assoc($q_total)['total'] ?? 0;
$total_halaman = $total_row > 0 ? ceil($total_row / $per_halaman) : 1;

// Query utama
$sql_utama = "SELECT p.*, k.plat_nomor, k.jenis_kendaraan, t.tarif_per_jam,
                     a.nama_area, u.nama_lengkap
              FROM tb_transaksi p
              JOIN tb_kendaraan      k ON p.id_kendaraan = k.id_kendaraan
              JOIN tb_tarif          t ON p.id_tarif     = t.id_tarif
              LEFT JOIN tb_area_parkir a ON p.id_area    = a.id_area
              LEFT JOIN tb_user        u ON p.id_user    = u.id_user
              $where
              ORDER BY p.id_parkir DESC
              LIMIT $per_halaman OFFSET $offset";

$result = mysqli_query($conn, $sql_utama);
if (!$result) {
    die("<div class='alert alert-danger m-4'><strong>Error query:</strong> "
        . mysqli_error($conn) . "</div>");
}
?>

<div class="content-wrapper">
<div class="container-fluid py-4">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-0"><i class="fas fa-car me-2 text-primary"></i>Transaksi Parkir</h4>
      <small class="text-muted">Kelola data kendaraan masuk & keluar</small>
    </div>
    <a href="masuk.php" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Parkir Masuk
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
      <span class="fw-semibold">Daftar Transaksi</span>
      <form method="GET" class="d-flex gap-2">
        <input type="text" name="cari" class="form-control form-control-sm"
               placeholder="Cari no. plat / jenis..." value="<?= htmlspecialchars($cari) ?>" style="width:220px">
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
              <th>No. Plat</th>
              <th>Jenis</th>
              <th>Area</th>
              <th>Waktu Masuk</th>
              <th>Waktu Keluar</th>
              <th>Durasi</th>
              <th>Biaya</th>
              <th>Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $icon_map = ['motor'=>'🏍️','mobil'=>'🚗','truk'=>'🚛','bus'=>'🚌','sepeda'=>'🚲','lainnya'=>'🚘'];
            $no = $offset + 1;
            if ($result && mysqli_num_rows($result) > 0):
              while ($row = mysqli_fetch_assoc($result)):
                $ic = $icon_map[strtolower($row['jenis_kendaraan'])] ?? '🚘';
            ?>
            <tr>
              <td class="ps-4"><?= $no++ ?></td>
              <td><strong><?= htmlspecialchars(strtoupper($row['plat_nomor'])) ?></strong></td>
              <td><?= $ic ?> <?= htmlspecialchars($row['jenis_kendaraan']) ?></td>
              <td><?= htmlspecialchars($row['nama_area'] ?? '-') ?></td>
              <td><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
              <td><?= $row['waktu_keluar'] ? date('d/m/Y H:i', strtotime($row['waktu_keluar'])) : '<span class="text-muted">-</span>' ?></td>
              <td>
                <?php if ($row['durasi_jam']): ?>
                  <?= $row['durasi_jam'] ?> jam
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($row['biaya_total']): ?>
                  Rp <?= number_format($row['biaya_total'], 0, ',', '.') ?>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($row['status'] === 'masuk'): ?>
                  <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Parkir</span>
                <?php else: ?>
                  <span class="badge bg-success"><i class="fas fa-check me-1"></i>Selesai</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if ($row['status'] === 'masuk'): ?>
                  <a href="keluar.php?id=<?= $row['id_parkir'] ?>"
                     class="btn btn-sm btn-danger" title="Proses Keluar">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                  </a>
                <?php else: ?>
                  <a href="struk.php?id=<?= $row['id_parkir'] ?>" target="_blank"
                     class="btn btn-sm btn-info text-white" title="Cetak Struk">
                    <i class="fas fa-print"></i> Struk
                  </a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
              <td colspan="10" class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada data transaksi.
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