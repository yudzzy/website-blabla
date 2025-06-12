<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
require_once "config.php";

// Proses Approve/Reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'approve') {
        $sql = "UPDATE peminjaman_ruangan SET status = 'disetujui' WHERE peminjaman_id = :id";
    } elseif ($_GET['action'] == 'reject') {
        $sql = "UPDATE peminjaman_ruangan SET status = 'ditolak' WHERE peminjaman_id = :id";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $_SESSION['success'] = "Peminjaman telah di-update.";
    header("Location: peminjaman.php");
    exit();
}

// Tab aktif
$tab = $_GET['tab'] ?? 'semua';

// Ambil data peminjaman
$sql_all = "SELECT pr.*, u.nama_lengkap, r.nama_ruangan FROM peminjaman_ruangan pr
LEFT JOIN users u ON pr.user_id = u.user_id
LEFT JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
ORDER BY pr.peminjaman_id DESC";
$peminjaman = $pdo->query($sql_all)->fetchAll();

// Ambil data peminjaman disetujui
$sql_disetujui = "SELECT pr.*, u.nama_lengkap, r.nama_ruangan FROM peminjaman_ruangan pr
LEFT JOIN users u ON pr.user_id = u.user_id
LEFT JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
WHERE pr.status = 'disetujui'
ORDER BY pr.peminjaman_id DESC";
$peminjaman_disetujui = $pdo->query($sql_disetujui)->fetchAll();

// Ambil data peminjaman ditolak
$sql_ditolak = "SELECT pr.*, u.nama_lengkap, r.nama_ruangan FROM peminjaman_ruangan pr
LEFT JOIN users u ON pr.user_id = u.user_id
LEFT JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
WHERE pr.status = 'ditolak'
ORDER BY pr.peminjaman_id DESC";
$peminjaman_ditolak = $pdo->query($sql_ditolak)->fetchAll();

// Tampilkan data yang menunggu persetujuan
$sql_menunggu = "SELECT pr.*, u.nama_lengkap, r.nama_ruangan FROM peminjaman_ruangan pr
LEFT JOIN users u ON pr.user_id = u.user_id
LEFT JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
WHERE pr.status = 'menunggu'
ORDER BY pr.peminjaman_id DESC";
$peminjaman_menunggu = $pdo->query($sql_menunggu)->fetchAll();

$active = 'peminjaman';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
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
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Data Peminjaman Ruangan</h2>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <!-- Tabs -->
            <div class="mb-6 flex gap-2">
                <a href="peminjaman.php?tab=semua" class="px-4 py-2 rounded-t-lg font-medium border-b-2 transition <?php echo $tab=='semua' ? 'border-purple-600 text-purple-600 bg-white' : 'border-transparent text-gray-500 bg-gray-100'; ?>">Semua</a>
            </div>
            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $data = $peminjaman_menunggu;
                        foreach ($data as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['peminjaman_id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_ruangan']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['waktu_mulai'] . ' - ' . $row['waktu_selesai']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['durasi_pinjam']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if($row['status'] == 'menunggu'): ?>
                                    <a href="peminjaman.php?action=approve&id=<?php echo $row['peminjaman_id']; ?>" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded mr-2">Approve</a>
                                    <a href="peminjaman.php?action=reject&id=<?php echo $row['peminjaman_id']; ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Reject</a>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
    </style>
</body>
</html> 