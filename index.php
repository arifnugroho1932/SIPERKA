<?php
session_start();
require_once 'config/db.php';
require_once 'auth/middleware.php';

// Ambil data gedung
$stmtGedung = $pdo->query("SELECT * FROM gedung ORDER BY kode ASC");
$gedungs = $stmtGedung->fetchAll();

// Ambil data ruangan
$stmtRuangan = $pdo->query("SELECT * FROM ruangan WHERE status = 'aktif' ORDER BY nama ASC");
$semuaRuangan = $stmtRuangan->fetchAll();

// Kelompokkan ruangan berdasarkan gedung_id
$ruanganPerGedung = [];
foreach ($semuaRuangan as $r) {
    $ruanganPerGedung[$r['gedung_id']][] = $r;
}

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPERKA - Sistem Peminjaman Ruangan</title>
    
    <!-- Tailwind CSS (CDN for single-file implementation) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Lora (Serif) & Inter (Sans-serif) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'alabaster': '#fbfaf7',
                        'midnight': '#111c24',
                        'amber-warm': '#d97706',
                        'pure-white': '#ffffff',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'serif': ['Lora', 'serif'],
                    },
                }
            }
        }
    </script>

    <!-- Custom CSS for Editorial Neo-Brutalist Styles -->
    <style>
        body {
            background-color: #fbfaf7; /* Warm Alabaster */
            color: #111c24; /* Midnight Slate */
        }
        
        /* Signature Element: Sharp Editorial Frame */
        .editorial-card {
            background-color: #ffffff;
            border: 1px solid #111c24;
            border-radius: 6px;
            box-shadow: 3px 3px 0px #111c24;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .editorial-card:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #111c24;
        }
        
        .editorial-btn-primary {
            background-color: #d97706; /* Warm Amber */
            color: #ffffff;
            border: 1px solid #111c24;
            border-radius: 6px;
            box-shadow: 3px 3px 0px #111c24;
            font-weight: 600;
            transition: all 0.1s ease;
            display: inline-block;
        }
        
        .editorial-btn-primary:active {
            transform: translate(3px, 3px);
            box-shadow: 0px 0px 0px #111c24;
        }
        
        .editorial-btn-secondary {
            background-color: #ffffff;
            color: #111c24;
            border: 1px solid #111c24;
            border-radius: 6px;
            box-shadow: 3px 3px 0px #111c24;
            font-weight: 600;
            transition: all 0.1s ease;
        }
        
        .editorial-btn-secondary:active {
            transform: translate(3px, 3px);
            box-shadow: 0px 0px 0px #111c24;
        }

        /* Status Badges */
        .status-badge {
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.25rem 0.75rem;
            border: 1px solid #111c24;
            border-radius: 4px;
        }
        
        .badge-tersedia {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .badge-terpakai {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Navigation */
        .nav-link {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            color: #111c24;
            transition: color 0.2s ease;
            padding-bottom: 2px;
        }
        
        .nav-link:hover {
            color: #d97706;
        }
        
        .nav-link.active {
            border-bottom: 3px solid #d97706;
            font-weight: 600;
        }

        /* Segmented Control (Tabs) */
        .segmented-control {
            display: inline-flex;
            background-color: #eae8df;
            border: 1px solid #111c24;
            border-radius: 6px;
            padding: 4px;
            overflow-x: auto;
            max-width: 100%;
        }
        
        .segmented-control::-webkit-scrollbar { display: none; }
        
        .segment-btn {
            padding: 8px 20px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            color: #555;
            white-space: nowrap;
        }
        
        .segment-btn.active {
            background-color: #ffffff;
            color: #111c24;
            border: 1px solid #111c24;
            box-shadow: 2px 2px 0px #111c24;
        }
        
        .segment-btn:not(.active):hover {
            color: #111c24;
        }

        /* Utilities */
        .metadata-icon {
            color: #111c24;
            opacity: 0.7;
        }

        .building-section { display: none; animation: fadeInUp 0.3s ease; }
        .building-section.active { display: block; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    </style>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <!-- Sticky Header -->
    <header class="sticky top-0 z-50 bg-alabaster border-b border-midnight/10 backdrop-blur-md">
        <div class="max-w-[1200px] mx-auto px-6 h-20 flex items-center justify-between">
            <!-- Brand -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-warm border border-midnight shadow-[2px_2px_0px_#111c24] flex items-center justify-center rounded-sm">
                    <span class="font-serif font-bold text-white text-lg">S</span>
                </div>
                <h1 class="font-serif font-bold text-2xl tracking-tight text-midnight hidden sm:block">SIPERKA</h1>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="index.php" class="nav-link active">Beranda</a>
                <a href="status.php" class="nav-link">Jadwal Global</a>
                <?php if ($isLoggedIn): ?>
                    <a href="history.php" class="nav-link">Riwayat Saya</a>
                <?php endif; ?>
            </nav>

            <!-- User Action -->
            <div class="flex items-center gap-4">
                <?php if ($isLoggedIn): ?>
                    <div class="flex items-center gap-3">
                        <a href="auth/logout.php" class="editorial-btn-secondary text-sm px-3 py-2 ml-2 text-red-600">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="editorial-btn-primary text-sm px-4 py-2">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Layout Container -->
    <main class="flex-grow max-w-[1200px] w-full mx-auto px-6 py-12">
        
        <!-- Page Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="max-w-2xl">
                <h2 class="font-serif text-4xl md:text-5xl font-bold leading-tight mb-4">Eksplorasi & <br/>Reservasi Ruangan.</h2>
                <p class="text-lg text-midnight/80 font-sans">Akses direktori fasilitas kampus secara real-time. Temukan ruangan yang sesuai dengan kebutuhan akademik Anda.</p>
            </div>
            
            <!-- Segmented Control for Buildings -->
            <div class="segmented-control shrink-0">
                <?php foreach ($gedungs as $index => $g): ?>
                    <button class="segment-btn <?= $index === 0 ? 'active' : '' ?>" id="tab-<?= $g['kode'] ?>" onclick="switchBuilding('<?= $g['kode'] ?>')">
                        <?= htmlspecialchars($g['nama']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Building Sections -->
        <?php foreach ($gedungs as $index => $g): ?>
            <div class="building-section <?= $index === 0 ? 'active' : '' ?>" id="section-<?= $g['kode'] ?>">
                
                <div class="mb-6 pb-4 border-b border-midnight/10">
                    <h3 class="font-serif text-xl font-bold"><?= htmlspecialchars($g['nama']) ?></h3>
                    <p class="text-midnight/70 text-sm mt-1"><?= htmlspecialchars($g['deskripsi']) ?></p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <?php 
                    $ruangans = $ruanganPerGedung[$g['id']] ?? [];
                    if (empty($ruangans)): ?>
                        <div class="col-span-1 lg:col-span-2 py-8 text-center border-2 border-dashed border-midnight/20 rounded-md">
                            <p class="text-midnight/60 font-medium">Tidak ada ruangan aktif di gedung ini.</p>
                        </div>
                    <?php else: 
                        foreach ($ruangans as $r): ?>
                            <div class="editorial-card p-6 flex flex-col h-full">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <span class="status-badge badge-tersedia mb-3 inline-block">Tersedia</span>
                                        <h3 class="font-serif text-2xl font-bold"><?= htmlspecialchars($r['nama']) ?></h3>
                                    </div>
                                </div>
                                
                                <div class="h-px w-full bg-midnight/10 mb-5"></div>
                                
                                <!-- Metadata -->
                                <div class="grid grid-cols-2 gap-4 mb-8 flex-grow">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-users metadata-icon"></i>
                                        <span class="text-sm font-medium">Kapasitas <?= $r['kapasitas'] ?></span>
                                    </div>
                                    <div class="flex items-center gap-3 col-span-2 md:col-span-1">
                                        <i class="fa-solid fa-layer-group metadata-icon"></i>
                                        <span class="text-sm font-medium line-clamp-2"><?= htmlspecialchars($r['fasilitas']) ?></span>
                                    </div>
                                </div>
                                
                                <a href="booking_form.php?ruangan_id=<?= $r['id'] ?>" class="editorial-btn-primary w-full py-3 px-4 text-center mt-auto">
                                    Pesan Ruangan
                                </a>
                            </div>
                        <?php endforeach; 
                    endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </main>
    
    <!-- Footer -->
    <footer class="mt-auto border-t border-midnight/10 py-8 bg-pure-white">
        <div class="max-w-[1200px] mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="font-serif font-bold text-lg text-midnight">SIPERKA.</p>
            <p class="text-sm text-midnight/60 font-medium">&copy; 2026 Sistem Peminjaman Ruangan Akademik. All rights reserved.</p>
        </div>
    </footer>

    <!-- Mobile Nav (Bottom Bar) for very small screens -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-midnight/10 flex justify-around p-3 z-50">
        <a href="index.php" class="flex flex-col items-center text-amber-warm">
            <i class="fa-solid fa-home mb-1"></i>
            <span class="text-[10px] font-bold">Beranda</span>
        </a>
        <a href="status.php" class="flex flex-col items-center text-midnight/60">
            <i class="fa-solid fa-calendar-alt mb-1"></i>
            <span class="text-[10px] font-medium">Jadwal</span>
        </a>
        <?php if ($isLoggedIn): ?>
            <a href="history.php" class="flex flex-col items-center text-midnight/60">
                <i class="fa-solid fa-clock-rotate-left mb-1"></i>
                <span class="text-[10px] font-medium">Riwayat</span>
            </a>
        <?php endif; ?>
    </div>

    <script>
        function switchBuilding(id) {
            document.querySelectorAll('.segment-btn').forEach(tab => tab.classList.remove('active'));
            document.getElementById('tab-' + id).classList.add('active');
            
            document.querySelectorAll('.building-section').forEach(sec => sec.classList.remove('active'));
            document.getElementById('section-' + id).classList.add('active');
        }
    </script>
</body>
</html>
