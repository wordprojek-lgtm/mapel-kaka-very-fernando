<?php
include '../config/koneksi.php';
include '../auth/cek_login.php';
include '../auth/role.php';
include '../config/log.php';

onlyAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: index.php?pesan=ID tidak valid!&type=danger");
    exit;
}

// Cek area ada
$cek = mysqli_query($conn, "SELECT id_area, nama_area, terisi 
                             FROM tb_area_parkir WHERE id_area = $id");
if (!$cek || mysqli_num_rows($cek) == 0) {
    header("Location: index.php?pesan=Data area tidak ditemukan!&type=danger");
    exit;
}

$data      = mysqli_fetch_assoc($cek);
$nama_area = $data['nama_area'];

// ✅ FIX: Cek kendaraan aktif dari tb_transaksi (bukan dari kolom terisi yang bisa stale)
$cek_aktif = mysqli_query($conn, "SELECT COUNT(*) as jml 
                                   FROM tb_transaksi 
                                   WHERE id_area = $id AND status = 'masuk'");
$jumlah_aktif = 0;
if ($cek_aktif) {
    $row_aktif    = mysqli_fetch_assoc($cek_aktif);
    $jumlah_aktif = intval($row_aktif['jml']);
}

// ✅ FIX: Sinkronkan kolom terisi dengan data nyata sebelum mengambil keputusan
mysqli_query($conn, "UPDATE tb_area_parkir 
                     SET terisi = (
                         SELECT COUNT(*) FROM tb_transaksi 
                         WHERE id_area = $id AND status = 'masuk'
                     ) 
                     WHERE id_area = $id");

if ($jumlah_aktif > 0) {
    // Ambil detail kendaraan yang masih di dalam untuk info lebih jelas
    $detail = mysqli_query($conn, "SELECT tk.id_parkir, k.plat_nomor, k.jenis_kendaraan, tk.waktu_masuk
                                   FROM tb_transaksi tk
                                   JOIN tb_kendaraan k ON tk.id_kendaraan = k.id_kendaraan
                                   WHERE tk.id_area = $id AND tk.status = 'masuk'
                                   LIMIT 3");

    $info_kendaraan = '';
    if ($detail && mysqli_num_rows($detail) > 0) {
        $plat_list = [];
        while ($kend = mysqli_fetch_assoc($detail)) {
            $plat_list[] = $kend['plat_nomor'];
        }
        $info_kendaraan = ' (' . implode(', ', $plat_list) . ($jumlah_aktif > 3 ? ', ...' : '') . ')';
    }

    header("Location: index.php?pesan=Area '$nama_area' tidak bisa dihapus karena masih ada $jumlah_aktif kendaraan aktif$info_kendaraan. Selesaikan transaksi kendaraan tersebut terlebih dahulu!&type=warning");
    exit;
}

// Aman untuk dihapus
if (mysqli_query($conn, "DELETE FROM tb_area_parkir WHERE id_area = $id")) {
    logAktivitas($conn, "Menghapus area parkir: $nama_area");
    header("Location: index.php?pesan=Area '$nama_area' berhasil dihapus!&type=success");
} else {
    header("Location: index.php?pesan=Gagal menghapus: " . mysqli_error($conn) . "&type=danger");
}
exit;
?>