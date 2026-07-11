<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPERKA - Login</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'alabaster': '#fbfaf7', 'midnight': '#111c24', 'amber-warm': '#d97706', 'pure-white': '#ffffff',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'], 'serif': ['Lora', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #fbfaf7; color: #111c24; }
        .editorial-card { background-color: #ffffff; border: 1px solid #111c24; border-radius: 6px; box-shadow: 6px 6px 0px #111c24; }
        .editorial-btn-primary { background-color: #d97706; color: #ffffff; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24; font-weight: 600; transition: all 0.1s ease; cursor: pointer; text-align: center; }
        .editorial-btn-primary:active { transform: translate(3px, 3px); box-shadow: 0px 0px 0px #111c24; }
        .editorial-input { width: 100%; border: 1px solid #111c24; border-radius: 4px; padding: 0.875rem; font-family: 'Inter', sans-serif; transition: all 0.2s ease; background-color: #ffffff; }
        .editorial-input:focus { outline: none; box-shadow: 2px 2px 0px #d97706; border-color: #d97706; }
        .editorial-label { display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem; color: #111c24; text-align: left;}
    </style>
</head>
<body class="antialiased font-sans min-h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Decorative background elements -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-amber-warm/10 rounded-full blur-3xl -z-10"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-midnight/5 rounded-full blur-3xl -z-10"></div>

    <main class="w-full max-w-md px-6 py-12">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-amber-warm border border-midnight shadow-[3px_3px_0px_#111c24] flex items-center justify-center rounded-sm mx-auto mb-4">
                <span class="font-serif font-bold text-white text-2xl">S</span>
            </div>
            <h1 class="font-serif font-bold text-3xl tracking-tight text-midnight">SIPERKA.</h1>
            <p class="text-sm font-medium text-midnight/70 mt-2">Sistem Peminjaman Ruangan Akademik</p>
        </div>

        <div class="editorial-card p-8">
            <div class="text-center mb-8 border-b border-midnight/10 pb-6">
                <h2 class="font-serif text-2xl font-bold mb-2">Selamat Datang</h2>
                <p class="text-sm text-midnight/70">Silakan masuk ke akun SIPERKA Anda.</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 border border-[#111c24] bg-[#fee2e2] shadow-[3px_3px_0px_#111c24] rounded-md font-bold text-[#991b1b] text-sm">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                <?php 
                if ($_GET['error'] === 'invalid') echo "Username atau password salah.";
                elseif ($_GET['error'] === 'empty') echo "Silakan isi username dan password.";
                else echo "Terjadi kesalahan.";
                ?>
            </div>
            <?php endif; ?>

            <form action="auth/login.php" method="POST" class="space-y-5">
                <div>
                    <label for="username" class="editorial-label">NIM / NIDN / Username</label>
                    <input type="text" id="username" name="username" class="editorial-input" placeholder="Masukkan ID Anda" required>
                </div>
                <div>
                    <label for="password" class="editorial-label">Password</label>
                    <input type="password" id="password" name="password" class="editorial-input" placeholder="Masukkan password" required>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="editorial-btn-primary w-full py-3">Masuk</button>
                </div>

                <div class="mt-6 text-center text-sm font-medium">
                    <span class="text-midnight/60">Belum punya akun?</span> 
                    <a href="register.php" class="text-amber-warm hover:text-midnight transition-colors underline decoration-2 underline-offset-4 ml-1">Daftar di sini</a>
                </div>
            </form>
        </div>

        <div class="text-center mt-8">
            <a href="index.php" class="text-sm font-bold text-midnight/60 hover:text-midnight transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke Beranda
            </a>
        </div>
    </main>

</body>
</html>
