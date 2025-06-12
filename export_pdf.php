<?php
require 'config.php';
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$bulan = $_GET['bulan'] ?? date('Y-m');

// Ambil data laporan dari database
$sql = "SELECT pr.peminjaman_id, r.nama_ruangan, u.nama_lengkap, pr.tanggal, pr.waktu_mulai, pr.waktu_selesai, pr.tanggal_pengembalian, pr.waktu_pengembalian
        FROM peminjaman_ruangan pr
        JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
        JOIN users u ON pr.user_id = u.user_id
        WHERE DATE_FORMAT(pr.tanggal, '%Y-%m') = ?
        ORDER BY pr.peminjaman_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$bulan]);
$list = $stmt->fetchAll();

// Buat konten HTML
$htmlContent = '<html><head><title>Laporan</title></head><body>';
$htmlContent .= '<h1>Laporan Bulan ' . date('F Y', strtotime($bulan . '-01')) . '</h1>';
$htmlContent .= '<table border="1"><tr><th>No</th><th>Ruangan</th><th>Peminjam</th><th>Tanggal</th><th>Waktu</th><th>Durasi</th><th>Pengembalian</th></tr>';

$no = 1;
foreach ($list as $row) {
    $durasi = strtotime($row['waktu_selesai']) - strtotime($row['waktu_mulai']);
    $durasi_jam = round($durasi/3600, 2) . ' jam';
    $pengembalian = $row['tanggal_pengembalian'] ? date('d/m/Y', strtotime($row['tanggal_pengembalian'])) . ' ' . $row['waktu_pengembalian'] : '-';
    
    $htmlContent .= '<tr>';
    $htmlContent .= '<td>' . $no++ . '</td>';
    $htmlContent .= '<td>' . htmlspecialchars($row['nama_ruangan']) . '</td>';
    $htmlContent .= '<td>' . htmlspecialchars($row['nama_lengkap']) . '</td>';
    $htmlContent .= '<td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>';
    $htmlContent .= '<td>' . $row['waktu_mulai'] . ' - ' . $row['waktu_selesai'] . '</td>';
    $htmlContent .= '<td>' . $durasi_jam . '</td>';
    $htmlContent .= '<td>' . $pengembalian . '</td>';
    $htmlContent .= '</tr>';
}

$htmlContent .= '</table></body></html>';

// Simpan konten HTML ke file
file_put_contents('report.html', $htmlContent);

// Konversi HTML ke PDF
exec("wkhtmltopdf report.html report.pdf");

// Berikan tautan untuk mengunduh PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="report.pdf"');
readfile('report.pdf');
?> 