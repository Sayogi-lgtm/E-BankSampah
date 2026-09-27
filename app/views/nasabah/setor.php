<?php
// Form Penyetoran Sampah - Menyetorkan sampah dan mendapatkan nilai saldo, Compatible dengan PHP 7.3+

function formatRupiah($number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

$saldo = $saldo ?? 0;
$nasabah = $nasabah ?? [];
$hargaSampah = $hargaSampah ?? [];
$errors = $errors ?? [];
$oldInput = $oldInput ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor Sampah - <?= APP_NAME ?></title>
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
        .flash-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }

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

        /* Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .card-header h3 { margin: 0; font-size: 1.1rem; color: #333; }
        .card-body { padding: 1.5rem; }

        /* Form */
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

        /* Sidebar Card */
        .sidebar-card {
            background: #f0f7f0;
            border: 1px solid #c8e6c9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .sidebar-card label {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .sidebar-card .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1b5e20;
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

        /* Harga List */
        .harga-list {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 1rem;
        }
        .harga-item {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        .harga-item:last-child {
            border-bottom: none;
        }
        .harga-item .kategori {
            color: #333;
            font-weight: 500;
        }
        .harga-item .harga {
            color: #2e7d32;
            font-weight: 600;
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
                <a href="<?= base_url('nasabah/setor') ?>" class="active">🗑️ Setor Sampah</a>
                <a href="<?= base_url('nasabah/riwayat') ?>">📜 Riwayat Transaksi</a>
                <a href="<?= base_url('nasabah/cairkan') ?>">💰 Ajukan Pencairan</a>
                <a href="<?= base_url('nasabah/profil') ?>">👤 Profil Saya</a>
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
                    <h1>🗑️ Setor Sampah</h1>
                    <div class="breadcrumb">
                        <a href="<?= base_url('nasabah/dashboard') ?>">Dashboard</a> / Setor Sampah
                    </div>
                </div>
            </div>

            <div class="content-grid">
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Form Penyetoran Sampah</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <?php foreach ($errors as $error): ?>
                                    <div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <form method="POST" action="<?= base_url('nasabah/setor') ?>">
                                <div class="form-group">
                                    <label for="kategori_id" class="form-label">Kategori Sampah *</label>
                                    <select id="kategori_id" name="kategori_id" class="form-control" required onchange="updateHarga()">
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php foreach ($hargaSampah as $h): ?>
                                            <option value="<?= htmlspecialchars($h['kategori_id']) ?>" <?= (($oldInput['kategori_id'] ?? '') == $h['kategori_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($h['kategori_nama']) ?> - <?= formatRupiah($h['harga_beli']) ?>/kg
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="berat" class="form-label">Berat Sampah (kg) *</label>
                                    <input type="number" id="berat" name="berat" class="form-control" step="0.01" min="0" placeholder="0.00" value="<?= htmlspecialchars($oldInput['berat'] ?? '') ?>" required onchange="updateTotal()">
                                    <span class="form-text">Masukkan berat sampah yang akan disetorkan</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Nilai Setoran</label>
                                    <div style="font-size: 1.25rem; font-weight: 700; color: #2e7d32; padding: 0.75rem; background: #f0f7f0; border-radius: 8px;">
                                        <span id="total-nilai">Rp 0</span>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 1rem;">
                                    <button type="submit" name="submit" class="btn btn-primary" style="flex: 1;">🗑️ Setor Sekarang</button>
                                    <a href="<?= base_url('nasabah/dashboard') ?>" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Saldo Anda</h3>
                        </div>
                        <div class="card-body">
                            <div class="sidebar-card">
                                <label>Saldo Tabungan</label>
                                <div class="value"><?= formatRupiah($saldo) ?></div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h3 style="font-size: 1rem;">Daftar Harga Saat Ini</h3>
                                </div>
                                <div class="card-body">
                                    <div class="harga-list">
                                        <?php if (!empty($hargaSampah)): ?>
                                            <?php foreach ($hargaSampah as $h): ?>
                                                <div class="harga-item">
                                                    <span class="kategori"><?= htmlspecialchars($h['kategori_nama']) ?></span>
                                                    <span class="harga"><?= formatRupiah($h['harga_beli']) ?>/kg</span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p style="color: #999; margin: 0;">Belum ada harga yang ditetapkan</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const hargaSampahData = <?php echo json_encode($hargaSampah); ?>;

        function updateHarga() {
            updateTotal();
        }

        function updateTotal() {
            const kategoriId = document.getElementById('kategori_id').value;
            const berat = parseFloat(document.getElementById('berat').value) || 0;

            let harga = 0;
            for (let item of hargaSampahData) {
                if (item.kategori_id == kategoriId) {
                    harga = parseFloat(item.harga_beli);
                    break;
                }
            }

            const total = berat * harga;
            const totalElement = document.getElementById('total-nilai');
            totalElement.textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
        }
    </script>
</body>
</html>
