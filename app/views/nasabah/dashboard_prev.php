<?php
/**
 * Dashboard Nasabah - Go Green Village Design
 *
 * @var array|null $flash
 * @var array $nasabah
 * @var float $saldo
 * @var float $saldoTersedia
 * @var float $totalTerikat
 * @var array $riwayatTerbaru
 * @var array $pengajuanAktif
 * @var array $user
 */

function formatRupiah(float $number): string {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function formatTanggalIndonesia(string $datetime): string {
    $bulan = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
    $date = strtotime($datetime);
    return (int)date('j', $date) . ' ' . $bulan[(int)date('n', $date)] . ' ' . date('Y', $date);
}

function getGreeting(): string {
    $hour = (int) date('H');
    if ($hour < 12) return 'Selamat Pagi';
    if ($hour < 15) return 'Selamat Siang';
    if ($hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
}

$user = currentUser();
$greeting = getGreeting();

/** @var array|null $flash */
/** @var array $nasabah */
/** @var float $saldo */
/** @var float $saldoTersedia */
/** @var float $totalTerikat */
/** @var array $riwayatTerbaru */
/** @var array $pengajuanAktif */
/** @var array $user */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        :root {
            --green-primary: #2E7D32;
            --green-dark: #1B5E20;
            --bg-warm: #FDFBF7;
            --bg-white: #FFFFFF;
            --bg-soft: #F5F5F0;
            --text-primary: #3D4A3C;
            --text-secondary: #6B7B6E;
            --text-muted: #9CA39D;
            --border-light: #E8E8E3;
            --success: #2E7D32;
            --warning: #F57C00;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-md: 0 2px 8px rgba(0,0,0,0.06);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg-warm); color: var(--text-primary); line-height: 1.5; min-height: 100vh; }
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, var(--green-primary) 0%, var(--green-dark) 100%); color: white; flex-shrink: 0; position: fixed; top: 0; left: 0; height: 100vh; z-index: 100; }
        .sidebar-header { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; }
        .sidebar-logo svg { width: 32px; height: 32px; flex-shrink: 0; }
        .sidebar-logo-text { font-size: 1rem; font-weight: 600; line-height: 1.2; }
        .sidebar-logo-sub { font-size: 0.75rem; opacity: 0.8; }
        .sidebar-nav { padding: 16px 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: rgba(255,255,255,0.75); text-decoration: none; border-left: 3px solid transparent; transition: all 0.2s; }
        .sidebar-nav a:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar-nav a.active { background: rgba(255,255,255,0.12); color: white; border-left-color: #66BB6A; }
        .sidebar-nav a svg { width: 20px; height: 20px; flex-shrink: 0; }
        .sidebar-nav a.logout { margin-top: 24px; color: rgba(255,200,200,0.8); }
        .main { flex: 1; margin-left: 260px; padding: 32px 40px; max-width: 900px; }
        .flash { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-radius: var(--radius-md); margin-bottom: 28px; font-size: 0.9rem; }
        .flash-success { background: #E8F5E9; color: #1B5E20; }
        .flash-error { background: #FFEBEE; color: #C62828; }
        .flash svg { width: 18px; height: 18px; flex-shrink: 0; }
        .greeting { margin-bottom: 32px; }
        .greeting-name { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); }
        .greeting-location { font-size: 0.9rem; color: var(--text-secondary); margin-top: 4px; }
        .balance-card { background: var(--bg-white); border-radius: var(--radius-lg); padding: 28px 32px; margin-bottom: 24px; box-shadow: var(--shadow-md); border: 1px solid var(--border-light); }
        .balance-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .balance-label { font-size: 0.85rem; font-weight: 500; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px; }
        .balance-label svg { width: 18px; height: 18px; color: var(--green-primary); }
        .balance-amount { font-size: 2.25rem; font-weight: 700; color: var(--text-primary); line-height: 1.1; }
        .balance-actions { margin-top: 24px; display: flex; gap: 12px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 20px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 600; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer; }
        .btn svg { width: 18px; height: 18px; }
        .btn-primary { background: var(--green-primary); color: white; }
        .btn-primary:hover { background: var(--green-dark); }
        .btn-secondary { background: var(--bg-soft); color: var(--text-primary); border: 1px solid var(--border-light); }
        .btn-secondary:hover { background: var(--border-light); }
        .metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
        .metric { background: var(--bg-white); border-radius: var(--radius-md); padding: 20px 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-light); }
        .metric-header { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
        .metric-icon { width: 32px; height: 32px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
        .metric-icon svg { width: 16px; height: 16px; }
        .metric-icon.success { background: #E8F5E9; color: var(--success); }
        .metric-icon.warning { background: #FFF3E0; color: var(--warning); }
        .metric-label { font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.3px; }
        .metric-value { font-size: 1.25rem; font-weight: 600; color: var(--text-primary); }
        .withdrawal-card { background: #FFF8E1; border-radius: var(--radius-md); padding: 20px 24px; margin-bottom: 32px; border-left: 4px solid var(--warning); }
        .withdrawal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .withdrawal-title { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-primary); }
        .withdrawal-title svg { width: 18px; height: 18px; color: var(--warning); }
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-warning { background: #FFE0B2; color: #E65100; }
        .badge-success { background: #C8E6C9; color: #2E7D32; }
        .withdrawal-info { font-size: 0.9rem; color: var(--text-secondary); }
        .withdrawal-info strong { color: var(--text-primary); }
        .section { margin-bottom: 32px; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .section-title { font-size: 1rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 8px; }
        .section-title svg { width: 18px; height: 18px; color: var(--green-primary); }
        .section-link { font-size: 0.85rem; color: var(--green-primary); text-decoration: none; font-weight: 500; }
        .section-link:hover { text-decoration: underline; }
        .activity-list { background: var(--bg-white); border-radius: var(--radius-md); border: 1px solid var(--border-light); overflow: hidden; }
        .activity-item { display: flex; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border-light); }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; flex-shrink: 0; margin-right: 14px; }
        .activity-icon.in { background: #E8F5E9; color: var(--success); }
        .activity-icon.out { background: #FFF3E0; color: var(--warning); }
        .activity-icon.adjust { background: #E3F2FD; color: #1565C0; }
        .activity-content { flex: 1; min-width: 0; }
        .activity-type { font-size: 0.9rem; font-weight: 500; color: var(--text-primary); }
        .activity-date { font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; }
        .activity-amount { font-size: 0.95rem; font-weight: 600; flex-shrink: 0; margin-left: 16px; }
        .activity-amount.positive { color: var(--success); }
        .activity-amount.negative { color: var(--warning); }
        .empty-state { background: var(--bg-white); border-radius: var(--radius-md); padding: 48px 24px; text-align: center; border: 1px solid var(--border-light); }
        .empty-icon { width: 64px; height: 64px; margin: 0 auto 20px; background: #E8F5E9; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .empty-icon svg { width: 28px; height: 28px; color: var(--green-primary); }
        .empty-title { font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
        .empty-desc { font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 24px; }
        .mobile-header { display: none; position: fixed; top: 0; left: 0; right: 0; height: 56px; background: linear-gradient(135deg, var(--green-primary) 0%, var(--green-dark) 100%); color: white; align-items: center; justify-content: space-between; padding: 0 16px; z-index: 101; }
        .mobile-logo { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.95rem; }
        .mobile-logo svg { width: 28px; height: 28px; }
        .hamburger { width: 44px; height: 44px; background: transparent; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; }
        .hamburger span { display: block; width: 22px; height: 2px; background: white; border-radius: 2px; transition: all 0.3s; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 99; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .mobile-header { display: flex; }
            .sidebar-overlay.active { display: block; }
            .main { margin-left: 0; margin-top: 56px; padding: 24px 20px; }
            .balance-card { padding: 24px 20px; }
            .balance-amount { font-size: 1.75rem; }
            .balance-actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
            .metrics { grid-template-columns: 1fr; }
            .greeting-name { font-size: 1.25rem; }
            .withdrawal-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
        @media (max-width: 480px) {
            .main { padding: 20px 16px; }
            .balance-card { padding: 20px 16px; }
            .balance-amount { font-size: 1.5rem; }
            .balance-label { font-size: 0.8rem; }
            .activity-item { padding: 14px 16px; }
            .metric { padding: 16px 20px; }
        }
    </style>
</head>
<body>
    <header class="mobile-header">
        <div class="mobile-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            <span><?= APP_NAME ?></span>
        </div>
        <button class="hamburger" id="hamburgerBtn"><span></span><span></span><span></span></button>
    </header>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="dashboard">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    <div><div class="sidebar-logo-text"><?= APP_NAME ?></div><div class="sidebar-logo-sub">Bank Sampah Digital</div></div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="<?= base_url('nasabah/dashboard') ?>" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Dashboard</a>
                <a href="<?= base_url('nasabah/setor') ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>Setor Sampah</a>
                <a href="<?= base_url('nasabah/riwayat') ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>Riwayat Transaksi</a>
                <a href="<?= base_url('nasabah/cairkan') ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Ajukan Pencairan</a>
                <a href="<?= base_url('nasabah/profil') ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Profil Saya</a>
                <a href="<?= base_url('logout') ?>" class="logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Logout</a>
            </nav>
        </aside>
        <main class="main">
            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?php if ($flash['type'] === 'success'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><?php endif; ?>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>
            <div class="greeting">
                <h1 class="greeting-name"><?= $greeting ?>, <?= htmlspecialchars($user['nama'] ?? 'Nasabah') ?></h1>
                <p class="greeting-location"><?= htmlspecialchars($nasabah['bank_sampah_nama'] ?? 'Bank Sampah') ?></p>
            </div>
            <div class="balance-card">
                <div class="balance-header">
                    <div class="balance-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>Tabungan Hijau Kamu</div>
                </div>
                <div class="balance-amount"><?= formatRupiah($saldo) ?></div>
                <div class="balance-actions">
                    <a href="<?= base_url('nasabah/setor') ?>" class="btn btn-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Setor Sampah</a>
                    <a href="<?= base_url('nasabah/cairkan') ?>" class="btn btn-secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Cairkan</a>
                </div>
            </div>
            <div class="metrics">
                <div class="metric">
                    <div class="metric-header"><div class="metric-icon success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div><span class="metric-label">Bisa Dicairkan</span></div>
                    <div class="metric-value"><?= formatRupiah($saldoTersedia) ?></div>
                </div>
                <div class="metric">
                    <div class="metric-header"><div class="metric-icon warning"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><span class="metric-label">Dalam Pengajuan</span></div>
                    <div class="metric-value"><?= formatRupiah($totalTerikat) ?></div>
                </div>
            </div>
            <?php if (!empty($pengajuanAktif)): ?>
                <div class="withdrawal-card">
                    <div class="withdrawal-header">
                        <div class="withdrawal-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Pengajuan Aktif</div>
                        <?php foreach ($pengajuanAktif as $p): ?><span class="badge <?= $p['status'] === 'DISETUJUI' ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars($p['status']) ?></span><?php endforeach; ?>
                    </div>
                    <?php foreach ($pengajuanAktif as $p): ?><p class="withdrawal-info"><strong><?= htmlspecialchars($p['no_pengajuan']) ?></strong> <?= formatRupiah($p['jumlah']) ?> &middot; <?= htmlspecialchars($p['metode']) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Aktivitas Terakhir</h2>
                    <a href="<?= base_url('nasabah/riwayat') ?>" class="section-link">Lihat semua</a>
                </div>
                <?php if (!empty($riwayatTerbaru)): ?>
                    <div class="activity-list">
                        <?php foreach ($riwayatTerbaru as $row): ?>
                            <?php $iconClass = 'adjust'; $sign = ''; if ($row['tipe'] === 'SETOR') { $iconClass = 'in'; $sign = '+'; } elseif ($row['tipe'] === 'PENCAIRAN') { $iconClass = 'out'; $sign = '-'; } ?>
                            <div class="activity-item">
                                <div class="activity-icon <?= $iconClass ?>">
                                    <?php if ($row['tipe'] === 'SETOR'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <?php elseif ($row['tipe'] === 'PENCAIRAN'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg><?php endif; ?>
                                </div>
                                <div class="activity-content"><div class="activity-type"><?= htmlspecialchars($row['tipe']) ?></div><div class="activity-date"><?= formatTanggalIndonesia($row['created_at']) ?></div></div>
                                <div class="activity-amount <?= $row['jumlah'] >= 0 ? 'positive' : 'negative' ?>"><?= $sign ?><?= formatRupiah(abs($row['jumlah'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg></div>
                        <h3 class="empty-title">Belum Ada Aktivitas</h3>
                        <p class="empty-desc">Mulai setor sampah untuk melihat aktivitas Anda di sini.</p>
                        <a href="<?= base_url('nasabah/setor') ?>" class="btn btn-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Setor Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        (function() {
            const hamburger = document.getElementById('hamburgerBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('active'); }
            function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
            hamburger.addEventListener('click', function() { sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
            overlay.addEventListener('click', closeSidebar);
            document.querySelectorAll('.sidebar-nav a').forEach(function(link) { link.addEventListener('click', function() { if (window.innerWidth <= 768) closeSidebar(); }); });
            window.addEventListener('resize', function() { if (window.innerWidth > 768) closeSidebar(); });
        })();
    </script>
</body>
</html>