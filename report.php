<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$active = 'report';

// Query report pengajuan per bulan
$sql_pengajuan = "
    SELECT 
        DATE_FORMAT(tanggal, '%Y-%m') AS bulan,
        COUNT(*) AS total_pengajuan
    FROM peminjaman_ruangan
    GROUP BY bulan
    ORDER BY bulan DESC
";
$report_pengajuan = $pdo->query($sql_pengajuan)->fetchAll();

// Query report pengembalian per bulan
$sql_pengembalian = "
    SELECT 
        DATE_FORMAT(tanggal_pengembalian, '%Y-%m') AS bulan,
        COUNT(*) AS total_pengembalian
    FROM peminjaman_ruangan
    WHERE status_pengembalian = 'disetujui'
    GROUP BY bulan
    ORDER BY bulan DESC
";
$report_pengembalian = $pdo->query($sql_pengembalian)->fetchAll();

// Rekap peminjaman per ruangan
$sql_ruangan = "SELECT r.nama_ruangan, COUNT(*) as total FROM peminjaman_ruangan pr JOIN ruangan r ON pr.ruangan_id = r.ruangan_id GROUP BY pr.ruangan_id ORDER BY total DESC LIMIT 5";
$rekap_ruangan = $pdo->query($sql_ruangan)->fetchAll();

// Rekap peminjaman per user
$sql_user = "SELECT u.nama_lengkap, COUNT(*) as total FROM peminjaman_ruangan pr JOIN users u ON pr.user_id = u.user_id GROUP BY pr.user_id ORDER BY total DESC LIMIT 5";
$rekap_user = $pdo->query($sql_user)->fetchAll();

// Rekap pengembalian tepat waktu/terlambat
$sql_tepat = "SELECT COUNT(*) FROM peminjaman_ruangan WHERE status_pengembalian='disetujui' AND tanggal_pengembalian <= tanggal";
$tepat_waktu = $pdo->query($sql_tepat)->fetchColumn();
$sql_telat = "SELECT COUNT(*) FROM peminjaman_ruangan WHERE status_pengembalian='disetujui' AND tanggal_pengembalian > tanggal";
$telat = $pdo->query($sql_telat)->fetchColumn();

// Status ruangan
$sql_status = "SELECT nama_ruangan, (SELECT COUNT(*) FROM peminjaman_ruangan WHERE ruangan_id = r.ruangan_id AND status = 'disetujui' AND (status_pengembalian IS NULL OR status_pengembalian != 'disetujui')) as sedang_dipinjam FROM ruangan r ORDER BY nama_ruangan ASC";
$status_ruangan = $pdo->query($sql_status)->fetchAll();

// User aktif
$sql_user_aktif = "SELECT COUNT(*) FROM users WHERE role = 'user'";
$user_aktif = $pdo->query($sql_user_aktif)->fetchColumn();

// Ambil daftar bulan yang ada di data
$bulan_list = $pdo->query("SELECT DISTINCT DATE_FORMAT(tanggal, '%Y-%m') as bulan FROM peminjaman_ruangan ORDER BY bulan DESC")->fetchAll();
$bulan_aktif = $_GET['bulan'] ?? date('Y-m');

// Query data untuk laporan
$sql = "SELECT pr.peminjaman_id, r.nama_ruangan, u.nama_lengkap, pr.tanggal, pr.waktu_mulai, pr.waktu_selesai, pr.tanggal_pengembalian, pr.waktu_pengembalian
        FROM peminjaman_ruangan pr
        JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
        JOIN users u ON pr.user_id = u.user_id
        WHERE DATE_FORMAT(pr.tanggal, '%Y-%m') = ?
        ORDER BY pr.peminjaman_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$bulan_aktif]);
$list = $stmt->fetchAll();

// Hitung statistik
$total_peminjaman = count($list);
$total_dikembalikan = 0;
$total_durasi = 0;
foreach($list as $row) {
    if($row['tanggal_pengembalian']) {
        $total_dikembalikan++;
    }
    $durasi = strtotime($row['waktu_selesai']) - strtotime($row['waktu_mulai']);
    $total_durasi += $durasi;
}
$rata_durasi = $total_peminjaman > 0 ? round(($total_durasi / $total_peminjaman) / 3600, 2) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengajuan & Pengembalian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    .sidebar-link {
        transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    }
    .sidebar-link:hover:not(.bg-purple-50) {
        background: #f3e8ff;
        color: #7c3aed;
        transform: scale(1.04);
        box-shadow: 0 2px 8px 0 rgba(124,58,237,0.08);
    }
    .custom-table th {
        background: #f7fafc;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
    }
    .custom-table td {
        border-bottom: 1px solid #f1f1f1;
    }
    .custom-table tr:hover td {
        background: #f9fafb;
    }
    .badge-status {
        display: inline-block;
        padding: 2px 12px;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .badge-menunggu { background: #fef3c7; color: #b45309; }
    .badge-disetujui { background: #d1fae5; color: #065f46; }
    .badge-ditolak { background: #e0e7ff; color: #3730a3; }
    .badge-pengembalian { background: #f3e8ff; color: #7c3aed; }
    
    /* CSS untuk Print */
    @media print {
        body {
            background: white !important;
            font-size: 12pt;
            line-height: 1.4;
        }
        
        .no-print {
            display: none !important;
        }
        
        .print-only {
            display: block !important;
        }
        
        .sidebar {
            display: none !important;
        }
        
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        
        .container {
            box-shadow: none !important;
            border: none !important;
            margin: 0 !important;
            padding: 20pt !important;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30pt;
            border-bottom: 2pt solid #333;
            padding-bottom: 20pt;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15pt;
            margin-bottom: 30pt;
        }
        
        .stat-card {
            border: 1pt solid #333;
            padding: 15pt;
            text-align: center;
            background: #f8f9fa !important;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        
        th, td {
            border: 1pt solid #333;
            padding: 8pt;
            text-align: left;
        }
        
        th {
            background: #f0f0f0 !important;
            font-weight: bold;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
    }
    
    .print-only {
        display: none;
    }
    @media (max-width: 768px) {
        .sidebar {
            display: none;
        }
        .hamburger {
            display: block;
        }
        .text-xl {
            font-size: 1rem;
        }
        .w-5 {
            width: 1rem;
        }
        .h-8 {
            height: 1.5rem;
        }
        .flex-1 {
            padding: 4px;
        }
        .p-6 {
            padding: 4px;
        }
        .mb-6 {
            margin-bottom: 4px;
        }
        .grid-cols-1 {
            grid-template-columns: 1fr;
        }
        .flex.justify-between.items-center.mb-6 {
            flex-direction: column;
        }
    }
    @media (min-width: 769px) {
        .hamburger {
            display: none;
        }
    }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Print Header (hanya muncul saat print) -->
    <div class="print-only print-header">
        <h1 style="font-size: 24pt; margin-bottom: 10pt;">LAPORAN PEMINJAMAN RUANGAN</h1>
        <p style="font-size: 14pt;">Periode: <?= date('F Y', strtotime($bulan_aktif.'-01')) ?></p>
        <p style="font-size: 12pt;">Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <!-- Navbar -->
    <nav class="bg-white shadow-sm no-print">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-purple-600">Hallo admin 👋😎</h1>
                    </div>
                </div>
                <div class="flex items-center">
                    <button class="hamburger p-2 rounded-md text-gray-600 hover:text-purple-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-purple-500" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                    </button>
                    <div class="ml-3 relative">
                        <div class="flex items-center">
                            <span class="mr-2 text-gray-700">Hai, <?php echo htmlspecialchars($_SESSION["username"] ?? "Admin"); ?></span>
                            <button class="bg-gray-800 flex text-sm rounded-full focus:outline-none" id="user-menu-button">
                                <img class="h-8 w-8 rounded-full object-cover" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION["username"] ?? "Admin"); ?>&background=9333EA&color=fff" alt="Profile">
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md min-h-screen sidebar no-print">
            <div class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard_admin.php" class="sidebar-link block px-4 py-2.5 rounded transition flex items-center<?php if($active=='dashboard') echo ' bg-purple-50 border-l-4 border-purple-600 text-purple-600'; else echo ' text-gray-600 hover:text-purple-800'; ?>">
                            <i class="fas fa-tachometer-alt w-5 mr-2 inline-block"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="sidebar-link block px-4 py-2.5 rounded transition flex items-center<?php if($active=='users') echo ' bg-purple-50 border-l-4 border-purple-600 text-purple-600'; else echo ' text-gray-600 hover:text-purple-800'; ?>">
                            <i class="fas fa-users w-5 mr-2 inline-block"></i>
                            Pengguna
                        </a>
                    </li>
                    <li>
                        <a href="ruangan.php" class="sidebar-link block px-4 py-2.5 rounded transition flex items-center<?php if($active=='ruangan') echo ' bg-purple-50 border-l-4 border-purple-600 text-purple-600'; else echo ' text-gray-600 hover:text-purple-800'; ?>">
                            <i class="fas fa-door-open w-5 mr-2 inline-block"></i>
                            Ruangan
                        </a>
                    </li>
                    <li>
                        <a href="peminjaman.php" class="sidebar-link block px-4 py-2.5 rounded transition flex items-center<?php if($active=='peminjaman') echo ' bg-purple-50 border-l-4 border-purple-600 text-purple-600'; else echo ' text-gray-600 hover:text-purple-800'; ?>">
                            <i class="fas fa-calendar-alt w-5 mr-2 inline-block"></i>
                            Peminjaman
                        </a>
                    </li>
                    <li>
                        <a href="pengembalian_admin.php" class="sidebar-link block px-4 py-2.5 rounded transition flex items-center<?php if($active=='pengembalian') echo ' bg-purple-50 border-l-4 border-purple-600 text-purple-600'; else echo ' text-gray-600 hover:text-purple-800'; ?>">
                            <i class="fas fa-undo w-5 mr-2 inline-block"></i>
                            Pengembalian Ruangan
                        </a>
                    </li>
                    <li>
                        <a href="report.php" class="sidebar-link block px-4 py-2.5 rounded transition flex items-center<?php if($active=='report') echo ' bg-purple-50 border-l-4 border-purple-600 text-purple-600'; else echo ' text-gray-600 hover:text-purple-800'; ?>">
                            <i class="fas fa-chart-bar w-5 mr-2 inline-block"></i>
                            Laporan
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" class="sidebar-link block px-4 py-2.5 rounded transition flex items-center text-gray-600 hover:text-purple-800">
                            <i class="fas fa-sign-out-alt w-5 mr-2 inline-block"></i>
                            Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="flex-1 p-8 main-content">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 no-print">Report data Peminjaman dan Pengembalian</h2>
            
            <!-- Statistik Ringkasan (muncul di print) -->
            <div class="print-only stats-grid">
                <div class="stat-card">
                    <div style="font-size: 18pt; font-weight: bold; margin-bottom: 8pt;"><?= $total_peminjaman ?></div>
                    <div>Total Peminjaman</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 18pt; font-weight: bold; margin-bottom: 8pt;"><?= $total_dikembalikan ?></div>
                    <div>Dikembalikan</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 18pt; font-weight: bold; margin-bottom: 8pt;"><?= $total_peminjaman - $total_dikembalikan ?></div>
                    <div>Belum Kembali</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 18pt; font-weight: bold; margin-bottom: 8pt;"><?= $rata_durasi ?> jam</div>
                    <div>Rata-rata Durasi</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 container">
                <form method="get" class="mb-4 flex items-center gap-2 no-print">
                    <label for="bulan" class="font-medium text-gray-700">Pilih Bulan:</label>
                    <select name="bulan" id="bulan" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-400">
                        <?php foreach($bulan_list as $b): ?>
                            <option value="<?= $b['bulan'] ?>" <?= $b['bulan']==$bulan_aktif?'selected':'' ?>><?= date('F Y', strtotime($b['bulan'].'-01')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded">Tampilkan</button>
                    <button type="button" onclick="printReport()" class="ml-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                </form>
                
                <div class="no-break">
                    <h3 class="text-lg font-semibold mb-4 print-only" style="margin-top: 20pt;">Detail Peminjaman Ruangan - <?= date('F Y', strtotime($bulan_aktif.'-01')) ?></h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 custom-table">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengembalian</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $no = 1;
                            foreach($list as $row):
                                $durasi = strtotime($row['waktu_selesai']) - strtotime($row['waktu_mulai']);
                                $durasi_jam = round($durasi/3600, 2);
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= $no++ ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['nama_ruangan']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= $row['waktu_mulai'] ?> - <?= $row['waktu_selesai'] ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= $durasi_jam ?> jam</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php if($row['tanggal_pengembalian']): ?>
                                            <?= date('d/m/Y', strtotime($row['tanggal_pengembalian'])) ?> <?= $row['waktu_pengembalian'] ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">Belum dikembalikan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Footer untuk print -->
                <div class="print-only" style="margin-top: 40pt; text-align: center; font-size: 10pt; color: #666;">
                    <p>--- Akhir Laporan ---</p>
                    <p>Dicetak oleh: <?= htmlspecialchars($_SESSION["username"] ?? "Admin") ?> | <?= date('d/m/Y H:i:s') ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printReport() {
            // Simpan judul halaman asli
            const originalTitle = document.title;
            
            // Ubah judul untuk print
            document.title = 'Laporan Peminjaman - <?= date("F Y", strtotime($bulan_aktif."-01")) ?>';
            
            // Tampilkan loading state
            const printBtn = document.querySelector('button[onclick="printReport()"]');
            const originalBtnText = printBtn.innerHTML;
            printBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyiapkan...';
            printBtn.disabled = true;
            
            // Delay singkat untuk memastikan semua style terapply
            setTimeout(() => {
                // Trigger print dialog
                window.print();
                
                // Kembalikan state button
                printBtn.innerHTML = originalBtnText;
                printBtn.disabled = false;
                
                // Kembalikan judul asli
                document.title = originalTitle;
            }, 500);
        }
        
        // Event listener untuk print events
        window.addEventListener('beforeprint', function() {
            console.log('Print dialog dibuka');
        });
        
        window.addEventListener('afterprint', function() {
            console.log('Print dialog ditutup');
        });
        
        // Keyboard shortcut Ctrl+P
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printReport();
            }
        });

        document.querySelector('.hamburger').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        });
    </script>
</body>
</html> 