<?php
require_once __DIR__ . '/config.php';
try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare('INSERT INTO users (name, username, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Administrator', 'admin', password_hash('Admin123!', PASSWORD_DEFAULT), 'admin']);
    }
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute(['staff']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare('INSERT INTO users (name, username, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Staff Gudang', 'staff', password_hash('Staff123!', PASSWORD_DEFAULT), 'staff']);
    }
    $pdo->exec("INSERT IGNORE INTO categories (name, description) VALUES ('Elektronik','Perangkat elektronik'),('ATK','Alat tulis kantor'),('Peralatan','Peralatan umum')");
    echo '<h2>Setup selesai.</h2><p>Admin: <b>admin</b> / <b>Admin123!</b></p><p>Staff: <b>staff</b> / <b>Staff123!</b></p><p><strong>Hapus atau nonaktifkan setup.php setelah selesai.</strong></p><p><a href="login.php">Ke halaman login</a></p>';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Setup gagal. Pastikan schema.sql sudah diimpor dan konfigurasi database benar. Detail: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
