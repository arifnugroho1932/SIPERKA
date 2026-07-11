<?php
session_start();
require_once 'config/db.php';
require_once 'auth/middleware.php';
requireLogin();

$ruangan_id = $_GET['ruangan_id'] ?? null;
if (!$ruangan_id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, g.nama as nama_gedung FROM ruangan r JOIN gedung g ON r.gedung_id = g.id WHERE r.id = ? AND r.status = 'aktif'");
$stmt->execute([$ruangan_id]);
$ruangan = $stmt->fetch();

if (!$ruangan) {
    die("Ruangan tidak ditemukan atau tidak aktif.");
}

// jadwal kuliah per ruangan
$stmtJadwal = $pdo->prepare("
    SELECT hari, jam_mulai, jam_selesai, mata_kuliah
    FROM jadwal_kuliah
    WHERE ruangan_id = ?
    ORDER BY 
        CASE hari 
            WHEN 'Senin'  THEN 1 
            WHEN 'Selasa' THEN 2 
            WHEN 'Rabu'   THEN 3 
            WHEN 'Kamis'  THEN 4 
            WHEN 'Jumat'  THEN 5 
            WHEN 'Sabtu'  THEN 6 
            WHEN 'Minggu' THEN 7 
        END ASC,
        jam_mulai ASC
");
$stmtJadwal->execute([$ruangan_id]);
$jadwalRows = $stmtJadwal->fetchAll();

// jadwal perhari
$jadwalPerHari = [];
foreach ($jadwalRows as $row) {
    $jadwalPerHari[$row['hari']][] = $row;
}
$hariUrutan = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPERKA - Form Peminjaman</title>

    
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
        .editorial-card { background-color: #ffffff; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24; }
        .editorial-btn-primary { background-color: #d97706; color: #ffffff; border: 1px solid #111c24; border-radius: 6px; box-shadow: 3px 3px 0px #111c24; font-weight: 600; transition: all 0.1s ease; cursor: pointer; text-align: center; }
        .editorial-btn-primary:active { transform: translate(3px, 3px); box-shadow: 0px 0px 0px #111c24; }
        .editorial-input { width: 100%; border: 1px solid #111c24; border-radius: 4px; padding: 0.75rem; font-family: 'Inter', sans-serif; transition: all 0.2s ease; background-color: #ffffff; }
        .editorial-input:focus { outline: none; box-shadow: 2px 2px 0px #d97706; border-color: #d97706; }
        .editorial-label { display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem; color: #111c24; }
    </style>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <header class="sticky top-0 z-50 bg-alabaster border-b border-midnight/10 backdrop-blur-md">
        <div class="max-w-[1200px] mx-auto px-6 h-20 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 text-midnight hover:text-amber-warm transition-colors font-medium">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <h1 class="font-serif font-bold text-xl tracking-tight text-midnight hidden sm:block">Form Peminjaman</h1>
            <div class="w-20"></div> <!-- Spacer for flex balance -->
        </div>
    </header>

    <main class="flex-grow w-full max-w-2xl mx-auto px-6 py-12">

    <!-- ===== JADWAL KULIAH ===== -->
    <?php if (!empty($jadwalPerHari)): ?>
        <section class="card" style="margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.25rem;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #FFF4E5; display:flex; align-items:center; justify-content:center; font-size: 1.1rem; flex-shrink:0;">📅</div>
                <div>
                    <h3 style="margin: 0; font-size: 1rem; color: var(--text-dark);">Jadwal Kuliah Ruangan Ini</h3>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--text-light);">Pilih tanggal di form bawah untuk otomatis melihat jadwal hari tersebut</p>
                </div>
            </div>

            <!-- Tab Hari -->
            <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 1rem;">
                <?php $firstTab = true; foreach ($hariUrutan as $hari): if (!isset($jadwalPerHari[$hari])) continue; ?>
                <button
                    class="tab-btn"
                    onclick="showTab('<?= $hari ?>')"
                    id="tab-<?= $hari ?>"
                    style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease;
                    border: 2px solid <?= $firstTab ? '#d97706' : '#E5E7EB' ?>; 
                    background: <?= $firstTab ? '#d97706' : '#fff' ?>; 
                    color: <?= $firstTab ? '#fff' : '#4B5563' ?>;"
    ><?= $hari ?></button>
                <?php $firstTab = false; endforeach; ?>
            </div>

            <!-- Konten per Hari -->
            <?php $firstContent = true; foreach ($hariUrutan as $hari): if (!isset($jadwalPerHari[$hari])) continue; ?>
            <div id="jadwal-content-<?= $hari ?>" class="jadwal-content" style="display: <?= $firstContent ? 'block' : 'none' ?>;">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php foreach ($jadwalPerHari[$hari] as $j): ?>
                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0.85rem; background: #FFF8F0; border-left: 3px solid var(--warning); border-radius: 8px;">
                        <div style="flex-shrink:0; font-size:0.78rem; font-weight:800; color: var(--warning); white-space: nowrap;">
                            <?= date('H:i', strtotime($j['jam_mulai'])) ?> – <?= date('H:i', strtotime($j['jam_selesai'])) ?>
                        </div>
                        <div style="width: 1px; height: 28px; background: #d97706; flex-shrink:0;"></div>
                        <div style="font-size:0.875rem; color: var(--text-dark); font-weight: 500;">
                            <?= htmlspecialchars($j['mata_kuliah']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php $firstContent = false; endforeach; ?>

            <p style="margin-top: 1rem; margin-bottom: 0; font-size: 0.78rem; color: var(--text-light); text-align: center;">
                Peminjaman pada waktu di atas akan otomatis ditolak sistem.
            </p>
        </section>
        <?php endif; ?>
            
        <div class="editorial-card p-6 md:p-10">
            <div class="flex items-start gap-4 mb-8 pb-6 border-b border-midnight/10">
                <div class="w-14 h-14 bg-amber-warm border border-midnight shadow-[2px_2px_0px_#111c24] flex items-center justify-center rounded-sm shrink-0">
                    <i class="fa-solid fa-door-open text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="font-serif text-2xl font-bold text-midnight leading-tight"><?= htmlspecialchars($ruangan['nama']) ?></h2>
                    <p class="text-sm text-midnight/70 font-medium mt-1">Kapasitas: <?= $ruangan['kapasitas'] ?> Orang • <?= htmlspecialchars($ruangan['nama_gedung']) ?></p>
                </div>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'konflik'): ?>
                <div class="mb-6 p-4 border border-[#111c24] bg-[#fee2e2] shadow-[3px_3px_0px_#111c24] rounded-md font-bold text-[#991b1b]">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i>Jadwal bertabrakan! Ruangan sudah dipakai di waktu tersebut.
                </div>
            <?php endif; ?>

            <form action="actions/ajukan_peminjaman.php" method="POST" class="space-y-6">
                <input type="hidden" name="ruangan_id" value="<?= $ruangan['id'] ?>">
                
                <div>
                    <label for="kegiatan" class="editorial-label">Nama Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" id="kegiatan" name="kegiatan" class="editorial-input" placeholder="Contoh: Rapat Koordinasi BEM" required>
                </div>
                
                <div>
                    <label for="tanggal" class="editorial-label">Tanggal Peminjaman <span class="text-red-500">*</span></label>
                    <input type="date" id="tanggal" name="tanggal" class="editorial-input" required min="<?= date('Y-m-d') ?>">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="waktu_mulai" class="editorial-label">Waktu Mulai <span class="text-red-500">*</span></label>
                        <input type="time" id="waktu_mulai" name="waktu_mulai" class="editorial-input" required>
                    </div>
                    <div>
                        <label for="waktu_selesai" class="editorial-label">Waktu Selesai <span class="text-red-500">*</span></label>
                        <input type="time" id="waktu_selesai" name="waktu_selesai" class="editorial-input" required>
                    </div>
                </div>

                <div>
                    <label for="peserta" class="editorial-label">Perkiraan Jumlah Peserta <span class="text-red-500">*</span></label>
                    <input type="number" id="peserta" name="peserta" class="editorial-input" placeholder="Contoh: 30" max="<?= $ruangan['kapasitas'] ?>" required>
                    <p class="text-xs text-midnight/60 mt-1">Maksimal: <?= $ruangan['kapasitas'] ?> orang.</p>
                </div>

                <div>
                    <label for="keterangan" class="editorial-label">Keterangan Tambahan (Opsional)</label>
                    <textarea id="keterangan" name="keterangan" class="editorial-input min-h-[100px]" placeholder="Alat tambahan yang diperlukan, dsb."></textarea>
                </div>

                <div class="pt-6 border-t border-midnight/10">
                    <button type="submit" class="editorial-btn-primary w-full py-4 text-lg">Ajukan Peminjaman</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Tab switching untuk jadwal kuliah
        function showTab(hari) {
            document.querySelectorAll('.jadwal-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.style.background    = '#fff';
                btn.style.color         = 'var(--text-light)';
                btn.style.borderColor   = '#E5E7EB';
            });
            const content = document.getElementById('jadwal-content-' + hari);
            if (content) content.style.display = 'block';
            const activeBtn = document.getElementById('tab-' + hari);
            if (activeBtn) {
                activeBtn.style.background  = '#d97706';
                activeBtn.style.color       = '#fff';
                activeBtn.style.borderColor = '#d97706';
            }
        }

        // Otomatis pindah ke tab hari yang sesuai saat user memilih tanggal
        const namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        document.getElementById('tanggal').addEventListener('change', function () {
            if (!this.value) return;
            const parts = this.value.split('-');
            const d     = new Date(parts[0], parts[1] - 1, parts[2]);
            const hari  = namaHari[d.getDay()];
            if (document.getElementById('tab-' + hari)) {
                showTab(hari);
                // Scroll lembut ke jadwal agar user langsung melihat
                document.querySelector('.jadwal-content[style*="block"]')
                    ?.closest('section')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    </script>

</body>
</html>
