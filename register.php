<?php
// Inisialisasi sesi
session_start();

// Include file konfigurasi database
require_once "config.php";

// Mendefinisikan variabel dengan nilai kosong
$id_card = $username = $password = $confirm_password = $role = $jenis_penguna = $nama_lengkap = "";
$id_card_err = $username_err = $password_err = $confirm_password_err = $role_err = $jenis_penguna_err = $nama_lengkap_err = "";

// Memproses data form ketika form di-submit
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validasi ID Card
    if(empty(trim($_POST["id_card"]))){
        $id_card_err = "Masukkan nomor ID Card.";
    } else {
        $id_card = trim($_POST["id_card"]);
    }
    
    // Validasi username
    if(empty(trim($_POST["username"]))){
        $username_err = "Masukkan username.";
    } else {
        // Menyiapkan statement select
        $sql = "SELECT user_id FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $username_err = "Username ini sudah terdaftar.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }

            unset($stmt);
        }
    }
    
    // Validasi password
    if(empty(trim($_POST["password"]))){
        $password_err = "Masukkan password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password harus memiliki minimal 6 karakter.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validasi konfirmasi password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Konfirmasi password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password tidak cocok.";
        }
    }

    // Validasi role
    if(empty(trim($_POST["role"]))){
        $role_err = "Pilih role.";
    } else {
        $role = trim($_POST["role"]);
    }

    // Validasi jenis pengguna
    if(empty(trim($_POST["jenis_penguna"]))){
        $jenis_penguna_err = "Pilih jenis pengguna.";
    } else {
        $jenis_penguna = trim($_POST["jenis_penguna"]);
    }

    // Validasi nama lengkap
    if(empty(trim($_POST["nama_lengkap"]))){
        $nama_lengkap_err = "Masukkan nama lengkap.";
    } else {
        $nama_lengkap = trim($_POST["nama_lengkap"]);
    }
    
    // Cek error sebelum insert ke database
    if(empty($id_card_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err) && empty($jenis_penguna_err) && empty($nama_lengkap_err)){
        
        // Menyiapkan statement insert
        $sql = "INSERT INTO users (id_card, username, password, role, jenis_penguna, nama_lengkap) VALUES (:id_card, :username, :password, :role, :jenis_penguna, :nama_lengkap)";
         
        if($stmt = $pdo->prepare($sql)){
            // Bind parameter
            $stmt->bindParam(":id_card", $param_id_card, PDO::PARAM_STR);
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":role", $param_role, PDO::PARAM_STR);
            $stmt->bindParam(":jenis_penguna", $param_jenis_penguna, PDO::PARAM_STR);
            $stmt->bindParam(":nama_lengkap", $param_nama_lengkap, PDO::PARAM_STR);
            
            // Set parameter
            $param_id_card = $id_card;
            $param_username = $username;
            $param_password = $password; // Simpan password apa adanya (tidak di-hash)
            $param_role = $role;
            $param_jenis_penguna = $jenis_penguna;
            $param_nama_lengkap = $nama_lengkap;
            
            // Mencoba eksekusi statement
            if($stmt->execute()){
                // Set flash message di session
                $_SESSION['flash_message'] = "Selamat! Akun Anda telah berhasil terdaftar.";
                $_SESSION['flash_type'] = "success";
                
                // Redirect ke login
                header("location: login.php");
                exit();
            } else{
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }

            unset($stmt);
        }
    }
    
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-gray-100 py-6 flex flex-col justify-center sm:py-12">
    <?php if(!empty($success_msg)): ?>
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: '<?php echo $success_msg; ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
            });
        </script>
    <?php endif; ?>

    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
        <div class="relative px-4 py-10 bg-white mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
            <div class="max-w-md mx-auto">
                <div class="flex items-center space-x-5">
                    <div class="h-14 w-14 bg-purple-200 rounded-full flex flex-shrink-0 justify-center items-center text-purple-500 text-2xl font-mono">i</div>
                    <div class="block pl-2 font-semibold text-xl self-start text-gray-700">
                        <h2 class="leading-relaxed">Buat Akun Baru</h2>
                        <p class="text-sm text-gray-500 font-normal leading-relaxed">Silakan isi form di bawah ini untuk mendaftar.</p>
                    </div>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="divide-y divide-gray-200">
                    <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">
                        <!-- ID Card -->
                        <div class="relative">
                            <input type="text" name="id_card" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-purple-600 placeholder-transparent <?php echo (!empty($id_card_err)) ? 'border-red-500' : ''; ?>" placeholder="ID Card" value="<?php echo $id_card; ?>">
                            <label for="id_card" class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-440 peer-placeholder-shown:top-2 transition-all peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">ID Card</label>
                            <?php if(!empty($id_card_err)) echo '<p class="text-red-500 text-xs mt-1">'.$id_card_err.'</p>'; ?>
                        </div>

                        <!-- Username -->
                        <div class="relative">
                            <input type="text" name="username" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-purple-600 placeholder-transparent <?php echo (!empty($username_err)) ? 'border-red-500' : ''; ?>" placeholder="Username" value="<?php echo $username; ?>">
                            <label for="username" class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-440 peer-placeholder-shown:top-2 transition-all peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Username</label>
                            <?php if(!empty($username_err)) echo '<p class="text-red-500 text-xs mt-1">'.$username_err.'</p>'; ?>
                        </div>

                        <!-- Password -->
                        <div class="relative">
                            <input type="password" name="password" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-purple-600 placeholder-transparent <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>" placeholder="Password">
                            <label for="password" class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-440 peer-placeholder-shown:top-2 transition-all peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Password</label>
                            <?php if(!empty($password_err)) echo '<p class="text-red-500 text-xs mt-1">'.$password_err.'</p>'; ?>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="relative">
                            <input type="password" name="confirm_password" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-purple-600 placeholder-transparent <?php echo (!empty($confirm_password_err)) ? 'border-red-500' : ''; ?>" placeholder="Konfirmasi Password">
                            <label for="confirm_password" class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-440 peer-placeholder-shown:top-2 transition-all peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Konfirmasi Password</label>
                            <?php if(!empty($confirm_password_err)) echo '<p class="text-red-500 text-xs mt-1">'.$confirm_password_err.'</p>'; ?>
                        </div>

                        <!-- Role -->
                        <div class="relative">
                            <select name="role" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-purple-600 <?php echo (!empty($role_err)) ? 'border-red-500' : ''; ?>">
                                <option value="">Pilih Role</option>
                                <option value="admin" <?php echo ($role == "admin") ? "selected" : ""; ?>>Admin</option>
                                <option value="user" <?php echo ($role == "user") ? "selected" : ""; ?>>User</option>
                            </select>
                            <?php if(!empty($role_err)) echo '<p class="text-red-500 text-xs mt-1">'.$role_err.'</p>'; ?>
                        </div>

                        <!-- Jenis Pengguna -->
                        <div class="relative">
                            <select name="jenis_penguna" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-purple-600 <?php echo (!empty($jenis_penguna_err)) ? 'border-red-500' : ''; ?>">
                                <option value="">Pilih Jenis Pengguna</option>
                                <option value="siswa" <?php echo ($jenis_penguna == "siswa") ? "selected" : ""; ?>>Siswa</option>
                                <option value="guru" <?php echo ($jenis_penguna == "guru") ? "selected" : ""; ?>>Guru</option>
                            </select>
                            <?php if(!empty($jenis_penguna_err)) echo '<p class="text-red-500 text-xs mt-1">'.$jenis_penguna_err.'</p>'; ?>
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="relative">
                            <input type="text" name="nama_lengkap" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-purple-600 placeholder-transparent <?php echo (!empty($nama_lengkap_err)) ? 'border-red-500' : ''; ?>" placeholder="Nama Lengkap" value="<?php echo $nama_lengkap; ?>">
                            <label for="nama_lengkap" class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-440 peer-placeholder-shown:top-2 transition-all peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Nama Lengkap</label>
                            <?php if(!empty($nama_lengkap_err)) echo '<p class="text-red-500 text-xs mt-1">'.$nama_lengkap_err.'</p>'; ?>
                        </div>

                        <div class="relative mt-6">
                            <button type="submit" class="bg-purple-500 text-white rounded-md px-4 py-2 hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:ring-opacity-50 w-full">Daftar</button>
                        </div>
                        <p class="text-sm text-center text-gray-500 mt-4">
                            Sudah punya akun? <a href="login.php" class="text-purple-600 hover:text-purple-700">Login di sini</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tambahkan event listener untuk form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            // Tampilkan loading state pada tombol
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mendaftar...</span>';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html> 