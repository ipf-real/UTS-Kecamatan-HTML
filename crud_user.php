<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['login'])) {
    header("location: proses_login.php");
    exit();
}

$pesan = "";

if (isset($_POST['tambah'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $status   = trim($_POST['status']);

    $query = "INSERT INTO user (username,password,status) VALUES (?,?,?)";
    $stmt = mysqli_prepare($koneksi,$query);
    mysqli_stmt_bind_param($stmt,"sss",$username,$password,$status);

    $pesan = mysqli_stmt_execute($stmt)
        ? "User berhasil ditambahkan."
        : "Gagal menambahkan user.";
}

if (isset($_POST['simpan_edit'])) {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $status = trim($_POST['status']);

    $query="UPDATE user SET username=?,password=?,status=? WHERE id=?";
    $stmt=mysqli_prepare($koneksi,$query);
    mysqli_stmt_bind_param($stmt,"sssi",$username,$password,$status,$id);

    $pesan=mysqli_stmt_execute($stmt)
        ? "Data berhasil diperbarui."
        : "Gagal memperbarui data.";
}

if(isset($_GET['hapus'])){
    $id=$_GET['hapus'];

    $stmt=mysqli_prepare($koneksi,"DELETE FROM user WHERE id=?");
    mysqli_stmt_bind_param($stmt,"i",$id);
    mysqli_stmt_execute($stmt);

    header("Location: crud_user.php");
    exit();
}

$data_edit=null;

if(isset($_GET['edit'])){

    $id=$_GET['edit'];

    $stmt=mysqli_prepare($koneksi,"SELECT * FROM user WHERE id=?");
    mysqli_stmt_bind_param($stmt,"i",$id);
    mysqli_stmt_execute($stmt);

    $hasil=mysqli_stmt_get_result($stmt);

    $data_edit=mysqli_fetch_assoc($hasil);

}

$semua_user=mysqli_query($koneksi,"SELECT * FROM user ORDER BY id ASC");

?>

<html>
<head>
<title>Kelola User</title>
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
.subjudul{
font-size:15px;
font-weight:400;
margin-top:5px;
}
.container{
width:900px;
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
.form-group{
margin-bottom:18px;
}
.form-group label{
display:block;
margin-bottom:6px;
font-weight:600;
color:#444;
}
.form-group input,
.form-group select{
width:100%;
padding:12px;
border:1px solid #ccc;
border-radius:8px;
font-size:15px;
}
.form-group input:focus,
.form-group select:focus{
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

}

.btn:hover{

background:#065900;

}

</style>

</head>

<body>

<center>

<h1 style="
font-size:40px;
font-weight:800;
color:#000000;
margin-bottom:5px;
margin-top: 40px;
">
KELOLA USER
</h1>

<p style="font-size:16px; color:gray; margin-top:-15px;">
Kecamatan Jalancagak, Kab.Subang, Jawa Barat
</p>

</center>

<div class="container">

<a href="submit_formlogin.php" class="kembali">
← Kembali ke Dashboard
</a>

<div class="card">

<h2>

<?php
echo $data_edit ? "Edit User" : "Tambah User";
?>

</h2>

<?php
if($pesan!=""){
echo "<div class='pesan'>$pesan</div>";
}
?>

<form method="POST">

<?php
if($data_edit){
?>

<input type="hidden" name="id"
value="<?php echo $data_edit['id']; ?>">

<?php
}
?>

<div class="form-group">

<label>Username</label>

<input
type="text"
name="username"
required
value="<?php echo $data_edit ? htmlspecialchars($data_edit['username']) : ''; ?>">

</div>

<div class="form-group">

<label>Password</label>

<input
type="text"
name="password"
required
value="<?php echo $data_edit ? htmlspecialchars($data_edit['password']) : ''; ?>">

</div>

<div class="form-group">

<label>Status</label>

<select name="status">

<option value="admin"
<?php
if($data_edit && $data_edit['status']=="admin")
echo "selected";
?>
>
ADMIN
</option>

<option value="user"
<?php
if($data_edit && $data_edit['status']=="user")
echo "selected";
?>
>
USER
</option>

</select>

</div>

<?php
if($data_edit){
?>

<button
class="btn"
type="submit"
name="simpan_edit">

Simpan Perubahan

</button>

<?php
}else{
?>

<button
class="btn"
type="submit"
name="tambah">

Tambah User

</button>

<?php
}
?>

</form>
<br><br>

<h2>📋 Daftar User</h2>

<input
type="text"
id="cariUser"
placeholder="Cari username..."
style="
width:100%;
padding:12px;
margin-bottom:20px;
border:1px solid #ccc;
border-radius:8px;
font-size:15px;"
onkeyup="cariData()">


<table id="tabelUser">

<thead>

<tr>

<th>ID</th>
<th>Username</th>
<th>Status</th>
<th width="180">Aksi</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($semua_user)){ ?>

<tr>

<td align="center">
<?php echo $row['id']; ?>
</td>

<td>
<?php echo htmlspecialchars($row['username']); ?>
</td>

<td align="center">

<?php
if(strtolower($row['status'])=="admin"){
?>
<span class="badge-admin">ADMIN</span>
<?php
}else{
?>
<span class="badge-user">USER</span>
<?php
}
?>

</td>

<td align="center">

<div class="aksi">

<a
class="btn-edit"
href="crud_user.php?edit=<?php echo $row['id']; ?>">
Edit
</a>

<a
class="btn-hapus"
href="crud_user.php?hapus=<?php echo $row['id']; ?>"
onclick="return confirm('Yakin ingin menghapus user ini?');">
Hapus
</a>

</div>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<style>

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

.badge-admin{

background:#0d6efd;
color:white;
padding:5px 12px;
border-radius:20px;
font-size:13px;

}

.badge-user{

background: #B0BA99;
color:white;
padding:5px 12px;
border-radius:20px;
font-size:13px;

}

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

th:last-child,
td:last-child{
width:220px;
text-align:center;
white-space:nowrap;
}

@media(max-width:600px){
.container{
    width:95%;
    }
table{
    font-size:13px;
    }
}
</style>

<script>
function cariData(){
var input=document.getElementById("cariUser");
var filter=input.value.toUpperCase();
var table=document.getElementById("tabelUser");
var tr=table.getElementsByTagName("tr");
    for(var i=1;i<tr.length;i++){
        var td=tr[i].getElementsByTagName("td")[1];
    if(td){
        var txt=td.textContent || td.innerText;
    if(txt.toUpperCase().indexOf(filter)>-1){
        tr[i].style.display="";
}   else{
        tr[i].style.display="none";
}
        }
    }
}
</script>
</body>
</html>