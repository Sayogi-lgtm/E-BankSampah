<?php
/**
 * Setup Script: Add Nasabah Users - Web Version
 * Akses via browser: http://localhost/webalizer/Project-Backend-BankSampah/database/setup_nasabah_web.php
 */

require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Nasabah - Bank Sampah</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2e7d32;
            text-align: center;
        }
        .success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .user-card {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 10px 0;
            border-radius: 0 5px 5px 0;
        }
        .user-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .user-card p {
            margin: 5px 0;
            color: #666;
        }
        .login-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .login-info code {
            background: #fff;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #218838;
        }
        .error {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏦 Setup Nasabah Users</h1>

<?php
try {
    $db = getDbConnection();

    echo '<div class="success">✅ Berhasil terhubung ke database!</div>';

    // 1. Insert user NASABAH
    echo "<h2>📝 1. Menambahkan Users</h2>";

    $users = [
        [4, 'Budi Santoso', '081234567811', 'budi@banksampah.id'],
        [5, 'Siti Aminah', '081234567812', 'siti@banksampah.id'],
        [6, 'Warung Makan Sedap', '081234567813', 'warung@banksampah.id'],
    ];

    $hash = '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG';

    $stmt = $db->prepare("
        INSERT INTO users (id, desa_id, bank_sampah_id, nama, no_hp, email, password_hash, role, status)
        VALUES (:id, 1, 1, :nama, :no_hp, :email, :hash, 'NASABAH', 'AKTIF')
        ON DUPLICATE KEY UPDATE
            nama = VALUES(nama),
            role = 'NASABAH'
    ");

    foreach ($users as $u) {
        $stmt->execute([
            'id' => $u[0],
            'nama' => $u[1],
            'no_hp' => $u[2],
            'email' => $u[3],
            'hash' => $hash
        ]);
        echo "
        <div class='user-card'>
            <h3>{$u[1]}</h3>
            <p><strong>ID:</strong> {$u[0]}</p>
            <p><strong>No. HP:</strong> {$u[2]}</p>
            <p><strong>Email:</strong> {$u[3]}</p>
        </div>";
    }

    // 2. Update nasabah table
    echo "<h2>🔗 2. Menghubungkan ke Tabel Nasabah</h2>";

    $stmt2 = $db->prepare("UPDATE nasabah SET user_id = :user_id WHERE no_hp = :no_hp");

    foreach ($users as $u) {
        $stmt2->execute([
            'user_id' => $u[0],
            'no_hp' => $u[2]
        ]);
        echo "<p>✅ {$u[1]} -> user_id = {$u[0]}</p>";
    }

    echo "
        <div class='success'>
            <h2>✅ Setup Complete!</h2>
        </div>

        <div class='login-info'>
            <h3>🔑 Login Test Credentials</h3>
            <p><strong>Password untuk semua user:</strong> <code>password123</code></p>
            <hr>
            <p>📱 <strong>Budi Santoso:</strong> No HP <code>081234567811</code></p>
            <p>📱 <strong>Siti Aminah:</strong> No HP <code>081234567812</code></p>
            <p>📱 <strong>Warung Makan Sedap:</strong> No HP <code>081234567813</code></p>
        </div>

        <a href='../index.php' class='btn'>← Kembali ke Home</a>";

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ ERROR</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

    </div>
</body>
</html>
