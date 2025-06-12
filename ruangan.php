<?php
// Inisialisasi sesi
session_start();

// Cek apakah user sudah login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include file konfigurasi database
require_once "config.php";

// Proses form tambah/edit ruangan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'tambah') {
            $sql = "INSERT INTO ruangan (nama_ruangan, lokasi, kapasitas) VALUES (:nama_ruangan, :lokasi, :kapasitas)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nama_ruangan' => $_POST['nama_ruangan'],
                'lokasi' => $_POST['lokasi'],
                'kapasitas' => $_POST['kapasitas']
            ]);
            $_SESSION['success'] = "Ruangan berhasil ditambahkan!";
        } 
        else if ($_POST['action'] == 'edit') {
            $sql = "UPDATE ruangan SET nama_ruangan = :nama_ruangan, lokasi = :lokasi, kapasitas = :kapasitas WHERE ruangan_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nama_ruangan' => $_POST['nama_ruangan'],
                'lokasi' => $_POST['lokasi'],
                'kapasitas' => $_POST['kapasitas'],
                'id' => $_POST['ruangan_id']
            ]);
            $_SESSION['success'] = "Ruangan berhasil diperbarui!";
        }
        header("Location: ruangan.php");
        exit();
    }
}

// Proses hapus ruangan
if (isset($_GET['delete'])) {
    $sql = "DELETE FROM ruangan WHERE ruangan_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $_GET['delete']]);
    $_SESSION['success'] = "Ruangan berhasil dihapus!";
    header("Location: ruangan.php");
    exit();
}

// Ambil data ruangan
$sql = "SELECT * FROM ruangan ORDER BY ruangan_id ASC";
$ruangan = $pdo->query($sql)->fetchAll();

$active = 'ruangan';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ruangan</title>
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
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Manajemen Ruangan</h2>
                <button onclick="openModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold shadow flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Ruangan
                </button>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Tabel Ruangan -->
            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($ruangan as $room): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $room['ruangan_id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($room['nama_ruangan']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($room['lokasi']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $room['kapasitas']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editRuangan(<?php echo htmlspecialchars(json_encode($room)); ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteRuangan(<?php echo $room['ruangan_id']; ?>)" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div id="ruanganModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Tambah Ruangan</h3>
                <form id="ruanganForm" method="POST">
                    <input type="hidden" name="action" id="formAction" value="tambah">
                    <input type="hidden" name="ruangan_id" id="ruangan_id">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_ruangan">
                            Nama Ruangan
                        </label>
                        <input type="text" name="nama_ruangan" id="nama_ruangan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="lokasi">
                            Lokasi
                        </label>
                        <input type="text" name="lokasi" id="lokasi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="kapasitas">
                            Kapasitas
                        </label>
                        <input type="number" name="kapasitas" id="kapasitas" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg mr-2">
                            Batal
                        </button>
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('ruanganModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Tambah Ruangan';
            document.getElementById('formAction').value = 'tambah';
            document.getElementById('ruanganForm').reset();
        }

        function closeModal() {
            document.getElementById('ruanganModal').classList.add('hidden');
        }

        function editRuangan(ruangan) {
            document.getElementById('ruanganModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Edit Ruangan';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('ruangan_id').value = ruangan.ruangan_id;
            document.getElementById('nama_ruangan').value = ruangan.nama_ruangan;
            document.getElementById('lokasi').value = ruangan.lokasi;
            document.getElementById('kapasitas').value = ruangan.kapasitas;
        }

        function deleteRuangan(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ruangan akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `ruangan.php?delete=${id}`;
                }
            });
        }

        // Tutup modal jika user klik di luar modal
        window.onclick = function(event) {
            const modal = document.getElementById('ruanganModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        document.querySelector('.hamburger').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        });
    </script>
</body>
</html>
