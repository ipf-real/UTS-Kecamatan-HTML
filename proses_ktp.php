<?php
include "koneksi.php";

$provinsi      = trim($_POST['provinsi']);
$kabupaten     = trim($_POST['kabupaten']);
$kecamatan     = trim($_POST['kecamatan']);
$desa          = trim($_POST['desa']);
$permohonan    = trim($_POST['permohonan']);
$nama          = trim($_POST['nama']);
$no_kk         = trim($_POST['no_kk']);
$nik           = trim($_POST['nik']);
$ttl           = trim($_POST['ttl']);
$jk            = trim($_POST['jk']);
$alamat        = trim($_POST['alamat']);
$rt_rw         = trim($_POST['rt_rw']);
$agama         = trim($_POST['agama']);
$pekerjaan     = trim($_POST['pekerjaan']);
$status_kawin  = trim($_POST['status_kawin']);

// ---------------------------------------------------------
// Proses upload foto
// ---------------------------------------------------------
$folder_upload = "uploads/";
if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0755, true);
}

function uploadFoto($folder_upload) {
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // tidak ada file dipilih
    }

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Terjadi kesalahan saat upload foto.'];
    }

    $ekstensiDiizinkan = ['jpg', 'jpeg', 'png'];
    $ekstensi = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (!in_array($ekstensi, $ekstensiDiizinkan)) {
        return ['error' => 'Format foto harus JPG atau PNG.'];
    }

    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        return ['error' => 'Ukuran foto maksimal 2MB.'];
    }

    $namaFile = 'ktp_' . time() . '_' . uniqid() . '.' . $ekstensi;
    $tujuan = $folder_upload . $namaFile;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        return ['error' => 'Gagal menyimpan file foto ke server.'];
    }

    return ['nama' => $namaFile];
}

$hasilUpload = uploadFoto($folder_upload);

if (is_array($hasilUpload) && isset($hasilUpload['error'])) {
    echo "
    <script>
    alert('" . addslashes($hasilUpload['error']) . "');
    history.back();
    </script>
    ";
    exit();
}

$foto = $hasilUpload ? $hasilUpload['nama'] : null;

// ---------------------------------------------------------
// Simpan ke database (prepared statement, aman dari SQL Injection)
// ---------------------------------------------------------
$query = "INSERT INTO ktp
(provinsi, kabupaten, kecamatan, desa, permohonan, nama, no_kk, nik, ttl, jk, alamat, rt_rw, agama, pekerjaan, status_kawin, foto)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = mysqli_prepare($koneksi, $query);

if ($stmt === false) {
    echo "
    <script>
    alert('Terjadi kesalahan sistem. Silakan coba lagi.');
    history.back();
    </script>
    ";
    exit();
}

mysqli_stmt_bind_param(
    $stmt, "ssssssssssssssss",
    $provinsi, $kabupaten, $kecamatan, $desa, $permohonan, $nama, $no_kk, $nik, $ttl, $jk, $alamat, $rt_rw, $agama, $pekerjaan, $status_kawin, $foto
);

$sukses = mysqli_stmt_execute($stmt);

if ($sukses) {

echo "
<script>
alert('Pendaftaran berhasil!');
window.location='formktp.html';
</script>
";

} else {

// kalau gagal simpan, hapus foto yang sudah terlanjur diupload
if ($foto && file_exists($folder_upload . $foto)) {
    unlink($folder_upload . $foto);
}

echo "
<script>
alert('Pendaftaran gagal!');
history.back();
</script>
";

}
?>