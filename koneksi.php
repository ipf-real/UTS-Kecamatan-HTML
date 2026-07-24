<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = "localhost";//hostname mysql infinityfree: sql108.infinityfree.com
$db_user = "root";//username mysql infinityfree: if0_42414187
$db_pass = "";//password mysql database infinityfree: frmlgn123
$db_name = "labphp"; //connection mysql database infintyfree: if0_42414187_labphp

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($koneksi, "utf8mb4");
?>