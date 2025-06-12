<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
require_once "config.php";

$active = 'users';

// Proses tambah user
if(isset($_POST['tambah_user'])) {
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $password, $role, $nama_lengkap]);
    $_SESSION['success'] = "User berhasil ditambahkan.";
    header("Location: users.php");
    exit;
}
// Proses edit user
if(isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $role = $_POST['role'];
    if(!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username=?, password=?, role=?, nama_lengkap=? WHERE user_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $password, $role, $nama_lengkap, $user_id]);
    } else {
        $sql = "UPDATE users SET username=?, role=?, nama_lengkap=? WHERE user_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $role, $nama_lengkap, $user_id]);
    }
    $_SESSION['success'] = "User berhasil diupdate.";
    header("Location: users.php");
    exit;
}
// Proses hapus user
if(isset($_GET['hapus'])) {
    $user_id = $_GET['hapus'];
    $pdo->prepare("DELETE FROM users WHERE user_id=?")->execute([$user_id]);
    $_SESSION['success'] = "User berhasil dihapus.";
    header("Location: users.php");
    exit;
}
// Ambil data user
$users = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY user_id ASC")->fetchAll();
// Untuk edit
$edit_user = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_user = $pdo->query("SELECT * FROM users WHERE user_id=".intval($edit_id))->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    .sidebar {
        min-height: calc(100vh - 64px);
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
    }
    @media (min-width: 769px) {
        .hamburger {
            display: none;
        }
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
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Manajemen User</h2>
            <div class="flex justify-end mb-4">
                <?php if(!$edit_user): ?>
                <a href="?tambah=1" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold shadow flex items-center">
                    <i class="fas fa-plus mr-2"></i>Tambah User
                </a>
                <?php endif; ?>
            </div>
            <?php if(isset($_GET['tambah']) || $edit_user): ?>
            <div class="bg-white rounded-lg shadow p-6 max-w-lg mb-8 mx-auto">
                <h3 class="text-lg font-semibold mb-4 text-purple-700 flex items-center">
                    <i class="fas fa-users mr-2"></i>
                    <?= $edit_user ? 'Edit User' : 'Tambah User' ?>
                </h3>
                <form method="POST" class="space-y-4">
                    <?php if($edit_user): ?>
                        <input type="hidden" name="user_id" value="<?= $edit_user['user_id'] ?>">
                    <?php endif; ?>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($edit_user['username'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($edit_user['nama_lengkap'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Role</label>
                        <select name="role" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700" required>
                            <option value="admin" <?= (isset($edit_user['role']) && $edit_user['role']=='admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="user" <?= (isset($edit_user['role']) && $edit_user['role']=='user') ? 'selected' : '' ?>>User</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Password <?= $edit_user ? '<span class=\'text-xs text-gray-400\'>(Kosongkan jika tidak ingin mengubah)</span>' : '' ?></label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700" <?= $edit_user ? '' : 'required' ?> autocomplete="new-password">
                    </div>
                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="submit" name="<?= $edit_user ? 'edit_user' : 'tambah_user' ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold shadow"><?= $edit_user ? 'Update' : 'Tambah' ?></button>
                        <a href="users.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">Batal</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $index => $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $index + 1; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="users.php?edit=<?= $user['user_id'] ?>" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-edit"></i></a>
                                <a href="users.php?hapus=<?= $user['user_id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Yakin ingin menghapus user ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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