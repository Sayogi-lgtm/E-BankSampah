<?php
// Profil Saya - Halaman untuk melihat dan mengedit profil nasabah, Compatible dengan PHP 7.3+

$nasabah = $nasabah ?? [];
$oldInput = $oldInput ?? [];
$errors = $errors ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style type="text/css">
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            padding: 0;
            flex-shrink: 0;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar-header small { opacity: 0.8; }
        .sidebar nav { padding: 1rem 0; }
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.875rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .sidebar nav a:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar nav a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: #81c784;
        }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }

        /* Flash Messages */
        .flash {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1b5e20; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #2e7d32; text-decoration: none; }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .card-header h3 { margin: 0; font-size: 1.1rem; color: #333; }
        .card-body { padding: 1.5rem; }

        /* Form */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-grid-full {
            grid-column: 1 / -1;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }
        .form-text {
            display: block;
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #2e7d32;
            color: white;
        }
        .btn-primary:hover {
            background: #1b5e20;
        }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #d0d0d0;
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        /* Info Box */
        .info-box {
            background: #f0f7f0;
            border: 1px solid #c8e6c9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .info-box label {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .info-box .value {
            font-size: 1rem;
            font-weight: 600;
            color: #1b5e20;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🌿 <?= APP_NAME ?></h2>
                <small>Bank Sampah Digital</small>
            </div>
            <nav>
                <a href="<?= base_url('nasabah/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('nasabah/setor') ?>">🗑️ Setor Sampah</a>
                <a href="<?= base_url('nasabah/riwayat') ?>">📜 Riwayat Transaksi</a>
                <a href="<?= base_url('nasabah/cairkan') ?>">💰 Ajukan Pencairan</a>
                <a href="<?= base_url('nasabah/profil') ?>" class="active">👤 Profil Saya</a>
                <a href="<?= base_url('logout') ?>" style="margin-top: 1rem; color: #ffcdd2;">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if (isset($flash) && $flash): ?>
                <div class="flash flash-<?= $flash['type'] === 'error' ? 'error' : $flash['type'] ?>">
                    <?php if ($flash['type'] === 'success'): ?>✓<?php elseif ($flash['type'] === 'error'): ?>✕<?php else: ?>ℹ<?php endif; ?>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>👤 Profil Saya</h1>
                    <div class="breadcrumb">
                        <a href="<?= base_url('nasabah/dashboard') ?>">Dashboard</a> / Profil Saya
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Informasi Akun</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <label>Nomor Anggota</label>
                        <div class="value"><?= htmlspecialchars($nasabah['no_anggota'] ?? '-') ?></div>
                    </div>
                    <div class="info-box">
                        <label>Nomor HP Terdaftar</label>
                        <div class="value"><?= htmlspecialchars($nasabah['no_hp'] ?? '-') ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Edit Data Pribadi</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <?php foreach ($errors as $error): ?>
                            <div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <form method="POST" action="<?= base_url('nasabah/profil') ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nama" class="form-label">Nama Lengkap *</label>
                                <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($oldInput['nama'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="no_hp" class="form-label">Nomor HP *</label>
                                <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($oldInput['no_hp'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="alamat" class="form-label">Alamat</label>
                                <input type="text" id="alamat" name="alamat" class="form-control" placeholder="Jln. Contoh No. 123" value="<?= htmlspecialchars($oldInput['alamat'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="rt" class="form-label">RT</label>
                                <input type="text" id="rt" name="rt" class="form-control" maxlength="5" placeholder="01" value="<?= htmlspecialchars($oldInput['rt'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="rw" class="form-label">RW</label>
                                <input type="text" id="rw" name="rw" class="form-control" maxlength="5" placeholder="05" value="<?= htmlspecialchars($oldInput['rw'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="nama_bank" class="form-label">Nama Bank</label>
                                <input type="text" id="nama_bank" name="nama_bank" class="form-control" placeholder="BRI, BNI, Mandiri, dll" value="<?= htmlspecialchars($oldInput['nama_bank'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="no_rekening" class="form-label">Nomor Rekening</label>
                                <input type="text" id="no_rekening" name="no_rekening" class="form-control" placeholder="123456789012" value="<?= htmlspecialchars($oldInput['no_rekening'] ?? '') ?>">
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <button type="submit" name="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                            <a href="<?= base_url('nasabah/dashboard') ?>" class="btn btn-secondary" style="text-decoration: none;">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
