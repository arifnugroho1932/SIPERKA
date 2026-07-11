<?php
session_start();
require_once '../config/db.php';
require_once '../auth/middleware.php';
require_once '../config/mail.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $alasan = trim($_POST['alasan_penolakan'] ?? '');
    
    if ($id) {
        $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'ditolak', alasan_penolakan = ? WHERE id = ?");
        $stmt->execute([$alasan, $id]);

        // Kirim email notifikasi
        $stmtPeminjaman = $pdo->prepare("
            SELECT p.nama_kegiatan, p.tanggal, r.nama as ruangan_nama, u.email, u.nama as peminjam_nama
            FROM peminjaman p
            JOIN ruangan r ON p.ruangan_id = r.id
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmtPeminjaman->execute([$id]);
        $data = $stmtPeminjaman->fetch();

        if ($data && !empty($data['email'])) {
            $alasanText = !empty($alasan) ? $alasan : "Tidak ada alasan spesifik yang diberikan.";
            $subject = "Permohonan Ditolak: " . $data['ruangan_nama'];
            $body = "Halo " . $data['peminjam_nama'] . ",\n\n"
                  . "Mohon maaf, permohonan peminjaman ruangan Anda untuk kegiatan '" . $data['nama_kegiatan'] . "' "
                  . "pada tanggal " . $data['tanggal'] . " telah DITOLAK oleh Admin.\n\n"
                  . "Alasan Penolakan:\n"
                  . $alasanText . "\n\n"
                  . "Silakan ajukan peminjaman untuk waktu atau ruangan yang berbeda.\n\n"
                  . "Salam,\nSistem SIPERKA";

            sendSimpleEmail($data['email'], $subject, $body);
        }
    }
    header("Location: ../admin_bookings.php?sukses=tolak");
    exit;
} else {
    header("Location: ../admin_bookings.php");
    exit;
}