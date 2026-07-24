<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['login'])) {
    header("location: proses_login.php");
    exit();
}

$pesan = "";

// Helper: kalau prepare()/query() gagal, tampilkan pesan jelas, bukan blank.
function cekPrepare($stmt, $koneksi) {
    if ($stmt === false) {
        die("<div style='font-family:sans-serif;padding:20px;background:#fee;border:2px solid #c00;margin:20px;border-radius:8px;'>
                <b>Query error:</b> " . mysqli_error($koneksi) . "
             </div>");
    }
}

if (isset($_POST['tambah'])) {
    $nama_komoditas = trim($_POST['nama_komoditas']);
    $kategori       = trim($_POST['kategori']);
    $desa           = trim($_POST['desa']);
    $luas_lahan     = trim($_POST['luas_lahan']);
    $hasil_panen    = trim($_POST['hasil_panen']);
    $satuan         = trim($_POST['satuan']);
    $tahun          = trim($_POST['tahun']);
    $keterangan     = trim($_POST['keterangan']);

    $query = "INSERT INTO komoditas (nama_komoditas,kategori,desa,luas_lahan,hasil_panen,satuan,tahun,keterangan) VALUES (?,?,?,?,?,?,?,?)";
    $stmt = mysqli_prepare($koneksi, $query);
    cekPrepare($stmt, $koneksi);
    mysqli_stmt_bind_param(
        $stmt, "sssddsis",
        $nama_komoditas, $kategori, $desa, $luas_lahan, $hasil_panen, $satuan, $tahun, $keterangan
    );

    $pesan = mysqli_stmt_execute($stmt)
        ? "Data komoditas berhasil ditambahkan."
        : "Gagal menambahkan data.";
}

if (isset($_POST['simpan_edit'])) {
    $id             = $_POST['id'];
    $nama_komoditas = trim($_POST['nama_komoditas']);
    $kategori       = trim($_POST['kategori']);
    $desa           = trim($_POST['desa']);
    $luas_lahan     = trim($_POST['luas_lahan']);
    $hasil_panen    = trim($_POST['hasil_panen']);
    $satuan         = trim($_POST['satuan']);
    $tahun          = trim($_POST['tahun']);
    $keterangan     = trim($_POST['keterangan']);

    $query = "UPDATE komoditas SET nama_komoditas=?,kategori=?,desa=?,luas_lahan=?,hasil_panen=?,satuan=?,tahun=?,keterangan=? WHERE id=?";
    $stmt = mysqli_prepare($koneksi, $query);
    cekPrepare($stmt, $koneksi);
    mysqli_stmt_bind_param(
        $stmt, "sssddsisi",
        $nama_komoditas, $kategori, $desa, $luas_lahan, $hasil_panen, $satuan, $tahun, $keterangan, $id
    );

    $pesan = mysqli_stmt_execute($stmt)
        ? "Data berhasil diperbarui."
        : "Gagal memperbarui data.";
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $stmt = mysqli_prepare($koneksi, "DELETE FROM komoditas WHERE id=?");
    cekPrepare($stmt, $koneksi);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: crud_komoditas.php");
    exit();
}

$data_edit = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM komoditas WHERE id=?");
    cekPrepare($stmt, $koneksi);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data_edit = mysqli_fetch_assoc($hasil);
}

$data_komoditas = mysqli_query($koneksi, "SELECT * FROM komoditas ORDER BY id ASC");
if ($data_komoditas === false) {
    die("<div style='font-family:sans-serif;padding:20px;background:#fee;border:2px solid #c00;margin:20px;border-radius:8px;'>
            <b>Query error (SELECT):</b> " . mysqli_error($koneksi) . "
         </div>");
}

?>

<html>

<head>

<title>Kelola Data Komoditas</title>

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
font-size:13px;
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
padding:12px;
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
border-radius:20px;
font-size:13px;
color:white;
}

.badge-pertanian{ background:#198754; }
.badge-perkebunan{ background:#0d6efd; }
.badge-hortikultura{ background:#fd7e14; }

.btn-edit,
.btn-hapus{
display:inline-block;
padding:8px 15px;
border-radius:6px;
text-decoration:none;
font-size:14px;
font-weight:600;
text-align:center;
min-width:80px;
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

@media(max-width:600px){
.container{ width:98%; }
table{ font-size:13px; }
}

</style>

</head>

<body>

<center>

<h1 style="font-size:40px;font-weight:800;color:#000;margin-bottom:5px;margin-top:40px;">
KELOLA DATA KOMODITAS
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
<?php echo $data_edit ? "Edit Komoditas" : "Tambah Komoditas"; ?>
</h2>

<?php if($pesan!=""){ echo "<div class='pesan'>$pesan</div>"; } ?>

<form method="POST">

<?php if($data_edit){ ?>
<input type="hidden" name="id" value="<?php echo $data_edit['id']; ?>">
<?php } ?>

<div class="form-grid">

<div class="form-group">
<label>Nama Komoditas</label>
<input type="text" name="nama_komoditas" required value="<?php echo $data_edit ? htmlspecialchars($data_edit['nama_komoditas']) : ''; ?>">
</div>

<div class="form-group">
<label>Kategori</label>
<select name="kategori" required>
<option value="">Pilih Kategori</option>
<option value="Pertanian" <?php if($data_edit && $data_edit['kategori']=="Pertanian") echo "selected"; ?>>Pertanian</option>
<option value="Perkebunan" <?php if($data_edit && $data_edit['kategori']=="Perkebunan") echo "selected"; ?>>Perkebunan</option>
<option value="Hortikultura" <?php if($data_edit && $data_edit['kategori']=="Hortikultura") echo "selected"; ?>>Hortikultura</option>
</select>
</div>

<div class="form-group">
<label>Desa</label>
<select name="desa" required>
<option value="">Pilih Desa</option>
<?php
$daftar_desa = ["Jalancagak","Bunihayu","Tambakan","Curugrendeng","Sarireja","Kumpay","Mayang"];
foreach($daftar_desa as $d){
    $sel = ($data_edit && $data_edit['desa']==$d) ? "selected" : "";
    echo "<option value='$d' $sel>$d</option>";
}
?>
</select>
</div>

<div class="form-group">
<label>Luas Lahan (Ha)</label>
<input type="number" step="0.01" name="luas_lahan" required value="<?php echo $data_edit ? $data_edit['luas_lahan'] : ''; ?>">
</div>

<div class="form-group">
<label>Hasil Panen</label>
<input type="number" step="0.01" name="hasil_panen" required value="<?php echo $data_edit ? $data_edit['hasil_panen'] : ''; ?>">
</div>

<div class="form-group">
<label>Satuan</label>
<select name="satuan" required>
<option value="">Pilih Satuan</option>
<option value="Ton" <?php if($data_edit && $data_edit['satuan']=="Ton") echo "selected"; ?>>Ton</option>
<option value="Kg" <?php if($data_edit && $data_edit['satuan']=="Kg") echo "selected"; ?>>Kg</option>
<option value="Kwintal" <?php if($data_edit && $data_edit['satuan']=="Kwintal") echo "selected"; ?>>Kwintal</option>
</select>
</div>

<div class="form-group">
<label>Tahun</label>
<input type="number" name="tahun" min="2020" max="2035" required value="<?php echo $data_edit ? $data_edit['tahun'] : date('Y'); ?>">
</div>

<div class="form-group">
<label>Keterangan</label>
<textarea name="keterangan" rows="3"><?php echo $data_edit ? htmlspecialchars($data_edit['keterangan']) : ''; ?></textarea>
</div>

</div>

<?php if($data_edit){ ?>
<button class="btn" type="submit" name="simpan_edit">Simpan Perubahan</button>
<?php } else { ?>
<button class="btn" type="submit" name="tambah">Tambah Komoditas</button>
<?php } ?>

</form>

<br><br>

<h2>📋 Data Komoditas Kecamatan</h2>

<input type="text" id="cariKomoditas" placeholder="Cari nama komoditas atau desa..." style="width:100%;padding:12px;margin-bottom:20px;border:1px solid #ccc;border-radius:8px;font-size:15px;" onkeyup="cariData()">

<div style="overflow-x:auto;">
<table id="tabelKomoditas">

<thead>
<tr>
<th>ID</th>
<th>Komoditas</th>
<th>Kategori</th>
<th>Desa</th>
<th>Luas Lahan</th>
<th>Hasil</th>
<th>Tahun</th>
<th width="180">Aksi</th>
</tr>
</thead>

<tbody>
<?php while($row = mysqli_fetch_assoc($data_komoditas)){ ?>
<tr>
<td align="center"><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['nama_komoditas']); ?></td>
<td align="center">
<?php
if($row['kategori']=="Pertanian"){
?>
<span class="badge badge-pertanian">Pertanian</span>
<?php
} elseif($row['kategori']=="Perkebunan"){
?>
<span class="badge badge-perkebunan">Perkebunan</span>
<?php
} else {
?>
<span class="badge badge-hortikultura">Hortikultura</span>
<?php } ?>
</td>
<td><?php echo htmlspecialchars($row['desa']); ?></td>
<td align="center"><?php echo $row['luas_lahan']; ?> Ha</td>
<td align="center"><?php echo $row['hasil_panen']." ".$row['satuan']; ?></td>
<td align="center"><?php echo $row['tahun']; ?></td>
<td align="center">
<div class="aksi">
<a class="btn-edit" href="crud_komoditas.php?edit=<?php echo $row['id']; ?>">Edit</a>
<a class="btn-hapus" href="crud_komoditas.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
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
var input=document.getElementById("cariKomoditas");
var filter=input.value.toUpperCase();
var table=document.getElementById("tabelKomoditas");
var tr=table.getElementsByTagName("tr");
for(var i=1;i<tr.length;i++){
    var tdNama=tr[i].getElementsByTagName("td")[1];
    var tdDesa=tr[i].getElementsByTagName("td")[3];
    if(tdNama){
        var nama=(tdNama.textContent||tdNama.innerText).toUpperCase();
        var desa=(tdDesa.textContent||tdDesa.innerText).toUpperCase();
        if(nama.indexOf(filter)>-1 || desa.indexOf(filter)>-1){
            tr[i].style.display="";
        } else {
            tr[i].style.display="none";
        }
    }
}
}
</script>

</body>
</html>