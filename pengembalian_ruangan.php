<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$user_id = $_SESSION['user_id'];

// Ambil data peminjaman yang disetujui dan belum dikembalikan
$sql_peminjaman = "SELECT pr.*, r.nama_ruangan FROM peminjaman_ruangan pr
LEFT JOIN ruangan r ON pr.ruangan_id = r.ruangan_id
WHERE pr.user_id = :user_id AND pr.status = 'disetujui' AND (pr.status_pengembalian IS NULL OR pr.status_pengembalian != 'disetujui')";
$stmt = $pdo->prepare($sql_peminjaman);
$stmt->execute(['user_id' => $user_id]);
$peminjaman = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['peminjaman_id'])) {
    $peminjaman_id = $_POST['peminjaman_id'];
    $waktu_pengembalian = date('Y-m-d H:i:s');
    $sql_pengembalian = "UPDATE peminjaman_ruangan SET status_pengembalian = 'menunggu', waktu_pengembalian = :waktu_pengembalian WHERE peminjaman_id = :peminjaman_id";
    $stmt = $pdo->prepare($sql_pengembalian);
    $stmt->execute(['waktu_pengembalian' => $waktu_pengembalian, 'peminjaman_id' => $peminjaman_id]);
    $_SESSION['success'] = "Pengajuan pengembalian berhasil!";
    header("Location: pengembalian_ruangan.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Pengembalian Ruangan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh; /* Memperpanjang sidebar agar sesuai dengan tinggi layar */
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
                        <a href="dashboard_user.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800">
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
                        <a href="pengembalian_ruangan.php" class="sidebar-link active block px-4 py-2.5 rounded transition text-purple-600 hover:text-purple-800 flex items-center">
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
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Ajukan Pengembalian Ruangan</h2>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($peminjaman as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['peminjaman_id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_ruangan']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['waktu_mulai'] . ' - ' . $row['waktu_selesai']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST" action="">
                                    <input type="hidden" name="peminjaman_id" value="<?php echo $row['peminjaman_id']; ?>">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">Ajukan Pengembalian</button>
                                </form>
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
