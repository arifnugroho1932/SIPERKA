<?php
session_start();
require_once 'config/db.php';

// Ambil semua jadwal peminjaman yang disetujui (atau berlangsung/akan datang)
$stmt = $pdo->prepare("SELECT p.*, r.nama as nama_ruangan, u.nama as nama_peminjam 
                       FROM peminjaman p 
                       JOIN ruangan r ON p.ruangan_id = r.id 
                       JOIN users u ON p.user_id = u.id 
                       WHERE p.status = 'disetujui' 
                       ORDER BY p.tanggal ASC, p.waktu_mulai ASC");
$stmt->execute();
$jadwal = $stmt->fetchAll();

// Siapkan data untuk FullCalendar
$events = [];
foreach ($jadwal as $j) {
    $events[] = [
        'title' => $j['nama_ruangan'] . ' - ' . $j['nama_kegiatan'],
        'start' => $j['tanggal'] . 'T' . $j['waktu_mulai'],
        'end' => $j['tanggal'] . 'T' . $j['waktu_selesai'],
        'description' => 'Peminjam: ' . $j['nama_peminjam']
    ];
}
$eventsJson = json_encode($events);
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPERKA - Jadwal Global</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

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

    <style>
        body { background-color: #fbfaf7; color: #111c24; }
        
        .editorial-card {
            background-color: #ffffff; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .editorial-btn-primary {
            background-color: #d97706; color: #ffffff; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24; font-weight: 600;
        }
        .editorial-btn-secondary {
            background-color: #ffffff; color: #111c24; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24; font-weight: 600;
        }

        .status-badge { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.25rem 0.75rem; border: 1px solid #111c24; border-radius: 4px; display: inline-block; }
        .badge-pending { background-color: #fef08a; color: #854d0e; }
        .badge-terpakai { background-color: #fee2e2; color: #991b1b; }

        .nav-link { font-family: 'Inter', sans-serif; font-weight: 500; color: #111c24; transition: color 0.2s ease; padding-bottom: 2px; }
        .nav-link:hover { color: #d97706; }
        .nav-link.active { border-bottom: 3px solid #d97706; font-weight: 600; }
        
        /* FullCalendar Customizations */
        .fc-toolbar-title { font-family: 'Lora', serif; font-weight: 700; color: #111c24; }
        .fc-button-primary { background-color: #111c24 !important; border-color: #111c24 !important; box-shadow: 2px 2px 0px #d97706 !important; border-radius: 4px !important; text-transform: capitalize !important;}
        .fc-button-primary:not(:disabled).fc-button-active, .fc-button-primary:not(:disabled):active { background-color: #d97706 !important; border-color: #111c24 !important; box-shadow: 0px 0px 0px #111c24 !important; }
        .fc-theme-standard th, .fc-theme-standard td, .fc-theme-standard th { border-color: rgba(17, 28, 36, 0.1) !important; }
        .fc-event { border: 1px solid #111c24 !important; border-radius: 2px !important; box-shadow: 1px 1px 0px #111c24 !important; }
    </style>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <!-- Sticky Header -->
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
                <a href="status.php" class="nav-link active">Jadwal Global</a>
                <?php if ($isLoggedIn): ?>
                    <a href="history.php" class="nav-link">Riwayat Saya</a>
                <?php endif; ?>
            </nav>

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

    <main class="flex-grow max-w-[1200px] w-full mx-auto px-6 py-12">
        <div class="mb-10">
            <h2 class="font-serif text-4xl md:text-5xl font-bold leading-tight mb-4">Jadwal Global.</h2>
            <p class="text-lg text-midnight/80 font-sans">Pantau ketersediaan ruangan dan jadwal kegiatan yang sedang atau akan berlangsung.</p>
        </div>

        <div class="editorial-card p-4 md:p-8 mb-12">
            <div id="calendar"></div>
        </div>

        <div class="mt-8">
            <h3 class="font-serif text-2xl font-bold mb-6 border-b border-midnight/10 pb-4">Daftar Peminjaman Mendatang</h3>
            
            <?php if (empty($jadwal)): ?>
                <div class="py-12 text-center border-2 border-dashed border-midnight/20 rounded-md">
                    <p class="text-midnight/60 font-medium">Belum ada jadwal peminjaman ruangan yang disetujui.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($jadwal as $item): 
                    $isToday = ($item['tanggal'] === date('Y-m-d'));
                    $now = date('H:i:s');
                    $isOngoing = $isToday && ($item['waktu_mulai'] <= $now) && ($item['waktu_selesai'] >= $now);
                    $badgeClass = $isOngoing ? 'badge-terpakai' : 'badge-pending';
                    $badgeLabel = $isOngoing ? 'Berlangsung' : 'Akan Datang';
                ?>
                    <div class="editorial-card p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="status-badge <?= $badgeClass ?> mb-2"><?= $badgeLabel ?></span>
                                <h4 class="font-serif text-xl font-bold"><?= htmlspecialchars($item['nama_ruangan']) ?></h4>
                            </div>
                        </div>
                        <div class="h-px w-full bg-midnight/10 mb-4"></div>
                        <div class="text-sm font-medium text-midnight/80 mb-1"><i class="fa-solid fa-user mr-2 opacity-70"></i><?= htmlspecialchars($item['nama_peminjam']) ?></div>
                        <div class="text-sm font-medium text-midnight/80 mb-3"><i class="fa-solid fa-tag mr-2 opacity-70"></i><?= htmlspecialchars($item['nama_kegiatan']) ?></div>
                        
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="text-sm font-bold"><i class="fa-solid fa-calendar mr-2 opacity-70"></i><?= date('d M Y', strtotime($item['tanggal'])) ?></div>
                            <div class="text-sm font-bold"><i class="fa-solid fa-clock mr-2 opacity-70"></i><?= date('H:i', strtotime($item['waktu_mulai'])) ?> - <?= date('H:i', strtotime($item['waktu_selesai'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
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
        <a href="status.php" class="flex flex-col items-center text-amber-warm">
            <i class="fa-solid fa-calendar-alt mb-1"></i><span class="text-[10px] font-bold">Jadwal</span>
        </a>
        <?php if ($isLoggedIn): ?>
            <a href="history.php" class="flex flex-col items-center text-midnight/60">
                <i class="fa-solid fa-clock-rotate-left mb-1"></i><span class="text-[10px] font-medium">Riwayat</span>
            </a>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: { today: 'Hari Ini', month: 'Bulan', week: 'Minggu', day: 'Hari' },
                events: <?= $eventsJson ?>,
                eventColor: '#111c24',
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                allDaySlot: false,
                height: 'auto',
                eventClick: function(info) {
                    alert('Kegiatan: ' + info.event.title + '\n' + info.event.extendedProps.description);
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>
