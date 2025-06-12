<?php
require 'config.php';
$id = $_GET['id'];
$aksi = $_GET['aksi'];
$status = $aksi == 'approve' ? 'disetujui' : 'ditolak';
$pdo->prepare("UPDATE peminjaman_ruangan SET status_pengembalian=? WHERE peminjaman_id=?")
    ->execute([$status, $id]);
header('Location: pengembalian_admin.php');
exit; 