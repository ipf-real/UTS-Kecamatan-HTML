<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['login'])) {
    header("location: proses_login.php");
    exit();
}

$pesan = "";

// Helper: kalau prepare() gagal (biasanya karena nama kolom/tabel salah),
// tampilkan pesan jelas alih-alih layar blank.
function cekPrepare($stmt, $koneksi) {
    if ($stmt === false) {
        die("<div style='font-family:sans-serif;padding:20px;background:#fee;border:2px solid #c00;margin:20px;border-radius:8px;'>
                <b>Query error:</b> " . mysqli_error($koneksi) . "
             </div>");
    }
}

// ---------------------------------------------------------
// Helper: proses upload foto. Mengembalikan nama file baru,
// atau null kalau user tidak memilih file (dipakai saat edit
// supaya foto lama tidak hilang jika tidak diganti).
// ---------------------------------------------------------
$folder_upload = "uploads/";
if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0755, true);
}

function uploadFoto($folder_upload) {
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // user tidak upload file baru
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

// ---------------------------------------------------------
// TAMBAH DATA
// ---------------------------------------------------------
if (isset($_POST['tambah'])) {
    $provinsi     = trim($_POST['provinsi']);
    $kabupaten    = trim($_POST['kabupaten']);
    $kecamatan    = trim($_POST['kecamatan']);
    $desa         = trim($_POST['desa']);
    $permohonan   = trim($_POST['permohonan']);
    $nama         = trim($_POST['nama']);
    $no_kk        = trim($_POST['no_kk']);
    $nik          = trim($_POST['nik']);
    $ttl          = trim($_POST['ttl']);
    $jk           = trim($_POST['jk']);
    $alamat       = trim($_POST['alamat']);
    $rt_rw        = trim($_POST['rt_rw']);
    $agama_post   = trim($_POST['agama']);
    $pekerjaan    = trim($_POST['pekerjaan']);
    $status_kawin = trim($_POST['status_kawin']);

    $hasilUpload = uploadFoto($folder_upload);

    if (is_array($hasilUpload) && isset($hasilUpload['error'])) {
        $pesan = $hasilUpload['error'];
    } else {
        $foto = $hasilUpload ? $hasilUpload['nama'] : null;

        $query = "INSERT INTO ktp (provinsi,kabupaten,kecamatan,desa,permohonan,nama,no_kk,nik,ttl,jk,alamat,rt_rw,agama,pekerjaan,status_kawin,foto)
                  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = mysqli_prepare($koneksi, $query);
        cekPrepare($stmt, $koneksi);
        mysqli_stmt_bind_param(
            $stmt, "ssssssssssssssss",
            $provinsi,$kabupaten,$kecamatan,$desa,$permohonan,$nama,$no_kk,$nik,$ttl,$jk,$alamat,$rt_rw,$agama_post,$pekerjaan,$status_kawin,$foto
        );

        $pesan = mysqli_stmt_execute($stmt)
            ? "Data berhasil ditambahkan."
            : "Gagal menambahkan data.";
    }
}

// ---------------------------------------------------------
// SIMPAN EDIT
// ---------------------------------------------------------
if (isset($_POST['simpan_edit'])) {
    $id           = $_POST['id'];
    $provinsi     = trim($_POST['provinsi']);
    $kabupaten    = trim($_POST['kabupaten']);
    $kecamatan    = trim($_POST['kecamatan']);
    $desa         = trim($_POST['desa']);
    $permohonan   = trim($_POST['permohonan']);
    $nama         = trim($_POST['nama']);
    $no_kk        = trim($_POST['no_kk']);
    $nik          = trim($_POST['nik']);
    $ttl          = trim($_POST['ttl']);
    $jk           = trim($_POST['jk']);
    $alamat       = trim($_POST['alamat']);
    $rt_rw        = trim($_POST['rt_rw']);
    $agama_post   = trim($_POST['agama']);
    $pekerjaan    = trim($_POST['pekerjaan']);
    $status_kawin = trim($_POST['status_kawin']);

    $hasilUpload = uploadFoto($folder_upload);

    if (is_array($hasilUpload) && isset($hasilUpload['error'])) {
        $pesan = $hasilUpload['error'];
    } else {
        if ($hasilUpload) {
            // ada foto baru -> hapus foto lama, pakai foto baru
            $fotoBaru = $hasilUpload['nama'];

            $stmtLama = mysqli_prepare($koneksi, "SELECT foto FROM ktp WHERE id=?");
            mysqli_stmt_bind_param($stmtLama, "i", $id);
            mysqli_stmt_execute($stmtLama);
            $hasilLama = mysqli_stmt_get_result($stmtLama);
            $rowLama = mysqli_fetch_assoc($hasilLama);

            if ($rowLama && !empty($rowLama['foto']) && file_exists($folder_upload . $rowLama['foto'])) {
                unlink($folder_upload . $rowLama['foto']);
            }

            $query = "UPDATE ktp SET provinsi=?,kabupaten=?,kecamatan=?,desa=?,permohonan=?,nama=?,no_kk=?,nik=?,ttl=?,jk=?,alamat=?,rt_rw=?,agama=?,pekerjaan=?,status_kawin=?,foto=? WHERE id=?";
            $stmt = mysqli_prepare($koneksi, $query);
            cekPrepare($stmt, $koneksi);
            mysqli_stmt_bind_param(
                $stmt, "ssssssssssssssssi",
                $provinsi,$kabupaten,$kecamatan,$desa,$permohonan,$nama,$no_kk,$nik,$ttl,$jk,$alamat,$rt_rw,$agama_post,$pekerjaan,$status_kawin,$fotoBaru,$id
            );
        } else {
            // tidak ada foto baru -> foto lama dipertahankan
            $query = "UPDATE ktp SET provinsi=?,kabupaten=?,kecamatan=?,desa=?,permohonan=?,nama=?,no_kk=?,nik=?,ttl=?,jk=?,alamat=?,rt_rw=?,agama=?,pekerjaan=?,status_kawin=? WHERE id=?";
            $stmt = mysqli_prepare($koneksi, $query);
            cekPrepare($stmt, $koneksi);
            mysqli_stmt_bind_param(
                $stmt, "sssssssssssssssi",
                $provinsi,$kabupaten,$kecamatan,$desa,$permohonan,$nama,$no_kk,$nik,$ttl,$jk,$alamat,$rt_rw,$agama_post,$pekerjaan,$status_kawin,$id
            );
        }

        $pesan = mysqli_stmt_execute($stmt)
            ? "Data berhasil diperbarui."
            : "Gagal memperbarui data.";
    }
}

// ---------------------------------------------------------
// HAPUS DATA (+ hapus file foto)
// ---------------------------------------------------------
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $stmtCek = mysqli_prepare($koneksi, "SELECT foto FROM ktp WHERE id=?");
    mysqli_stmt_bind_param($stmtCek, "i", $id);
    mysqli_stmt_execute($stmtCek);
    $hasilCek = mysqli_stmt_get_result($stmtCek);
    $rowCek = mysqli_fetch_assoc($hasilCek);

    if ($rowCek && !empty($rowCek['foto']) && file_exists($folder_upload . $rowCek['foto'])) {
        unlink($folder_upload . $rowCek['foto']);
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM ktp WHERE id=?");
    cekPrepare($stmt, $koneksi);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: crud_ktp.php");
    exit();
}

$data_edit = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM ktp WHERE id=?");
    cekPrepare($stmt, $koneksi);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data_edit = mysqli_fetch_assoc($hasil);
}

$data_ktp = mysqli_query($koneksi, "SELECT * FROM ktp ORDER BY id ASC");
if ($data_ktp === false) {
    die("<div style='font-family:sans-serif;padding:20px;background:#fee;border:2px solid #c00;margin:20px;border-radius:8px;'>
            <b>Query error (SELECT):</b> " . mysqli_error($koneksi) . "
         </div>");
}

?>

<html>

<head>

<title>Kelola Data KTP</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:#eef2f5;
}

.container{
width:1100px;
max-width:95%;
margin:30px auto;
}

.card{
background:white;
border-radius:12px;
padding:30px;
box-shadow:0 5px 20px rgba(0,0,0,.15);
}

.kembali{
display:inline-block;
margin-bottom:20px;
text-decoration:none;
background:#6c757d;
color:white;
padding:10px 20px;
border-radius:8px;
transition:.3s;
}

.kembali:hover{
background:#5a6268;
}

h2{
margin-bottom:20px;
color:#333;
}

.pesan{
background:#d4edda;
color:#155724;
padding:12px;
border-radius:8px;
margin-bottom:20px;
}

.form-grid{
display:grid;
grid-template-columns:1fr;
gap:18px;
}

.form-group label{
display:block;
margin-bottom:6px;
font-weight:600;
color:#444;
}

.form-group input,
.form-group select,
.form-group textarea{
width:100%;
padding:12px;
border:1px solid #ccc;
border-radius:8px;
font-size:15px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{
outline:none;
border-color:#087500;
}

.btn{
background:#087500;
color:white;
padding:12px 30px;
border:none;
border-radius:8px;
cursor:pointer;
font-size:15px;
font-weight:600;
transition:.3s;
margin-top:20px;
}

.btn:hover{
background:#065900;
}

table{
width:100%;
border-collapse:collapse;
background:white;
}

thead{
background:#087500;
color:white;
}

th{
padding:14px;
}

td{
padding:10px;
font-size:13px;
border-bottom:1px solid #ddd; 
}

tbody tr:nth-child(even){
background:#f7f7f7;
}

tbody tr:hover{
background:#ecf9ec;
}

.badge{
padding:5px 12px;
border-radius:15px;
font-size:13px;
color:white;
}

.badge-baru{ background:#FFBB00; }
.badge-penggantian{ background:#ffc107; color:#000; }
.badge-belumkawin{ background:#0d6efd; }
.badge-kawin{ background:#6f42c1; }
.badge-cerdup{ background:#e97502; }
.badge-cermat{ background:#e90202; }

.btn-edit,
.btn-hapus{
display:inline-block;
padding:8px 15px;
border-radius:6px;
text-decoration:none;
font-size:13px;
font-weight:600;
text-align:center;
min-width:45px;
transition:.3s;
margin:2px;
}

.btn-edit{
background:#999999;
color:#000;
}

.btn-edit:hover{
background:#656565;
}

.btn-hapus{
background:#dc3545;
color:#fff;
}

.btn-hapus:hover{
background:#bb2d3b;
}

.aksi{
display:flex;
justify-content:center;
align-items:center;
gap:8px;
}

/* ---- Tambahan untuk foto ---- */
.foto-wrapper{
display:flex;
align-items:flex-start;
gap:15px;
flex-wrap:wrap;
}

.foto-input{
flex:1;
min-width:200px;
}

.foto-preview{
width:100px;
height:125px;
border:2px dashed #bbb;
border-radius:8px;
display:flex;
align-items:center;
justify-content:center;
overflow:hidden;
background:#f9f9f9;
flex-shrink:0;
}

.foto-preview img{
width:100%;
height:100%;
object-fit:cover;
}

.foto-preview span{
font-size:11px;
color:#999;
text-align:center;
padding:0 6px;
}

.foto-hint{
font-size:12px;
color:gray;
margin-top:6px;
}

.foto-thumb{
width:45px;
height:55px;
object-fit:cover;
border-radius:4px;
border:1px solid #ddd;
}

@media(max-width:600px){
.container{ width:98%; }
.form-grid{ grid-template-columns:1fr; }
table{ font-size:13px; }
}

</style>

</head>

<body>

<center>

<h1 style="font-size:40px;font-weight:800;color:#000;margin-bottom:5px;margin-top:40px;">
KELOLA DATA E-KTP
</h1>

<p style="font-size:16px;color:gray;margin-top:-15px;">
Kecamatan Jalancagak, Kab.Subang, Jawa Barat
</p>

</center>

<div class="container">

<a href="submit_formlogin.php" class="kembali">
← Kembali ke Dashboard
</a>

<div class="card">

<h2>
<?php echo $data_edit ? "Edit Data KTP" : "Tambah Data KTP"; ?>
</h2>

<?php if($pesan!=""){ echo "<div class='pesan'>$pesan</div>"; } ?>

<form method="POST" enctype="multipart/form-data">

<?php if($data_edit){ ?>
<input type="hidden" name="id" value="<?php echo $data_edit['id']; ?>">
<?php } ?>

<div class="form-grid">

<div class="form-group">
<label>Provinsi</label>
<input type="text" name="provinsi" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['provinsi']) : ''; ?>">
</div>

<div class="form-group">
<label>Kabupaten</label>
<input type="text" name="kabupaten" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['kabupaten']) : ''; ?>">
</div>

<div class="form-group">
<label>Kecamatan</label>
<input type="text" name="kecamatan" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['kecamatan']) : ''; ?>">
</div>

<div class="form-group">
<label>Desa</label>
<input type="text" name="desa" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['desa']) : ''; ?>">
</div>

<div class="form-group">
<label>Nama Lengkap</label>
<input type="text" name="nama" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['nama']) : ''; ?>">
</div>

<div class="form-group">
<label>NIK</label>
<input type="text" name="nik" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['nik']) : ''; ?>">
</div>

<div class="form-group">
<label>Nomor KK</label>
<input type="text" name="no_kk" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['no_kk']) : ''; ?>">
</div>

<div class="form-group">
<label>Tempat / Tanggal Lahir</label>
<input type="text" name="ttl" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['ttl']) : ''; ?>">
</div>

<div class="form-group">
<label>Jenis Kelamin</label>
<select name="jk" required>
<option value="">-- Pilih --</option>
<option value="Laki-Laki" <?php if($data_edit && $data_edit['jk']=="Laki-Laki") echo "selected"; ?>>Laki-Laki</option>
<option value="Perempuan" <?php if($data_edit && $data_edit['jk']=="Perempuan") echo "selected"; ?>>Perempuan</option>
</select>
</div>

<div class="form-group">
<label>Agama</label>
<select name="agama" required>
<option value="">-- Pilih --</option>
<?php
$daftar_agama = ["Islam","Kristen","Katolik","Hindu","Buddha","Konghucu"];
foreach($daftar_agama as $a){
    $sel = ($data_edit && $data_edit['agama']==$a) ? "selected" : "";
    echo "<option value='$a' $sel>$a</option>";
}
?>
</select>
</div>

<div class="form-group">
<label>Pekerjaan</label>
<input type="text" name="pekerjaan" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['pekerjaan']) : ''; ?>">
</div>

<div class="form-group">
<label>Status Kawin</label>
<select name="status_kawin" required>
<option value="">-- Pilih --</option>
<option value="Belum Kawin" <?php if($data_edit && $data_edit['status_kawin']=="Belum Kawin") echo "selected"; ?>>Belum Kawin</option>
<option value="Kawin" <?php if($data_edit && $data_edit['status_kawin']=="Kawin") echo "selected"; ?>>Kawin</option>
<option value="Cerai Hidup" <?php if($data_edit && $data_edit['status_kawin']=="Cerai Hidup") echo "selected"; ?>>Cerai Hidup</option>
<option value="Cerai Mati" <?php if($data_edit && $data_edit['status_kawin']=="Cerai Mati") echo "selected"; ?>>Cerai Mati</option>
</select>
</div>

<div class="form-group">
<label>Alamat Lengkap</label>
<textarea name="alamat" rows="3"><?php echo $data_edit ? htmlspecialchars($data_edit['alamat']) : ''; ?></textarea>
</div>

<div class="form-group">
<label>RT / RW</label>
<input type="text" name="rt_rw" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['rt_rw']) : ''; ?>">
</div>

<div class="form-group">
<label>Permohonan</label>
<select name="permohonan" required>
<option value="">-- Pilih --</option>
<option value="Baru" <?php if($data_edit && $data_edit['permohonan']=="Baru") echo "selected"; ?>>Baru</option>
<option value="Penggantian" <?php if($data_edit && $data_edit['permohonan']=="Penggantian") echo "selected"; ?>>Penggantian</option>
</select>
</div>

<div class="form-group">
<label>Pas Foto</label>
<div class="foto-wrapper">

<div class="foto-input">
<input type="file" name="foto" id="foto" accept="image/jpeg, image/jpg, image/png" onchange="previewFoto(event)" <?php echo $data_edit ? '' : 'required'; ?>>
<p class="foto-hint">
Format JPG/PNG, maksimal 2MB.
<?php echo $data_edit ? 'Kosongkan jika tidak ingin mengganti foto.' : ''; ?>
</p>
</div>

<div class="foto-preview" id="fotoPreview">
<?php if ($data_edit && !empty($data_edit['foto']) && file_exists($folder_upload . $data_edit['foto'])): ?>
<img src="<?php echo $folder_upload . htmlspecialchars($data_edit['foto']); ?>" alt="Foto saat ini">
<?php else: ?>
<span>Preview foto</span>
<?php endif; ?>
</div>

</div>
</div>

</div>

<?php if($data_edit){ ?>
<button class="btn" type="submit" name="simpan_edit">Simpan Perubahan</button>
<?php } else { ?>
<button class="btn" type="submit" name="tambah">Tambah Data</button>
<?php } ?>

</form>

<br><br>

<h2>📋 Data Pendaftaran E-KTP</h2>

<input type="text" id="cariKtp" placeholder="Cari nama atau NIK..." style="width:100%;padding:12px;margin-bottom:20px;border:1px solid #ccc;border-radius:8px;font-size:15px;" onkeyup="cariData()">

<div style="overflow-x:auto;">
<table id="tabelKtp">

<thead>
<tr>
<th>ID</th>
<th>Foto</th>
<th>Nama</th>
<th>NIK</th>
<th>Desa</th>
<th>Permohonan</th>
<th>Status</th>
<th width="180">Aksi</th>
</tr>
</thead>

<tbody>
<?php while($row = mysqli_fetch_assoc($data_ktp)){ ?>
<tr>
<td align="center"><?php echo $row['id']; ?></td>
<td align="center">
<?php if (!empty($row['foto']) && file_exists($folder_upload . $row['foto'])): ?>
<img class="foto-thumb" src="<?php echo $folder_upload . htmlspecialchars($row['foto']); ?>" alt="Foto">
<?php else: ?>
-
<?php endif; ?>
</td>
<td><?php echo htmlspecialchars($row['nama']); ?></td>
<td><?php echo htmlspecialchars($row['nik']); ?></td>
<td><?php echo htmlspecialchars($row['desa']); ?></td>
<td align="center">
<?php if($row['permohonan']=="Baru"){ ?>
<span class="badge badge-baru">BARU</span>
<?php } else { ?>
<span class="badge badge-penggantian">PENGGANTIAN</span>
<?php } ?>
</td>
<td align="center">
<?php if($row['status_kawin']=="Belum Kawin"){ ?>
<span class="badge badge-belumkawin">BELUM KAWIN</span>
<?php } elseif($row['status_kawin']=="Kawin"){ ?>
<span class="badge badge-kawin">KAWIN</span>
<?php } elseif($row['status_kawin']=="Cerai Hidup"){ ?>
<span class="badge badge-cerdup">CERAI HIDUP</span>
<?php } elseif($row['status_kawin']=="Cerai Mati"){ ?>
<span class="badge badge-cermat">CERAI MATI</span>
<?php } ?>
</td>
<td align="center">
<div class="aksi">
<a class="btn-edit" href="crud_ktp.php?edit=<?php echo $row['id']; ?>">Edit</a>
<a class="btn-hapus" href="crud_ktp.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
</div>
</td>
</tr>
<?php } ?>
</tbody>

</table>
</div>

</div>

</div>

<script>
function cariData(){
var input=document.getElementById("cariKtp");
var filter=input.value.toUpperCase();
var table=document.getElementById("tabelKtp");
var tr=table.getElementsByTagName("tr");
for(var i=1;i<tr.length;i++){
    var tdNama=tr[i].getElementsByTagName("td")[2];
    var tdNik=tr[i].getElementsByTagName("td")[3];
    if(tdNama){
        var nama=(tdNama.textContent||tdNama.innerText).toUpperCase();
        var nik=(tdNik.textContent||tdNik.innerText).toUpperCase();
        if(nama.indexOf(filter)>-1 || nik.indexOf(filter)>-1){
            tr[i].style.display="";
        } else {
            tr[i].style.display="none";
        }
    }
}
}

function previewFoto(event){
var file=event.target.files[0];
var previewBox=document.getElementById('fotoPreview');
if(!file){ return; }

var maxSize=2*1024*1024;
if(file.size>maxSize){
    alert('Ukuran foto maksimal 2MB. Silakan pilih foto lain.');
    event.target.value='';
    return;
}

var reader=new FileReader();
reader.onload=function(e){
    previewBox.innerHTML='<img src="'+e.target.result+'" alt="Preview Foto">';
};
reader.readAsDataURL(file);
}
</script>

</body>
</html>
