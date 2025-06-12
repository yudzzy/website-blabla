<?php
require 'config.php';
$active = 'pengembalian';
$pengembalian = $pdo->query("SELECT pr.*, u.nama_lengkap, r.nama_ruangan
    FROM peminjaman_ruangan pr
    JOIN users u ON pr.user_id = u.user_id
    JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
    WHERE pr.status_pengembalian = 'menunggu'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Ruangan</title>
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
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
        <div class="w-64 bg-white shadow-md min-h-screen">
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
        <div class="flex-1 p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Data Pengembalian Ruangan</h2>
            <div class="bg-white rounded-lg shadow-sm overflow-x-auto p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kembali</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Kembali</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($pengembalian as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['nama_lengkap'] ?? '') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['nama_ruangan'] ?? '') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $row['tanggal'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $row['tanggal_pengembalian'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $row['waktu_pengembalian'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['keterangan'] ?? '') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="approve_pengembalian.php?id=<?= $row['peminjaman_id'] ?>&aksi=approve" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded mr-2">Approve</a>
                                <a href="approve_pengembalian.php?id=<?= $row['peminjaman_id'] ?>&aksi=tolak" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Tolak</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

body {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
}

.flex {
    flex: 1;
} 