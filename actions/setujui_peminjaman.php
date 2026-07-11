<?php
session_start();
require_once '../config/db.php';
require_once '../auth/middleware.php';
require_once '../config/mail.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'disetujui' WHERE id = ?");
        $stmt->execute([$id]);

        // Kirim email notifikasi
        $stmtPeminjaman = $pdo->prepare("
            SELECT p.nama_kegiatan, p.tanggal, p.waktu_mulai, p.waktu_selesai, r.nama as ruangan_nama, u.email, u.nama as peminjam_nama
            FROM peminjaman p
            JOIN ruangan r ON p.ruangan_id = r.id
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmtPeminjaman->execute([$id]);
        $data = $stmtPeminjaman->fetch();

        if ($data && !empty($data['email'])) {
            $subject = "Permohonan Disetujui: " . $data['ruangan_nama'];
            $body = "Halo " . $data['peminjam_nama'] . ",\n\n"
                  . "Selamat! Permohonan peminjaman ruangan Anda telah DISETUJUI oleh Admin.\n\n"
                  . "Detail Peminjaman:\n"
                  . "- Ruangan: " . $data['ruangan_nama'] . "\n"
                  . "- Kegiatan: " . $data['nama_kegiatan'] . "\n"
                  . "- Tanggal: " . $data['tanggal'] . "\n"
                  . "- Waktu: " . $data['waktu_mulai'] . " s/d " . $data['waktu_selesai'] . "\n\n"
                  . "Harap menjaga kebersihan dan fasilitas ruangan selama penggunaan.\n\n"
                  . "Salam,\nSistem SIPERKA";

            sendSimpleEmail($data['email'], $subject, $body);
        }
    }
    header("Location: ../admin_bookings.php?sukses=setuju");
    exit;
} else {
    header("Location: ../admin_bookings.php");
    exit;
}