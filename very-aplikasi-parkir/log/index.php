<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Hanya Admin yang bisa melihat log.'); window.location='../dashboard/index.php';</script>";
    exit;
}

include '../template/header.php';
include '../template/sidebar.php';
include '../template/navbar.php';

// FIX 1: Filter & Pagination server-side
$cari        = isset($_GET['cari']) ? mysqli_real_escape_string($conn, trim($_GET['cari'])) : '';
$filter_role = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
$filter_tgl  = isset($_GET['tgl'])  ? mysqli_real_escape_string($conn, $_GET['tgl']) : '';

$where_parts = [];
if ($cari !== '')        $where_parts[] = "(tu.username LIKE '%$cari%' OR tla.aktivitas LIKE '%$cari%')";
if ($filter_role !== '') $where_parts[] = "tu.role = '$filter_role'";
if ($filter_tgl !== '')  $where_parts[] = "DATE(tla.waktu_aktivitas) = '$filter_tgl'";
$where = count($where_parts) > 0 ? 'WHERE ' . implode(' AND ', $where_parts) : '';

$per_hal   = 20;
$halaman   = isset($_GET['halaman']) ? max(1, intval($_GET['halaman'])) : 1;
$offset    = ($halaman - 1) * $per_hal;

$total_q   = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_log_aktivitas tla JOIN tb_user tu ON tla.id_user = tu.id_user $where");
$total     = $total_q ? mysqli_fetch_assoc($total_q)['total'] : 0;
$total_hal = ceil($total / $per_hal);

// FIX 2: Pakai LIMIT agar tidak load semua data sekaligus
$query = mysqli_query($conn,
    "SELECT tla.aktivitas, tla.waktu_aktivitas, tu.username, tu.role
     FROM tb_log_aktivitas tla
     JOIN tb_user tu ON tla.id_user = tu.id_user
     $where
     ORDER BY tla.waktu_aktivitas DESC
     LIMIT $per_hal OFFSET $offset"
);
?>

<!-- FIX 3: Tambahkan content-wrapper agar konsisten dengan halaman lain -->
<div class="content-wrapper">
  <div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header rounded-top-4 py-3 d-flex justify-content-between align-items-center"
           style="background:linear-gradient(135deg,#1e293b,#334155);color:white">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Log Aktivitas Sistem</h5>
        <span class="badge bg-light text-dark"><i class="bi bi-shield-check me-1"></i> Audit Trail</span>
      </div>

      <div class="card-body">

        <!-- FIX 4: Filter via form GET (server-side, bukan hanya JS) -->
        <form method="GET" class="row g-2 mb-3">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
              <input type="text" name="cari" class="form-control"
                     placeholder="Cari username / aktivitas..."
                     value="<?= htmlspecialchars($cari) ?>">
            </div>
          </div>
          <div class="col-md-3">
            <select name="role" class="form-select">
              <option value="">-- Semua Role --</option>
              <option value="admin"   <?= $filter_role==='admin'   ? 'selected':'' ?>>Admin</option>
              <option value="petugas" <?= $filter_role==='petugas' ? 'selected':'' ?>>Petugas</option>
              <option value="owner"   <?= $filter_role==='owner'   ? 'selected':'' ?>>Owner</option>
            </select>
          </div>
          <div class="col-md-3">
            <input type="date" name="tgl" class="form-control"
                   value="<?= htmlspecialchars($filter_tgl) ?>">
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
          </div>
          <div class="col-md-1">
            <a href="index.php" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-clockwise"></i></a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th class="text-center" style="width:5%">No</th>
                <th style="width:20%">Waktu Aktivitas</th>
                <th style="width:15%">Username</th>
                <th class="text-center" style="width:10%">Role</th>
                <th>Aktivitas</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = $offset + 1;
              if ($query && mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)):
                  $badge = match($row['role']) {
                      'admin'   => 'bg-danger',
                      'petugas' => 'bg-primary',
                      'owner'   => 'bg-warning text-dark',
                      default   => 'bg-secondary'
                  };
                  $waktu = date('d/m/Y H:i:s', strtotime($row['waktu_aktivitas']));
              ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><i class="bi bi-calendar2-event text-muted me-1"></i><?= $waktu ?></td>
                <td><i class="bi bi-person-fill text-primary me-1"></i>
                    <span class="fw-bold text-primary"><?= htmlspecialchars($row['username']) ?></span></td>
                <td class="text-center"><span class="badge <?= $badge ?>"><?= ucfirst($row['role']) ?></span></td>
                <td><?= htmlspecialchars($row['aktivitas']) ?></td>
              </tr>
              <?php endwhile; else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                  <?= ($cari||$filter_role||$filter_tgl) ? "Tidak ada log yang sesuai filter." : "Belum ada rekaman aktivitas." ?>
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Info & Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
          <small class="text-muted">
            Menampilkan <strong><?= $total>0 ? $offset+1 : 0 ?></strong>–<strong><?= min($offset+$per_hal,$total) ?></strong>
            dari <strong><?= $total ?></strong> rekaman
          </small>
          <?php if ($total_hal > 1):
            $q_str = "cari=".urlencode($cari)."&role=".urlencode($filter_role)."&tgl=".urlencode($filter_tgl);
            $pg_start = max(1, $halaman-2);
            $pg_end   = min($total_hal, $halaman+2);
          ?>
          <nav><ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $halaman<=1?'disabled':'' ?>">
              <a class="page-link" href="?<?= $q_str ?>&halaman=<?= $halaman-1 ?>"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php if ($pg_start>1): ?>
              <li class="page-item"><a class="page-link" href="?<?= $q_str ?>&halaman=1">1</a></li>
              <?php if ($pg_start>2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($i=$pg_start;$i<=$pg_end;$i++): ?>
              <li class="page-item <?= $i==$halaman?'active':'' ?>">
                <a class="page-link" href="?<?= $q_str ?>&halaman=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($pg_end<$total_hal): ?>
              <?php if ($pg_end<$total_hal-1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
              <li class="page-item"><a class="page-link" href="?<?= $q_str ?>&halaman=<?= $total_hal ?>"><?= $total_hal ?></a></li>
            <?php endif; ?>
            <li class="page-item <?= $halaman>=$total_hal?'disabled':'' ?>">
              <a class="page-link" href="?<?= $q_str ?>&halaman=<?= $halaman+1 ?>"><i class="bi bi-chevron-right"></i></a>
            </li>
          </ul></nav>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include '../template/footer.php'; ?>