<?php
// Inisialisasi sesi
session_start();

// Cek apakah user sudah login, jika tidak maka redirect ke halaman login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include file konfigurasi database
require_once "config.php";

// Query untuk mengambil data statistik
$sql_stats = "SELECT 
    COUNT(*) as total_peminjaman,
    SUM(CASE WHEN status = 'disetujui' AND (status_pengembalian IS NULL OR status_pengembalian != 'disetujui') THEN 1 ELSE 0 END) as peminjaman_aktif,
    SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as peminjaman_tertunda,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as peminjaman_ditolak
FROM peminjaman_ruangan";

$stats = $pdo->query($sql_stats)->fetch(PDO::FETCH_ASSOC);

// Query untuk mengambil jumlah total ruangan dari tabel ruangan
$sql_total_ruangan = "SELECT COUNT(*) as total_ruangan FROM ruangan";
$total_ruangan = $pdo->query($sql_total_ruangan)->fetch(PDO::FETCH_ASSOC)['total_ruangan'] ?? 0;

// Query untuk mengambil data peminjaman terbaru (termasuk yang sudah dikembalikan)
$sql_peminjaman = "SELECT 
    pr.peminjaman_id,
    pr.ruangan_id,
    pr.user_id,
    pr.tanggal,
    pr.waktu_mulai,
    pr.waktu_selesai,
    pr.durasi_pinjam,
    pr.status,
    pr.status_pengembalian,
    u.nama_lengkap as peminjam,
    r.nama_ruangan
FROM peminjaman_ruangan pr
LEFT JOIN users u ON pr.user_id = u.user_id
LEFT JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
ORDER BY pr.peminjaman_id DESC
LIMIT 10";

try {
    $peminjaman_list = $pdo->query($sql_peminjaman)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tampilkan pesan error yang lebih user-friendly
    $peminjaman_list = [];
    error_log("Database Error: " . $e->getMessage());
}

// Mengatur variabel statistik
$total_peminjaman = $stats['total_peminjaman'] ?? 0;
$peminjaman_aktif = $stats['peminjaman_aktif'] ?? 0;
$peminjaman_tertunda = $stats['peminjaman_tertunda'] ?? 0;
$peminjaman_ditolak = $stats['peminjaman_ditolak'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar {
            min-height: calc(100vh - 64px);
            position: sticky;
            top: 0;
        }
        
        .sidebar-link {
            transition: all 0.2s;
        }
        
        .sidebar-link:hover {
            background-color: rgba(168, 85, 247, 0.1);
            border-left: 4px solid #9333EA;
        }
        
        .sidebar-link.active {
            background-color: rgba(168, 85, 247, 0.1);
            border-left: 4px solid #9333EA;
            color: #9333EA;
        }
        
        .gradient-bg {
            background: linear-gradient(to right, #9333EA, #EC4899);
        }
        
        .stats-card {
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }

        .status-badge {
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-aktif {
            background-color: #DEF7EC;
            color: #03543F;
        }

        .status-selesai {
            background-color: #E1EFFE;
            color: #1E429F;
        }

        .status-tertunda {
            background-color: #FEF3C7;
            color: #92400E;
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
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
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
        <div class="w-64 bg-white shadow-md sidebar">
            <div class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="sidebar-link active block px-4 py-2.5 rounded transition text-purple-600 hover:text-purple-800">
                            <i class="fas fa-tachometer-alt w-5 mr-2 inline-block"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800 flex items-center">
                            <i class="fas fa-users w-5 mr-2 inline-block"></i>
                            Pengguna
                        </a>
                    </li>
                    <li>
                        <a href="ruangan.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800">
                            <i class="fas fa-door-open w-5 mr-2 inline-block"></i>
                            Ruangan
                        </a>
                    </li>
                    <li>
                        <a href="peminjaman.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800">
                            <i class="fas fa-calendar-alt w-5 mr-2 inline-block"></i>
                            Peminjaman
                        </a>
                    </li>
                    <li>
                        <a href="pengembalian_admin.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800">
                            <i class="fas fa-undo w-5 mr-2 inline-block"></i>
                            Pengembalian Ruangan
                        </a>
                    </li>
                    <li>
                        <a href="report.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800 flex items-center">
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
        <div class="flex-1 p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard Overview</h2>
            
            <!-- Stats cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                <!-- Total Peminjaman -->
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-purple-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $total_peminjaman; ?></p>
                                <p class="text-sm font-medium text-gray-500">Total Peminjaman</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                <i class="fas fa-clipboard-list text-xl text-purple-500"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peminjaman Aktif -->
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-green-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $peminjaman_aktif; ?></p>
                                <p class="text-sm font-medium text-gray-500">Peminjaman Aktif</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-check-circle text-xl text-green-500"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Ruangan -->
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-blue-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $total_ruangan; ?></p>
                                <p class="text-sm font-medium text-gray-500">Total Ruangan</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-door-open text-xl text-blue-500"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peminjaman Tertunda -->
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-yellow-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $peminjaman_tertunda; ?></p>
                                <p class="text-sm font-medium text-gray-500">Peminjaman Tertunda</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                <i class="fas fa-clock text-xl text-yellow-500"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peminjaman Ditolak -->
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-red-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $peminjaman_ditolak; ?></p>
                                <p class="text-sm font-medium text-gray-500">Peminjaman Ditolak</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                <i class="fas fa-times-circle text-xl text-red-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabel Peminjaman -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">Data Peminjaman Terbaru</h3>
                    <!-- <a href="#" class="text-purple-600 hover:text-purple-800 text-sm font-medium">Lihat Semua</a> -->
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pengembalian</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach (array_reverse($peminjaman_list) as $peminjaman): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $peminjaman['peminjaman_id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($peminjaman['nama_ruangan']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($peminjaman['peminjam']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $peminjaman['waktu_mulai'] . ' - ' . $peminjaman['waktu_selesai']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $peminjaman['durasi_pinjam']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge <?php 
                                        echo match($peminjaman['status']) {
                                            'disetujui' => 'status-aktif',
                                            'ditolak' => 'status-selesai',
                                            'menunggu' => 'status-tertunda',
                                            default => ''
                                        };
                                    ?>">
                                        <?php echo ucfirst($peminjaman['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($peminjaman['status_pengembalian'] === 'disetujui'): ?>
                                        <span class="status-badge status-aktif">Sudah Dikembalikan</span>
                                    <?php elseif ($peminjaman['status_pengembalian'] === 'menunggu'): ?>
                                        <span class="status-badge status-tertunda">Menunggu Pengembalian</span>
                                    <?php elseif ($peminjaman['status_pengembalian'] === 'ditolak'): ?>
                                        <span class="status-badge status-selesai">Pengembalian Ditolak</span>
                                    <?php else: ?>
                                        <span class="status-badge bg-gray-200 text-gray-600">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.hamburger').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        });
    </script>
</body>
</html> 