<?php
session_start();
require_once "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == "" || $password == "") {
        echo "<script>alert('Username dan Password masih kosong!');</script>";
    } else {

        $query = "SELECT * FROM user WHERE username=? AND password=?";
        $stmt = mysqli_prepare($koneksi, $query);

        mysqli_stmt_bind_param($stmt,"ss",$username,$password);
        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($hasil)==1){

            $data = mysqli_fetch_assoc($hasil);

            $_SESSION['login']=true;
            $_SESSION['id']=$data['id'];
            $_SESSION['username']=$data['username'];
            $_SESSION['status']=$data['status'];

            header("Location: submit_formlogin.php");
            exit();

        }else{
            echo "<script>alert('Username atau Password Salah!');</script>";
        }

    }

}
?>

<html>
<head>

<title>Login Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}
body{
    background:#eef2f5;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.login-box{
    width:380px;
    background:white;
    padding:35px;
    border-radius:10px;
    box-shadow:0px 5px 20px rgba(0,0,0,.15);
}
.login-box h2{
    text-align:center;
    margin-bottom:25px;
}
.login-box label{
    display:block;
    margin-bottom:5px;
    color:#555;
}
.login-box input{
    width:100%;
    padding:10px;
    margin-bottom:18px;
    border:1px solid #ccc;
    border-radius:5px;
}
.login-box input:focus{
    outline:none;
    border:1px solid #087500;
}
.login-box button{
    width:100%;
    padding:12px;
    background: #087500;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:15px;
}
.login-box button:hover{

    background: #063B00;
}
</style>
</head>

<body>
<div class="login-box">
<h2>LOGIN</h2>

<form method="POST">
<label>Username</label>
<input type="text" name="username" placeholder="Masukkan Username" required>
<label>Password</label>
<input type="password" name="password" placeholder="Masukkan Password" required>
<button type="submit">
LOGIN
</button>
</form>

<!--
<span style="font-size:12px;">Belum memiliki akun?</span>&nbsp;
<a href="proses_signup.php" style="font-size:12px; color: #087500;">Sign Up</a> 
-->
</div>
</body>
</html>