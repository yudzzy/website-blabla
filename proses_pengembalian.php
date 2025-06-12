<?php
require 'config.php';
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$peminjaman_id = $_POST['peminjaman_id'];
$tanggal_pengembalian = $_POST['tanggal_pengembalian'];
$waktu_pengembalian = $_POST['waktu_pengembalian'];
$keterangan = $_POST['keterangan'];

$sql = "UPDATE peminjaman_ruangan SET tanggal_pengembalian=?, waktu_pengembalian=?, status_pengembalian='menunggu', keterangan=? WHERE peminjaman_id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tanggal_pengembalian, $waktu_pengembalian, $keterangan, $peminjaman_id]);

header('Location: dashboard_user.php?msg=pengembalian_diajukan');
exit; 