<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';

// Filter tanggal
$tgl_awal     = isset($_GET['tgl_awal'])        ? $_GET['tgl_awal']        : date('Y-m-01');
$tgl_akhir    = isset($_GET['tgl_akhir'])       ? $_GET['tgl_akhir']       : date('Y-m-d');
$filter_jenis = isset($_GET['jenis_kendaraan']) ? mysqli_real_escape_string($conn, $_GET['jenis_kendaraan']) : '';

// Bangun WHERE — jenis_kendaraan dari tb_kendaraan (alias k)
$where = "WHERE t.status='keluar' AND DATE(t.waktu_keluar) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
if ($filter_jenis) {
    $where .= " AND k.jenis_kendaraan = '$filter_jenis'";
}

// Base JOIN
$join = "FROM tb_transaksi t LEFT JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan";

// Pagination
$per_halaman = 10;
$halaman     = isset($_GET['halaman']) ? intval($_GET['halaman']) : 1;
$offset      = ($halaman - 1) * $per_halaman;

$total_query   = mysqli_query($conn, "SELECT COUNT(*) as total $join $where");
$total_row     = $total_query ? mysqli_fetch_assoc($total_query)['total'] : 0;
$total_halaman = ceil($total_row / $per_halaman);

$query = mysqli_query($conn, "SELECT t.*, k.plat_nomor, k.jenis_kendaraan $join $where ORDER BY t.waktu_keluar DESC LIMIT $per_halaman OFFSET $offset");

// Hitung total pendapatan (semua data, bukan hanya halaman ini)
$total_query2 = mysqli_query($conn, "SELECT SUM(t.biaya_total) as grand_total $join $where");
$grand_total  = $total_query2 ? mysqli_fetch_assoc($total_query2)['grand_total'] : 0;

// Ambil daftar jenis kendaraan untuk filter (dari tb_kendaraan)
$jenis_query = mysqli_query($conn, "SELECT DISTINCT jenis_kendaraan FROM tb_kendaraan ORDER BY jenis_kendaraan");

// Bangun query string untuk export (tanpa halaman)
$export_params = http_build_query([
    'tgl_awal'        => $tgl_awal,
    'tgl_akhir'       => $tgl_akhir,
    'jenis_kendaraan' => $filter_jenis,
]);
?>

<div class="content-wrapper">
  <div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Laporan Transaksi Parkir</h4>
        <small class="text-muted">Rekap pendapatan parkir kendaraan</small>
      </div>
      <div class="d-flex gap-2">
        <a href="export_excel.php?<?= $export_params ?>" class="btn btn-success btn-sm">
          <i class="fas fa-file-excel me-1"></i> Export Excel
        </a>
        <a href="export_pdf.php?<?= $export_params ?>" class="btn btn-danger btn-sm" target="_blank">
          <i class="fas fa-file-pdf me-1"></i> Export PDF
        </a>
      </div>
    </div>

    <!-- FILTER -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
      <div class="card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label fw-semibold small mb-1">Tanggal Awal</label>
            <input type="date" name="tgl_awal" class="form-control form-control-sm" value="<?= $tgl_awal ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small mb-1">Tanggal Akhir</label>
            <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= $tgl_akhir ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small mb-1">Jenis Kendaraan</label>
            <select name="jenis_kendaraan" class="form-select form-select-sm">
              <option value="">-- Semua --</option>
              <?php while ($j = mysqli_fetch_assoc($jenis_query)): ?>
                <option value="<?= $j['jenis_kendaraan'] ?>" <?= $filter_jenis === $j['jenis_kendaraan'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($j['jenis_kendaraan']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm px-3">
              <i class="fas fa-search me-1"></i> Filter
            </button>
            <a href="index.php" class="btn btn-outline-secondary btn-sm px-3">
              <i class="fas fa-times me-1"></i> Reset
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- SUMMARY CARD -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 rounded-4 shadow-sm bg-primary text-white">
          <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="opacity-75">Total Transaksi</small>
                <h4 class="fw-bold mb-0"><?= number_format($total_row) ?></h4>
              </div>
              <i class="fas fa-receipt fa-2x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 rounded-4 shadow-sm bg-success text-white">
          <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="opacity-75">Total Pendapatan</small>
                <h4 class="fw-bold mb-0">Rp <?= number_format($grand_total, 0, ',', '.') ?></h4>
              </div>
              <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 rounded-4 shadow-sm bg-info text-white">
          <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="opacity-75">Periode</small>
                <h5 class="fw-bold mb-0"><?= date('d/m/Y', strtotime($tgl_awal)) ?> – <?= date('d/m/Y', strtotime($tgl_akhir)) ?></h5>
              </div>
              <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TABEL -->
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header bg-white py-3 rounded-top-4">
        <span class="fw-semibold">Detail Transaksi</span>
        <small class="text-muted ms-2"><?= $total_row ?> data ditemukan</small>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th class="ps-4" style="width:45px">No</th>
                <th>ID</th>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Durasi</th>
                <th>Total Biaya</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = $offset + 1;
              if ($query && mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)):
                  $awal   = new DateTime($row['waktu_masuk']);
                  $akhir  = new DateTime($row['waktu_keluar']);
                  $diff   = $awal->diff($akhir);
                  $durasi = $diff->h + ($diff->days * 24);
                  if ($diff->i > 0 || $diff->s > 0) $durasi++;
              ?>
              <tr>
                <td class="ps-4"><?= $no++ ?></td>
                <td><span class="badge bg-secondary">#<?= $row['id_parkir'] ?></span></td>
                <td><span class="badge bg-dark fs-6"><?= strtoupper($row['plat_nomor']) ?></span></td>
                <td>
                  <?php
                  $icon = ['motor'=>'🏍️','mobil'=>'🚗','truk'=>'🚛','bus'=>'🚌','sepeda'=>'🚲'];
                  $ic   = $icon[strtolower($row['jenis_kendaraan'] ?? '')] ?? '🚘';
                  echo $ic . ' ' . htmlspecialchars($row['jenis_kendaraan'] ?? '-');
                  ?>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['waktu_keluar'])) ?></td>
                <td class="text-center"><span class="badge bg-info text-dark"><?= $durasi ?> Jam</span></td>
                <td class="fw-bold text-success">Rp <?= number_format($row['biaya_total'], 0, ',', '.') ?></td>
                <td><span class="badge bg-success">Keluar</span></td>
              </tr>
              <?php endwhile; else: ?>
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                  Belum ada data transaksi untuk periode ini.
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
            <?php if ($total_row > 0): ?>
            <tfoot>
              <tr class="table-light fw-bold">
                <td colspan="7" class="text-end pe-3">TOTAL PENDAPATAN :</td>
                <td class="text-success">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
                <td></td>
              </tr>
            </tfoot>
            <?php endif; ?>
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
              <a class="page-link" href="?halaman=<?= $i ?>&tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&jenis_kendaraan=<?= urlencode($filter_jenis) ?>"><?= $i ?></a>
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