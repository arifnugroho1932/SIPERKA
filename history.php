<?php
session_start();
require_once 'config/db.php';
require_once 'auth/middleware.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT p.*, r.nama as nama_ruangan 
    FROM peminjaman p 
    JOIN ruangan r ON p.ruangan_id = r.id 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPERKA - Riwayat Peminjaman</title>
    
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
        .editorial-card { background-color: #ffffff; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .editorial-btn-primary { background-color: #d97706; color: #ffffff; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24; font-weight: 600; }
        .editorial-btn-secondary { background-color: #ffffff; color: #111c24; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24; font-weight: 600; }
        
        .status-badge { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.25rem 0.75rem; border: 1px solid #111c24; border-radius: 4px; display: inline-block; }
        .badge-tersedia { background-color: #dcfce7; color: #166534; }
        .badge-pending { background-color: #fef08a; color: #854d0e; }
        .badge-ditolak { background-color: #fee2e2; color: #991b1b; }
        
        .nav-link { font-family: 'Inter', sans-serif; font-weight: 500; color: #111c24; transition: color 0.2s ease; padding-bottom: 2px; }
        .nav-link:hover { color: #d97706; }
        .nav-link.active { border-bottom: 3px solid #d97706; font-weight: 600; }
    </style>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <header class="sticky top-0 z-50 bg-alabaster border-b border-midnight/10 backdrop-blur-md">
        <div class="max-w-[1200px] mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-warm border border-midnight shadow-[2px_2px_0px_#111c24] flex items-center justify-center rounded-sm">
                    <span class="font-serif font-bold text-white text-lg">S</span>
                </div>
                <h1 class="font-serif font-bold text-2xl tracking-tight text-midnight hidden sm:block">SIPERKA</h1>
            </div>

            <nav class="hidden md:flex items-center gap-8">
                <a href="index.php" class="nav-link">Beranda</a>
                <a href="status.php" class="nav-link">Jadwal Global</a>
                <a href="history.php" class="nav-link active">Riwayat Saya</a>
            </nav>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <a href="auth/logout.php" class="editorial-btn-secondary text-sm px-3 py-2 ml-2 text-red-600">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-[1200px] w-full mx-auto px-6 py-12">
        <div class="mb-10">
            <h2 class="font-serif text-4xl md:text-5xl font-bold leading-tight mb-4">Riwayat Peminjaman.</h2>
            <p class="text-lg text-midnight/80 font-sans">Lihat dan kelola status reservasi ruangan Anda.</p>
        </div>

        <?php if (isset($_GET['sukses'])): ?>
            <div class="mb-6 p-4 border border-[#111c24] bg-[#dcfce7] shadow-[3px_3px_0px_#111c24] rounded-md font-bold text-[#166534]">
                <i class="fa-solid fa-check-circle mr-2"></i>Peminjaman berhasil diajukan dan sedang menunggu persetujuan Admin!
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['sukses_batal'])): ?>
            <div class="mb-6 p-4 border border-[#111c24] bg-[#fee2e2] shadow-[3px_3px_0px_#111c24] rounded-md font-bold text-[#991b1b]">
                <i class="fa-solid fa-info-circle mr-2"></i>Peminjaman berhasil dibatalkan.
            </div>
        <?php endif; ?>

        <?php if (empty($riwayat)): ?>
            <div class="py-12 text-center border-2 border-dashed border-midnight/20 rounded-md">
                <p class="text-midnight/60 font-medium">Belum ada riwayat peminjaman.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($riwayat as $item): 
                    $badgeClass = ''; $statusLabel = '';
                    if ($item['status'] === 'pending') { $badgeClass = 'badge-pending'; $statusLabel = 'Menunggu'; }
                    if ($item['status'] === 'disetujui') { $badgeClass = 'badge-tersedia'; $statusLabel = 'Disetujui'; }
                    if ($item['status'] === 'ditolak') { $badgeClass = 'badge-ditolak'; $statusLabel = 'Ditolak'; }
                    if ($item['status'] === 'dibatalkan') { $badgeClass = 'badge-ditolak'; $statusLabel = 'Dibatalkan'; }
                ?>
                <div class="editorial-card p-6 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="status-badge <?= $badgeClass ?> mb-2"><?= $statusLabel ?></span>
                            <h4 class="font-serif text-xl font-bold"><?= htmlspecialchars($item['nama_ruangan']) ?></h4>
                        </div>
                    </div>
                    <div class="h-px w-full bg-midnight/10 mb-4"></div>
                    
                    <div class="text-sm font-medium text-midnight/80 mb-4 flex-grow">
                        <i class="fa-solid fa-tag mr-2 opacity-70 mb-2"></i><?= htmlspecialchars($item['nama_kegiatan']) ?><br>
                        <i class="fa-solid fa-calendar mr-2 opacity-70 mt-2 mb-2"></i><?= date('d M Y', strtotime($item['tanggal'])) ?><br>
                        <i class="fa-solid fa-clock mr-2 opacity-70 mt-2"></i><?= date('H:i', strtotime($item['waktu_mulai'])) ?> - <?= date('H:i', strtotime($item['waktu_selesai'])) ?>
                    </div>

                    <?php if ($item['status'] === 'ditolak' && !empty($item['alasan_penolakan'])): ?>
                        <div class="mt-4 p-3 border border-midnight bg-[#fee2e2] text-sm text-[#991b1b] rounded-sm font-medium">
                            <strong>Alasan Penolakan:</strong> <?= htmlspecialchars($item['alasan_penolakan']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($item['status'] === 'pending'): ?>
                    <div class="mt-6 pt-4 border-t border-midnight/10">
                        <form action="actions/batal_peminjaman.php" method="POST" onsubmit="return confirm('Yakin ingin membatalkan peminjaman ini?');" class="m-0">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="editorial-btn-secondary px-4 py-2 text-sm w-full border-red-600 text-red-600 shadow-[2px_2px_0px_#dc2626]">Batalkan Peminjaman</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-midnight/10 py-8 bg-pure-white">
        <div class="max-w-[1200px] mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="font-serif font-bold text-lg text-midnight">SIPERKA.</p>
            <p class="text-sm text-midnight/60 font-medium">&copy; 2026 Sistem Peminjaman Ruangan Akademik.</p>
        </div>
    </footer>

    <!-- Mobile Nav -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-midnight/10 flex justify-around p-3 z-50">
        <a href="index.php" class="flex flex-col items-center text-midnight/60">
            <i class="fa-solid fa-home mb-1"></i><span class="text-[10px] font-medium">Beranda</span>
        </a>
        <a href="status.php" class="flex flex-col items-center text-midnight/60">
            <i class="fa-solid fa-calendar-alt mb-1"></i><span class="text-[10px] font-medium">Jadwal</span>
        </a>
        <a href="history.php" class="flex flex-col items-center text-amber-warm">
            <i class="fa-solid fa-clock-rotate-left mb-1"></i><span class="text-[10px] font-bold">Riwayat</span>
        </a>
    </div>

</body>
</html>
