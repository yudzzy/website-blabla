<?php
// Inisialisasi sesi
session_start();

// Periksa jika pengguna sudah login, jika ya redirect ke halaman dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard_admin.php");
    exit;
}

// Ambil flash message jika ada
$flash_message = '';
$flash_type = '';
if(isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_type'];
    // Hapus flash message setelah diambil
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

// Tampilkan notifikasi sukses registrasi jika ada
$register_success = false;
if(isset($_SESSION['register_success'])) {
    $register_success = true;
    unset($_SESSION['register_success']); // Hapus session setelah ditampilkan
}

// Include file konfigurasi
require_once "config.php";

// Inisialisasi variabel
$username = $password = "";
$username_err = $password_err = "";
$login_err = "";

// Proses form ketika di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validasi username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Silakan masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validasi password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Silakan masukkan password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validasi kredensial
    if (empty($username_err) && empty($password_err)) {
        // Siapkan statement select
        $sql = "SELECT user_id, username, password, role, nama_lengkap, jenis_penguna FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)) {
            // Bind variabel ke statement sebagai parameter
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            
            // Set parameter
            $param_username = trim($_POST["username"]);
            
            // Mencoba eksekusi statement
            if($stmt->execute()) {
                // Periksa jika username ada
                if($stmt->rowCount() == 1) {
                    if($row = $stmt->fetch()) {
                        $id = $row["user_id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        $role = $row["role"];
                        $nama_lengkap = $row["nama_lengkap"];
                        $jenis_penguna = $row["jenis_penguna"];
                        
                        if($password == $hashed_password) { // Menggunakan perbandingan langsung karena password tidak di-hash
                            // Password benar, mulai sesi baru
                            session_start();
                            
                            // Simpan data di sesi
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            $_SESSION["nama_lengkap"] = $nama_lengkap;
                            $_SESSION["jenis_penguna"] = $jenis_penguna;
                            
                            // Redirect berdasarkan role
                            if($role == "admin") {
                                header("location: dashboard_admin.php");
                            } else {
                                header("location: dashboard_user.php");
                            }
                        } else {
                            // Password salah
                            $login_err = "Username atau password tidak valid.";
                        }
                    }
                } else {
                    // Username tidak ditemukan
                    $login_err = "Username atau password tidak valid.";
                }
            } else {
                $login_err = "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }

            // Tutup statement
            unset($stmt);
        }
    }
    
    // Tutup koneksi
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            position: relative;
        }
        
        .gradient-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom right, rgba(168, 85, 247, 0.1), rgba(236, 72, 153, 0.1));
            z-index: 0;
        }
        
        .card-header, .card-content, .card-footer {
            position: relative;
            z-index: 10;
        }

        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
            pointer-events: none;
        }

        /* Animasi untuk notifikasi */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Animasi untuk tombol dan input */
        .login-button {
            transition: all 0.3s ease;
            background: linear-gradient(to right, #9333EA, #EC4899);
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(147, 51, 234, 0.3);
        }

        .input-field {
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            transform: scale(1.01);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-purple-50 to-pink-50">
    <?php if(!empty($flash_message)): ?>
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: '<?php echo $flash_message; ?>',
            icon: '<?php echo $flash_type; ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    </script>
    <?php endif; ?>

    <div class="card w-full max-w-md">
        <div class="gradient-bg"></div>
        
        <div class="card-header p-6 text-center space-y-1">
            <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center transform hover:scale-110 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-purple-900">Selamat Datang Kembali</h2>
            <p class="text-gray-500">Silakan masuk ke akun Anda</p>
        </div>
        
        <div class="card-content px-6 pt-2 pb-6 space-y-4">
            <?php if(!empty($login_err)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative animate-bounce" role="alert">
                    <span class="block sm:inline"><?php echo $login_err; ?></span>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4">
                <div class="space-y-2">
                    <label for="username" class="text-sm font-medium">Username</label>
                    <div class="relative">
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="username" class="input-field w-full px-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" placeholder="Masukkan username Anda" required>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <div class="relative">
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" class="input-field w-full px-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" placeholder="Masukkan password Anda" required>
                    </div>
                </div>
                
                <button type="submit" class="login-button w-full py-2 px-4 text-white rounded-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:ring-opacity-50 transition-all">
                    Masuk
                </button>
            </form>
        </div>
        
        <div class="card-footer p-6 text-center">
            <div class="text-sm text-gray-500">
                Belum punya akun? 
                <a href="register.php" class="font-medium text-purple-600 hover:text-purple-500 transition-colors duration-300">Daftar sekarang</a>
            </div>
        </div>
    </div>
</body>
</html>