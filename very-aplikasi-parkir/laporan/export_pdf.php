<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';

// use HARUS di paling atas, sebelum logika apapun
$autoload_path = '../vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
}

// Ambil filter dari GET
$tgl_awal     = isset($_GET['tgl_awal'])        ? $_GET['tgl_awal']        : date('Y-m-01');
$tgl_akhir    = isset($_GET['tgl_akhir'])       ? $_GET['tgl_akhir']       : date('Y-m-d');
$filter_jenis = isset($_GET['jenis_kendaraan']) ? mysqli_real_escape_string($conn, $_GET['jenis_kendaraan']) : '';

// Bangun WHERE
$where = "WHERE status='keluar' AND DATE(waktu_keluar) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
if ($filter_jenis) {
    $where .= " AND jenis_kendaraan = '$filter_jenis'";
}

// Query semua data
$query     = mysqli_query($conn, "SELECT * FROM tb_transaksi $where ORDER BY waktu_keluar DESC");
$total_q   = mysqli_query($conn, "SELECT SUM(biaya_total) as grand_total FROM tb_transaksi $where");
$grand_total = $total_q ? mysqli_fetch_assoc($total_q)['grand_total'] : 0;

// Bangun HTML untuk PDF
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Laporan Transaksi Parkir</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
    .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; }
    .header h2 { font-size: 16px; color: #1a73e8; margin-bottom: 4px; }
    .header p { font-size: 10px; color: #666; margin: 2px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background-color: #1a73e8; color: white; padding: 7px 5px; text-align: center; font-size: 10px; }
    td { padding: 5px; border: 1px solid #ddd; font-size: 10px; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .text-right  { text-align: right; }
    .text-center { text-align: center; }
    .tfoot-row { background-color: #e8f0fe !important; font-weight: bold; }
    .footer { margin-top: 25px; }
    .footer-sign { float: right; text-align: center; margin-right: 30px; }
    .footer-info { font-size: 10px; color: #666; }
    .badge-plat { background: #333; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold; }
  </style>
</head>
<body>

  <div class="header">
    <h2>LAPORAN TRANSAKSI PARKIR</h2>
    <p>Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    <?php if ($filter_jenis): ?>
    <p>Jenis Kendaraan: <?= htmlspecialchars($filter_jenis) ?></p>
    <?php endif; ?>
    <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
  </div>

  <table>
    <thead>
      <tr>
        <th width="25">No</th>
        <th width="40">ID</th>
        <th width="65">Plat Nomor</th>
        <th width="55">Jenis</th>
        <th width="75">Waktu Masuk</th>
        <th width="75">Waktu Keluar</th>
        <th width="40">Durasi</th>
        <th width="75">Total Biaya</th>
        <th width="45">Status</th>
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
        <td class="text-center"><?= $no++ ?></td>
        <td class="text-center">#<?= $row['id_transaksi'] ?></td>
        <td class="text-center"><span class="badge-plat"><?= strtoupper($row['no_plat']) ?></span></td>
        <td><?= htmlspecialchars($row['jenis_kendaraan']) ?></td>
        <td class="text-center"><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
        <td class="text-center"><?= date('d/m/Y H:i', strtotime($row['waktu_keluar'])) ?></td>
        <td class="text-center"><?= $durasi ?> Jam</td>
        <td class="text-right">Rp <?= number_format($row['biaya_total'], 0, ',', '.') ?></td>
        <td class="text-center">Keluar</td>
      </tr>
      <?php endwhile; ?>
      <?php else: ?>
      <tr>
        <td colspan="9" class="text-center" style="padding:15px; color:#888;">
          Tidak ada data transaksi untuk periode ini.
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr class="tfoot-row">
        <td colspan="7" class="text-right" style="padding-right:8px;">TOTAL PENDAPATAN :</td>
        <td class="text-right">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <div class="footer">
    <div class="footer-sign">
      <p class="footer-info">Balikpapan, <?= date('d/m/Y') ?></p>
      <br><br><br>
      <p>( ................................. )</p>
      <p><strong>Petugas Parkir</strong></p>
    </div>
  </div>

</body>
</html>
<?php
$html = ob_get_clean();

// Cek apakah dompdf tersedia
if (file_exists($autoload_path)) {
    // Gunakan DOMPDF - use di sini sudah aman karena require sudah dipanggil di atas
    $options = new \Dompdf\Options();          // ✅ pakai namespace langsung, tanpa use
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new \Dompdf\Dompdf($options);    // ✅ pakai namespace langsung, tanpa use
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("Laporan_Parkir_" . date('d-m-Y') . ".pdf", ["Attachment" => 0]);

} else {
    // Fallback jika dompdf belum diinstall: tampil HTML lalu print otomatis
    echo $html;
    echo '<script>window.onload = function(){ window.print(); }</script>';
}
?>