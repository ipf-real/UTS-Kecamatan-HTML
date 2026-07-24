<?php
session_start();

if ((isset($_GET['aksi'])) && ($_GET['aksi'] == "logout")) {
    session_destroy();
    header("location: proses_login.php");
    exit();
}

if (!isset($_SESSION['login'])) {
    header("location: proses_login.php");
    exit();
}

$nama   = $_SESSION['username'];
$status = $_SESSION['status'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<title>Dashboard Admin - Kecamatan Jalancagak</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',Arial,sans-serif;
}

body{
    background:#eef2f5;
}

/* HEADER */

.header{
    width:100%;
    height:180px;
    background:url('image/header.jpg') center center;
    background-size:cover;
}

/* DASHBOARD */

.container{
    width:800px;
    margin:0 auto;
    position:relative;
    top:-120px;
    background:#fff;
    border-radius:12px;
    padding:35px;
    box-shadow:0 5px 20px rgba(0,0,0,.15);
}

.container h2{
    color:#2c3e50;
    margin-bottom:8px;
}

.container p{
    color:#666;
    margin-bottom:25px;
}

/* MENU */

.menu{
    display:flex;
    flex-direction:column;
    gap:15px;
}

.menu a{
    text-decoration:none;
    color:white;
    background:#087500;
    padding:15px;
    border-radius:8px;
    font-size:18px;
    font-weight:bold;
    transition:.3s;
}

.menu a:hover{
    background:#063B00;
    transform:translateY(-2px);
}

/* LOGOUT */

.logout{
    margin-top:30px;
    text-align:center;
}

.logout a{
    display:inline-block;
    text-decoration:none;
    background:#dc3545;
    color:white;
    padding:12px 35px;
    border-radius:8px;
    font-weight:bold;
    transition:.3s;
}

.logout a:hover{
    background:#bb2d3b;
}

/* FOOTER */

.footer{
    text-align:center;
    color:#777;
    margin-top:20px;
    font-size:14px;
}

</style>

</head>

<body>

<div class="header"></div>

<div class="container">

<h2>
Selamat Datang,
<?php echo htmlspecialchars($nama); ?>
</h2>

<p>
Status Login :
<b><?php echo htmlspecialchars($status); ?></b>
</p>

<div class="menu">

<a href="crud_user.php">
Kelola User
</a>
<!--
<a href="crud_desa.php">
Kelola Data Desa
</a>

<a href="crud_potensi_desa.php">
Kelola Potensi Desa
</a>
-->
<a href="crud_ktp.php">
Kelola Data KTP
</a>

<a href="crud_komoditas.php">
Kelola Komoditas
</a>
</div>

<div class="logout">

<a href="submit_formlogin.php?aksi=logout">
Logout
</a>

</div>

<div class="footer">
Kecamatan Jalancagak © 2026
</div>

</div>

</body>
</html>