<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$user_id = $_SESSION['user_id'];

// Statistik
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu
FROM peminjaman_ruangan WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql_stats);
$stmt->execute(['user_id' => $user_id]);
$stats = $stmt->fetch();

// Riwayat peminjaman user
$sql_riwayat = "SELECT pr.*, r.nama_ruangan FROM peminjaman_ruangan pr
LEFT JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
WHERE pr.user_id = :user_id
ORDER BY pr.peminjaman_id DESC";
$stmt = $pdo->prepare($sql_riwayat);
$stmt->execute(['user_id' => $user_id]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-purple-600">Hallo <?php echo htmlspecialchars($_SESSION["username"] ?? "User"); ?> 👋</h1>
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
                            <span class="mr-2 text-gray-700">Hai, <?php echo htmlspecialchars($_SESSION["username"] ?? "User"); ?></span>
                            <button class="bg-gray-800 flex text-sm rounded-full focus:outline-none" id="user-menu-button">
                                <img class="h-8 w-8 rounded-full object-cover" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION["username"] ?? "User"); ?>&background=9333EA&color=fff" alt="Profile">
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
                        <a href="dashboard_user.php" class="sidebar-link active block px-4 py-2.5 rounded transition text-purple-600 hover:text-purple-800">
                            <i class="fas fa-tachometer-alt w-5 mr-2 inline-block"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="data_ruangan_user.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800">
                            <i class="fas fa-door-open w-5 mr-2 inline-block"></i>
                            Ajukan Peminjaman
                        </a>
                    </li>
                    <li>
                        <a href="pengembalian_ruangan.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800 flex items-center">
                            <i class="fas fa-undo w-5 mr-2 inline-block"></i> Ajukan Pengembalian
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
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard User</h2>
            <!-- Stats cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-purple-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $stats['total'] ?? 0; ?></p>
                                <p class="text-sm font-medium text-gray-500">Total Peminjaman</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                <i class="fas fa-clipboard-list text-xl text-purple-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-green-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $stats['disetujui'] ?? 0; ?></p>
                                <p class="text-sm font-medium text-gray-500">Disetujui</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-check-circle text-xl text-green-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-yellow-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $stats['menunggu'] ?? 0; ?></p>
                                <p class="text-sm font-medium text-gray-500">Menunggu</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                <i class="fas fa-clock text-xl text-yellow-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 left-0 w-2 h-full bg-red-500"></div>
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-4xl font-bold text-gray-800 mb-1"><?php echo $stats['ditolak'] ?? 0; ?></p>
                                <p class="text-sm font-medium text-gray-500">Ditolak</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                <i class="fas fa-times-circle text-xl text-red-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tabel Riwayat -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Riwayat Peminjaman Anda</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach (array_reverse($riwayat) as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['peminjaman_id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_ruangan']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['waktu_mulai'] . ' - ' . $row['waktu_selesai']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['durasi_pinjam']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge <?php 
                                        echo match($row['status']) {
                                            'disetujui' => 'status-aktif',
                                            'ditolak' => 'status-selesai',
                                            'menunggu' => 'status-tertunda',
                                            default => ''
                                        };
                                    ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <style>
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
    <script>
        document.querySelector('.hamburger').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        });
    </script>
</body>
</html> 