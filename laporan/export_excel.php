<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';

// Ambil filter dari GET
$tgl_awal     = isset($_GET['tgl_awal'])        ? $_GET['tgl_awal']        : date('Y-m-01');
$tgl_akhir    = isset($_GET['tgl_akhir'])       ? $_GET['tgl_akhir']       : date('Y-m-d');
$filter_jenis = isset($_GET['jenis_kendaraan']) ? mysqli_real_escape_string($conn, $_GET['jenis_kendaraan']) : '';

// Bangun WHERE — jenis_kendaraan sekarang dari tb_kendaraan (alias k)
$where = "WHERE t.status='keluar' AND DATE(t.waktu_keluar) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
if ($filter_jenis) {
    $where .= " AND k.jenis_kendaraan = '$filter_jenis'";
}

// Query dengan JOIN ke tb_kendaraan untuk mendapatkan plat_nomor & jenis_kendaraan
$sql = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan
        FROM tb_transaksi t
        LEFT JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
        $where
        ORDER BY t.waktu_keluar DESC";

$query = mysqli_query($conn, $sql);

// Header download Excel
header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Parkir_" . date('d-m-Y') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body>

<center>
  <h2>LAPORAN TRANSAKSI PARKIR</h2>
  <p>Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
  <?php if ($filter_jenis): ?>
  <p>Jenis Kendaraan: <?= htmlspecialchars($filter_jenis) ?></p>
  <?php endif; ?>
  <p>Tanggal Cetak: <?= date('d/m/Y H:i:s') ?></p>
</center>

<br>

<table border="1" cellspacing="0" cellpadding="5">
  <thead>
    <tr style="background-color:#1a73e8; color:white; font-weight:bold; text-align:center;">
      <th>No</th>
      <th>ID Transaksi</th>
      <th>Nomor Plat</th>
      <th>Jenis Kendaraan</th>
      <th>Waktu Masuk</th>
      <th>Waktu Keluar</th>
      <th>Durasi (Jam)</th>
      <th>Total Biaya (Rp)</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no = 1;
    $total_pendapatan = 0;

    if ($query && mysqli_num_rows($query) > 0):
      while ($row = mysqli_fetch_assoc($query)):
        $total_pendapatan += $row['biaya_total'];

        $awal   = new DateTime($row['waktu_masuk']);
        $akhir  = new DateTime($row['waktu_keluar']);
        $diff   = $awal->diff($akhir);
        $durasi = $diff->h + ($diff->days * 24);
        if ($diff->i > 0 || $diff->s > 0) $durasi++;
    ?>
    <tr>
      <td align="center"><?= $no++ ?></td>
      <td align="center">#<?= $row['id_parkir'] ?></td>
      <td align="center"><?= strtoupper($row['plat_nomor']) ?></td>
      <td><?= htmlspecialchars($row['jenis_kendaraan'] ?? '-') ?></td>
      <td><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
      <td><?= date('d/m/Y H:i', strtotime($row['waktu_keluar'])) ?></td>
      <td align="center"><?= $durasi ?> Jam</td>
      <td align="right"><?= number_format($row['biaya_total'], 0, ',', '.') ?></td>
      <td align="center"><?= ucfirst($row['status']) ?></td>
    </tr>
    <?php endwhile; ?>
    <?php else: ?>
    <tr>
      <td colspan="9" align="center">Tidak ada data transaksi untuk periode ini.</td>
    </tr>
    <?php endif; ?>
  </tbody>
  <tfoot>
    <tr style="background-color:#f2f2f2; font-weight:bold;">
      <td colspan="7" align="right">TOTAL PENDAPATAN :</td>
      <td align="right"><?= number_format($total_pendapatan, 0, ',', '.') ?></td>
      <td></td>
    </tr>
  </tfoot>
</table>

</body>
</html>