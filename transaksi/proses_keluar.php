<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../config/log.php';

// Hanya izinkan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['proses_bayar'])) {
    header("Location: index.php");
    exit;
}

$id_parkir = intval($_POST['id_parkir'] ?? 0);

if ($id_parkir <= 0) {
    header("Location: index.php?pesan=ID parkir tidak valid&type=danger");
    exit;
}

// Ambil data transaksi yang masih berstatus 'masuk'
$stmt = mysqli_prepare($conn,
    "SELECT p.*, k.plat_nomor, t.tarif_per_jam
     FROM tb_transaksi p
     JOIN tb_kendaraan k ON p.id_kendaraan = k.id_kendaraan
     JOIN tb_tarif     t ON p.id_tarif     = t.id_tarif
     WHERE p.id_parkir = ? AND p.status = 'masuk'
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $id_parkir);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: index.php?pesan=Data tidak ditemukan atau sudah diproses&type=danger");
    exit;
}

// ── Hitung Durasi & Biaya ──────────────────────────────────────────────────────
$waktu_keluar = date('Y-m-d H:i:s');
$dt_masuk     = new DateTime($data['waktu_masuk']);
$dt_keluar    = new DateTime($waktu_keluar);
$selisih      = $dt_masuk->diff($dt_keluar);

// Total menit → jam, minimal 1 jam, pembulatan ke atas
$total_menit  = ($selisih->days * 24 * 60) + ($selisih->h * 60) + $selisih->i;
$durasi_jam   = max(1, (int) ceil($total_menit / 60));
$biaya_total  = $durasi_jam * $data['tarif_per_jam'];

// ── Update tb_transaksi ────────────────────────────────────────────────────────
$upd = mysqli_prepare($conn,
    "UPDATE tb_transaksi
     SET waktu_keluar = ?,
         durasi_jam   = ?,
         biaya_total  = ?,
         status       = 'keluar'
     WHERE id_parkir = ? AND status = 'masuk'"
);
mysqli_stmt_bind_param($upd, "siid", $waktu_keluar, $durasi_jam, $biaya_total, $id_parkir);

if (mysqli_stmt_execute($upd) && mysqli_stmt_affected_rows($upd) > 0) {
    mysqli_stmt_close($upd);
    logAktivitas($conn, "Kendaraan keluar: {$data['plat_nomor']}, biaya: Rp " . number_format($biaya_total));
    header("Location: struk.php?id=$id_parkir");
    exit;
} else {
    mysqli_stmt_close($upd);
    header("Location: index.php?pesan=Gagal memproses keluar: " . mysqli_error($conn) . "&type=danger");
    exit;
}