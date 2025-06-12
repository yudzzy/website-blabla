<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

// Navbar dan Sidebar
function render_navbar_sidebar($active = '') {
    ?>
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
        <div class="w-64 bg-white shadow-md min-h-screen">
            <div class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard_user.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800 flex items-center">
                            <i class="fas fa-tachometer-alt w-5 mr-2 inline-block"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="data_ruangan_user.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800 flex items-center">
                            <i class="fas fa-door-open w-5 mr-2 inline-block"></i>
                            Ajukan Peminjaman
                        </a>
                    </li>
                    <li>
                        <a href="pengembalian_ruangan.php" class="sidebar-link block px-4 py-2.5 rounded transition text-gray-600 hover:text-purple-800 flex items-center">
                            <i class="fas fa-undo w-5 mr-2 inline-block"></i>
                            Ajukan Pengembalian
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
    <?php
}

?><!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Ruangan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
<?php
// Jika ada ruangan_id di URL, tampilkan form pengajuan
if (isset($_GET['ruangan_id'])) {
    $ruangan_id = $_GET['ruangan_id'];
    $stmt = $pdo->prepare("SELECT * FROM ruangan WHERE ruangan_id = ?");
    $stmt->execute([$ruangan_id]);
    $ruangan = $stmt->fetch();

    if (!$ruangan) {
        render_navbar_sidebar('ruangan');
        echo "<div class='text-red-500'>Ruangan tidak ditemukan!</div></div></div></body></html>";
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $tanggal = $_POST['tanggal'];
        $waktu_mulai = $_POST['waktu_mulai'];
        $waktu_selesai = $_POST['waktu_selesai'];
        $keterangan = $_POST['keterangan'];
        $user_id = $_SESSION['user_id'];

        // Hitung durasi pinjam (dalam jam)
        $start = strtotime($waktu_mulai);
        $end = strtotime($waktu_selesai);
        $diff = ($end - $start) / 3600; // dalam jam
        if ($diff <= 0) $diff = 0.5; // minimal setengah jam
        $durasi_pinjam = $diff . ' jam';

        $sql = "INSERT INTO peminjaman_ruangan (user_id, ruangan_id, tanggal, waktu_mulai, waktu_selesai, durasi_pinjam, keterangan, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $ruangan_id, $tanggal, $waktu_mulai, $waktu_selesai, $durasi_pinjam, $keterangan]);
        $_SESSION['success'] = "Pengajuan peminjaman berhasil, menunggu persetujuan admin.";
        header("location: data_ruangan_user.php");
        exit;
    }
    render_navbar_sidebar('ruangan');
    ?>
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md p-8 mt-8">
            <h2 class="text-2xl font-semibold mb-6 text-purple-700 flex items-center"><i class="fas fa-door-open mr-2"></i>Ajukan Peminjaman Ruangan</h2>
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Nama Ruangan</label>
                    <input type="text" value="<?= htmlspecialchars($ruangan['nama_ruangan']) ?>" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700" readonly>
                </div>
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Tanggal</label>
                    <input type="date" name="tanggal" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-400" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Waktu Mulai</label>
                        <input type="time" name="waktu_mulai" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-400" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Waktu Selesai</label>
                        <input type="time" name="waktu_selesai" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-400" required>
                    </div>
                </div>
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-400 bg-gray-100 text-gray-700"></textarea>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold shadow">Ajukan</button>
                    <a href="data_ruangan_user.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<?php
    exit;
}

// Jika tidak ada ruangan_id, tampilkan daftar ruangan
$sql = "SELECT * FROM ruangan";
$ruangan = $pdo->query($sql)->fetchAll();
render_navbar_sidebar('ruangan');
?>
    <h2 class="text-2xl font-semibold mb-4">Daftar Ruangan</h2>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <table class="min-w-full bg-white rounded-lg shadow">
        <thead>
            <tr>
                <th class="px-4 py-2">Nama Ruangan</th>
                <th class="px-4 py-2">Lokasi</th>
                <th class="px-4 py-2">Kapasitas</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($ruangan as $r): ?>
            <tr>
                <td class="px-4 py-2"><?= htmlspecialchars($r['nama_ruangan']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($r['lokasi']) ?></td>
                <td class="px-4 py-2"><?= $r['kapasitas'] ?></td>
                <td class="px-4 py-2">
                    <?php
                    // Cek apakah ruangan sedang digunakan (ada peminjaman disetujui dan belum dikembalikan)
                    $sqlCek = "SELECT COUNT(*) FROM peminjaman_ruangan WHERE ruangan_id = ? AND status = 'disetujui' AND (status_pengembalian IS NULL OR status_pengembalian != 'disetujui')";
                    $stmtCek = $pdo->prepare($sqlCek);
                    $stmtCek->execute([$r['ruangan_id']]);
                    $isDipinjam = $stmtCek->fetchColumn();
                    echo $isDipinjam ? "<span class='text-red-500'>Ruangan sedang digunakan</span>" : "<span class='text-green-500'>Bisa Dipinjam</span>";
                    ?>
                </td>
                <td class="px-4 py-2">
                    <?php if(!$isDipinjam): ?>
                    <a href="data_ruangan_user.php?ruangan_id=<?= $r['ruangan_id'] ?>" class="bg-purple-600 text-white px-3 py-1 rounded">Ajukan Peminjaman</a>
                    <?php else: ?>
                    <button class="bg-gray-400 text-white px-3 py-1 rounded cursor-not-allowed" disabled>Ajukan Peminjaman</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
</div>
</body>
</html> 