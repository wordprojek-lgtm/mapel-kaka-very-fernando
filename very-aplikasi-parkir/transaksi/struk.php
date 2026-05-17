<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php?pesan=ID tidak valid&type=danger");
    exit;
}

$stmt = mysqli_prepare($conn,
    "SELECT p.*, k.plat_nomor, k.jenis_kendaraan,
            t.tarif_per_jam, a.nama_area,
            u.nama_lengkap
     FROM tb_transaksi p
     JOIN tb_kendaraan      k ON p.id_kendaraan = k.id_kendaraan
     JOIN tb_tarif          t ON p.id_tarif     = t.id_tarif
     LEFT JOIN tb_area_parkir a ON p.id_area    = a.id_area
     LEFT JOIN tb_user        u ON p.id_user    = u.id_user
     WHERE p.id_parkir = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: index.php?pesan=Data struk tidak ditemukan&type=danger");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Struk Parkir – <?= htmlspecialchars(strtoupper($data['plat_nomor'])) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Courier New', Courier, monospace;
      background: #f0f0f0;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      color: #000;
    }

    .struk {
      background: #fff;
      width: 320px;
      padding: 20px 18px;
      border: 1px solid #ccc;
      box-shadow: 0 4px 12px rgba(0,0,0,.15);
      font-size: 12px;
      line-height: 1.6;
    }

    .struk .header { text-align: center; margin-bottom: 6px; }
    .struk .header h2 { font-size: 15px; font-weight: bold; letter-spacing: 1px; }
    .struk .header p  { font-size: 11px; }

    .dash  { border-top: 1px dashed #000; margin: 8px 0; }
    .solid { border-top: 2px solid #000;  margin: 8px 0; }

    table.detail { width: 100%; border-collapse: collapse; }
    table.detail td { padding: 2px 0; vertical-align: top; }
    table.detail td:first-child { width: 40%; color: #444; }
    table.detail td:last-child  { font-weight: 500; }

    .total-row { font-size: 15px; font-weight: bold; }
    .total-row td:last-child { text-align: right; }

    .footer { text-align: center; font-size: 10px; margin-top: 10px; line-height: 1.8; }

    .badge-status {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: bold;
      letter-spacing: 1px;
      background: <?= $data['status'] === 'keluar' ? '#d4edda' : '#fff3cd' ?>;
      color:       <?= $data['status'] === 'keluar' ? '#155724' : '#856404' ?>;
      border: 1px solid <?= $data['status'] === 'keluar' ? '#c3e6cb' : '#ffc107' ?>;
    }

    .action-bar {
      display: flex;
      gap: 10px;
      margin-top: 20px;
      width: 320px;
    }
    .action-bar button,
    .action-bar a {
      flex: 1;
      padding: 10px;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
      text-align: center;
    }
    .btn-print  { background: #0d6efd; color: #fff; }
    .btn-back   { background: #6c757d; color: #fff; }

    @media print {
      body { background: #fff; padding: 0; }
      .action-bar { display: none; }
      .struk { box-shadow: none; border: none; }
    }
  </style>
</head>
<body>

<div class="struk">

  <!-- HEADER -->
  <div class="header">
    <h2>SISTEM PARKIR</h2>
    <p>Jl. Raya Utama No. 123</p>
    <p>Telp: (021) 000-0000</p>
  </div>

  <div class="dash"></div>
  <div style="text-align:center">
    <span class="badge-status"><?= $data['status'] === 'keluar' ? '✔ LUNAS' : '⏳ PARKIR' ?></span>
  </div>
  <div class="dash"></div>

  <!-- INFO KENDARAAN -->
  <table class="detail">
    <tr><td>ID Parkir</td><td>: #<?= str_pad($data['id_parkir'], 6, '0', STR_PAD_LEFT) ?></td></tr>
    <tr><td>No. Plat</td><td>: <strong><?= htmlspecialchars(strtoupper($data['plat_nomor'])) ?></strong></td></tr>
    <tr><td>Jenis</td>    <td>: <?= htmlspecialchars(ucfirst($data['jenis_kendaraan'])) ?></td></tr>
    <tr><td>Area</td>     <td>: <?= htmlspecialchars($data['nama_area'] ?? '-') ?></td></tr>
    <?php if (!empty($data['nama_lengkap'])): ?>
    <tr><td>Petugas</td>  <td>: <?= htmlspecialchars($data['nama_lengkap']) ?></td></tr>
    <?php endif; ?>
  </table>

  <div class="dash"></div>

  <!-- WAKTU -->
  <table class="detail">
    <tr>
      <td>Masuk</td>
      <td>: <?= date('d/m/Y H:i', strtotime($data['waktu_masuk'])) ?></td>
    </tr>
    <tr>
      <td>Keluar</td>
      <td>: <?= $data['waktu_keluar']
               ? date('d/m/Y H:i', strtotime($data['waktu_keluar']))
               : '-' ?></td>
    </tr>
    <tr>
      <td>Durasi</td>
      <td>: <?= $data['durasi_jam'] ? $data['durasi_jam'] . ' jam' : '-' ?></td>
    </tr>
    <tr>
      <td>Tarif/jam</td>
      <td>: Rp <?= number_format($data['tarif_per_jam'], 0, ',', '.') ?></td>
    </tr>
  </table>

  <div class="solid"></div>

  <!-- TOTAL -->
  <table class="detail total-row">
    <tr>
      <td>TOTAL BAYAR</td>
      <td style="text-align:right">Rp <?= number_format($data['biaya_total'] ?? 0, 0, ',', '.') ?></td>
    </tr>
  </table>

  <div class="dash"></div>

  <!-- FOOTER -->
  <div class="footer">
    <p>Terima kasih atas kunjungan Anda</p>
    <p>Simpan struk ini sebagai bukti pembayaran</p>
    <p>Dicetak: <?= date('d/m/Y H:i:s') ?></p>
  </div>

</div><!-- /struk -->

<!-- TOMBOL AKSI -->
<div class="action-bar">
  <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
  <a href="index.php" class="btn-back">← Kembali</a>
</div>

<script>
  <?php if ($data['status'] === 'keluar'): ?>
  window.onload = function() { window.print(); };
  <?php endif; ?>
</script>

</body>
</html>