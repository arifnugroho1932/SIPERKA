<?php
session_start();
require_once '../config/db.php';
require_once '../auth/middleware.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $ruangan_id = (int)$_POST['ruangan_id'];
    $mata_kuliah = trim($_POST['mata_kuliah']);
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    if (empty($id) || empty($ruangan_id) || empty($mata_kuliah) || empty($hari) || empty($jam_mulai) || empty($jam_selesai)) {
        header("Location: ../admin_jadwal.php?error=system");
        exit;
    }

    if (strtotime($jam_mulai) >= strtotime($jam_selesai)) {
        header("Location: ../admin_jadwal.php?error=jam_tidak_valid");
        exit;
    }

    try {
        // Cek overlap dengan jadwal lain (kecuali jadwal yang sedang diedit)
        $stmtCek = $pdo->prepare("
            SELECT id FROM jadwal_kuliah 
            WHERE ruangan_id = ? AND hari = ? AND id != ?
            AND (jam_mulai < ? AND jam_selesai > ?)
        ");
        $stmtCek->execute([
            $ruangan_id, $hari, $id,
            $jam_selesai, $jam_mulai
        ]);

        if ($stmtCek->rowCount() > 0) {
            header("Location: ../admin_jadwal.php?error=bentrok");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE jadwal_kuliah SET ruangan_id = ?, mata_kuliah = ?, hari = ?, jam_mulai = ?, jam_selesai = ? WHERE id = ?");
        $stmt->execute([$ruangan_id, $mata_kuliah, $hari, $jam_mulai, $jam_selesai, $id]);

        header("Location: ../admin_jadwal.php?sukses=edit");
        exit;
    } catch (PDOException $e) {
        header("Location: ../admin_jadwal.php?error=system");
        exit;
    }
}
header("Location: ../admin_jadwal.php");
exit;