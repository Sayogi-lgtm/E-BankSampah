<?php
/**
 * Dashboard Nasabah
 *
 * Menampilkan saldo tabungan dan ringkasan aktivitas terbaru
 * Compatible dengan PHP 7.3+
 *
 * @var array|null $flash Flash message from controller
 * @var array $nasabah Nasabah data from controller
 * @var float $saldo Total saldo tabungan
 * @var float $saldoTersedia Saldo yang bisa dicairkan
 * @var float $totalTerikat Total saldo dalam pengajuan
 * @var array $riwayatTerbaru Array transaksi terbaru
 * @var array $pengajuanAktif Array pengajuan aktif
 * @var array $user User data from session
 */

// Format uang ke Rupiah Indonesia
/**
 * Format number to Indonesian Rupiah
 * @param int|float $number
 * @return string
 */
function formatRupiah($number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

// Format tanggal Indonesia
/**
 * Format datetime to Indonesian date string (date only, no time)
 * @param string|int $datetime
 * @return string
 */
function formatTanggalIndonesia($datetime): string
{
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $date = is_string($datetime) ? strtotime($datetime) : $datetime;
    $day = date('j', $date);
    $month = $bulan[(int) date('n', $date)];
    $year = date('Y', $date);

    return $day . ' ' . $month . ' ' . $year;
}

// Get greeting based on time of day
/**
 * Get contextual greeting based on current hour
 * @return string
 */
function getGreeting(): string
{
    $hour = (int) date('H');
    if ($hour < 12) {
        return 'Selamat Pagi';
    } elseif ($hour < 15) {
        return 'Selamat Siang';
    } elseif ($hour < 18) {
        return 'Selamat Sore';
    } else {
        return 'Selamat Malam';
    }
}

// Get badge class for status
/**
 * Get CSS class for status badge
 * @param string $tipe Transaction type (SETOR, PENCAIRAN, KOREKSI)
 * @return string
 */
function getStatusBadgeClass(string $tipe): string
{
    switch ($tipe) {
        case 'SETOR':
            return 'badge-success';
        case 'PENCAIRAN':
            return 'badge-warning';
        case 'KOREKSI':
            return 'badge-info';
        default:
            return 'badge-secondary';
    }
}

$user = currentUser();
$greeting = getGreeting();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        /* ============================================
            DESIGN TOKENS - Go Green Village V2
           ============================================ */
        :root {
            /* Primary Greens */
            --forest-canopy: #1B5E20;
            --fresh-leaf: #2E7D32;
            --spring-leaf: #43A047;
            --new-growth: #66BB6A;
            --morning-dew: #A5D6A7;

            /* Nature Neutrals */
            --warm-paper: #FDFBF7;
            --clean-white: #FFFFFF;
            --soft-soil: #F5F5F0;
            --moss-gray: #6B7B6E;
            --earth-dark: #3D4A3C;

            /* Semantic */
            --success: #2E7D32;
            --growth: #43A047;
            --warning: #F57C00;
            --error: #D32F2F;
            --info: #1565C0;

            /* Spacing */
            --space-xs: 4px;
            --space-sm: 8px;
            --space-md: 16px;
            --space-lg: 24px;
            --space-xl: 32px;
            --space-2xl: 48px;

            /* Shape */
            --radius-btn: 8px;
            --radius-card: 12px;
            --radius-hero: 16px;
            --radius-badge: 20px;
        }

        /* Base Styles with Warm Paper */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--warm-paper);
            overflow-x: hidden;
            color: var(--earth-dark);
        }

        /* ============================================
           SVG ICON SYSTEM - Consistent Leaf-Inspired
           ============================================ */
        .icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .icon-lg {
            width: 24px;
            height: 24px;
        }
        .icon-xl {
            width: 32px;
            height: 32px;
        }
        .icon-container {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #E8F5E9;
            flex-shrink: 0;
        }
        .icon-container-lg {
            width: 48px;
            height: 48px;
            border-radius: 12px;
        }

        /* ============================================
           LEAF MOTIF SYSTEM
           ============================================ */
        .leaf-pattern {
            position: absolute;
            pointer-events: none;
            opacity: 0.06;
        }
        .leaf-pattern-subtle {
            opacity: 0.04;
        }

        /* Leaf SVG - Single Leaf */
        .leaf-decoration {
            position: absolute;
            width: 120px;
            height: auto;
            opacity: 0.08;
            pointer-events: none;
        }
        .leaf-decoration-sm {
            width: 60px;
            opacity: 0.06;
        }
        .leaf-top-right {
            top: 0;
            right: 0;
        }
        .leaf-bottom-left {
            bottom: 0;
            left: 0;
            transform: rotate(180deg);
        }

        /* ============================================
           MOBILE HEADER - Green Gradient
           ============================================ */
        .dashboard-wrapper { display: flex; min-height: 100vh; }

        /* Mobile Header - visible only on mobile */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            z-index: 1001;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .mobile-header-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
        }
        .hamburger {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        .hamburger span {
            display: block;
            width: 22px;
            height: 2px;
            background: white;
            margin: 3px 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--fresh-leaf) 0%, var(--forest-canopy) 100%);
            color: white;
            padding: 0;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 999;
            transition: transform 0.3s ease;
            overflow-y: auto;
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
            border-left-color: var(--new-growth);
        }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; margin-left: 260px; }

        /* Flash Messages */
        .flash {
            padding: var(--space-md) var(--space-lg);
            border-radius: var(--radius-btn);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-md);
            font-size: 0.95rem;
        }
        .flash-success { background: #D4EDDA; color: #155724; border: 1px solid #C3E6CB; }
        .flash-error { background: #F8D7DA; color: #721C24; border: 1px solid #F5C6CB; }
        .flash-warning { background: #FFF3CD; color: #856404; border: 1px solid #FFEeba; }
        .flash-info { background: #D1ECF1; color: #0C5460; border: 1px solid #BEE5EB; }

        /* Page Header */
        .page-header { margin-bottom: var(--space-xl); }
        .page-header h1 { margin: 0 0 var(--space-sm) 0; font-size: 1.5rem; color: var(--forest-canopy); font-weight: 700; }
        .page-header p { margin: 0; color: var(--moss-gray); font-size: 0.95rem; }

        /* Cards */
        .card {
            background: var(--clean-white);
            border-radius: var(--radius-card);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .card-header {
            padding: var(--space-lg) var(--space-lg);
            border-bottom: 1px solid var(--soft-soil);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h3 { margin: 0; font-size: 1rem; color: var(--earth-dark); font-weight: 600; display: flex; align-items: center; gap: var(--space-sm); }
        .card-body { padding: var(--space-lg); }

        /* Hero CTA - Primary Action with Leaf Motif */
        .hero-cta {
            background: linear-gradient(135deg, var(--fresh-leaf) 0%, var(--spring-leaf) 50%, var(--new-growth) 100%);
            border-radius: var(--radius-hero);
            padding: var(--space-xl) var(--space-xl) calc(var(--space-xl) - 4px);
            margin-bottom: var(--space-xl);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-lg);
            flex-wrap: wrap;
            box-shadow: 0 4px 20px rgba(46, 125, 50, 0.25);
            position: relative;
            overflow: hidden;
        }
        .hero-cta-content { flex: 1; min-width: 200px; position: relative; z-index: 2; }
        .hero-cta h2 {
            margin: 0 0 var(--space-sm) 0;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        .hero-cta p {
            margin: 0;
            opacity: 0.92;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .hero-cta .btn-hero {
            background: var(--clean-white);
            color: var(--fresh-leaf);
            padding: 0.875rem 1.75rem;
            border-radius: var(--radius-btn);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            transition: all 0.2s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            white-space: nowrap;
            position: relative;
            z-index: 2;
        }
        .hero-cta .btn-hero:hover {
            background: var(--warm-paper);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .hero-cta .btn-hero:active {
            transform: translateY(0);
        }
        .hero-cta .btn-hero svg {
            width: 18px;
            height: 18px;
        }
        @media (max-width: 600px) {
            .hero-cta {
                flex-direction: column;
                text-align: center;
                padding: var(--space-lg);
            }
            .hero-cta h2 {
                font-size: 1.2rem;
                justify-content: center;
            }
            .hero-cta .btn-hero {
                width: 100%;
                justify-content: center;
            }
            .leaf-decoration {
                width: 80px;
                opacity: 0.04;
            }
        }

        /* Saldo Cards */
        .saldo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-xl);
        }
        .saldo-card {
            background: var(--clean-white);
            border-radius: var(--radius-card);
            padding: var(--space-lg);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: relative;
        }
        .saldo-card.saldo-utama {
            background: linear-gradient(135deg, var(--fresh-leaf) 0%, var(--forest-canopy) 100%);
            color: white;
        }
        .saldo-card.saldo-tersedia {
            background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
            color: white;
        }
        .saldo-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: var(--space-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .saldo-amount {
            font-size: 1.75rem;
            font-weight: 700;
        }
        .saldo-card.saldo-terikat {
            background: var(--clean-white);
            border: 1px solid var(--morning-dew);
        }
        .saldo-card.saldo-terikat .saldo-amount {
            color: var(--warning);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: var(--space-md);
            margin-top: var(--space-md);
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-btn);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-primary { background: var(--fresh-leaf); color: white; }
        .btn-primary:hover { background: var(--forest-canopy); }
        .btn-secondary { background: var(--soft-soil); color: var(--earth-dark); }
        .btn-secondary:hover { background: var(--moss-gray); color: white; }
        .btn-warning { background: var(--warning); color: white; }
        .btn-warning:hover { background: #E65100; }

        /* Table */
        .table-responsive { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: var(--space-md);
            text-align: left;
            border-bottom: 1px solid var(--soft-soil);
        }
        th {
            background: var(--soft-soil);
            font-weight: 600;
            color: var(--moss-gray);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:hover { background: var(--warm-paper); }
        td { color: var(--earth-dark); }

        /* Badges */
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-badge);
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-success { background: #E8F5E9; color: var(--success); }
        .badge-warning { background: #FFF3E0; color: var(--warning); }
        .badge-info { background: #E3F2FD; color: var(--info); }
        .badge-secondary { background: var(--soft-soil); color: var(--moss-gray); }

        /* Empty State - Friendly Version */
        .empty-state {
            text-align: center;
            padding: var(--space-2xl) var(--space-lg);
            color: var(--moss-gray);
            position: relative;
        }
        .empty-state-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto var(--space-lg);
            background: #E8F5E9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .empty-state h3 {
            margin: 0 0 var(--space-sm) 0;
            font-size: 1.1rem;
            color: var(--earth-dark);
        }
        .empty-state p {
            margin: 0 0 var(--space-lg) 0;
            font-size: 0.9rem;
            color: var(--moss-gray);
            line-height: 1.5;
        }
        .empty-state .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            background: linear-gradient(135deg, var(--fresh-leaf) 0%, var(--spring-leaf) 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-btn);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(46, 125, 50, 0.3);
        }
        .empty-state .btn-cta:hover {
            background: linear-gradient(135deg, var(--forest-canopy) 0%, var(--fresh-leaf) 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.4);
        }

        /* Pengajuan Aktif */
        .pengajuan-item {
            background: var(--clean-white);
            border: 1px solid var(--soft-soil);
            border-radius: var(--radius-btn);
            padding: var(--space-md);
            margin-bottom: var(--space-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pengajuan-info h4 { margin: 0 0 4px 0; font-size: 0.95rem; color: var(--earth-dark); }
        .pengajuan-info p { margin: 0; font-size: 0.85rem; color: var(--moss-gray); }
        .pengajuan-status .badge { font-size: 0.8rem; }

        /* Quick Links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-md);
            margin-top: var(--space-lg);
        }
        .quick-link {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-md);
            background: var(--clean-white);
            border: 1px solid var(--soft-soil);
            border-radius: var(--radius-btn);
            text-decoration: none;
            color: var(--earth-dark);
            transition: all 0.2s;
        }
        .quick-link:hover {
            border-color: var(--fresh-leaf);
            background: #F1F8E9;
        }
        .quick-link-icon {
            width: 40px;
            height: 40px;
            background: #E8F5E9;
            border-radius: var(--radius-btn);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .quick-link-icon svg {
            width: 20px;
            height: 20px;
            color: var(--fresh-leaf);
        }
        .quick-link-text h4 { margin: 0; font-size: 0.95rem; }
        .quick-link-text p { margin: 0; font-size: 0.8rem; color: var(--moss-gray); }

        /* ============================================
           NEW DASHBOARD LAYOUT - Go Green Village Redesign
           ============================================ */

        /* Compact Greeting */
        .greeting-compact {
            margin-bottom: var(--space-lg);
        }
        .greeting-main {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--earth-dark);
            margin: 0;
        }
        .greeting-sub {
            font-size: 0.85rem;
            color: var(--moss-gray);
            margin: 4px 0 0 0;
        }

        /* Primary Balance - Hero Card */
        .balance-hero {
            background: linear-gradient(135deg, var(--fresh-leaf) 0%, var(--forest-canopy) 100%);
            border-radius: var(--radius-hero);
            padding: var(--space-lg) var(--space-xl);
            color: white;
            margin-bottom: var(--space-lg);
            position: relative;
            overflow: hidden;
        }
        .balance-hero::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            pointer-events: none;
        }
        .balance-hero-header {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-sm);
        }
        .balance-hero-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
        .balance-hero-label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }
        .balance-hero-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-md);
            flex-wrap: wrap;
        }
        .balance-hero-amount {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .balance-hero-cta {
            background: var(--clean-white);
            color: var(--fresh-leaf);
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-btn);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .balance-hero-cta:hover {
            background: var(--warm-paper);
            transform: translateY(-1px);
        }
        .balance-hero-cta svg {
            width: 16px;
            height: 16px;
        }

        /* Primary Actions - Horizontal Button Bar */
        .actions-bar {
            display: flex;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
        }
        .action-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-btn);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            min-height: 44px;
        }
        .action-btn svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        .action-btn-primary {
            background: var(--fresh-leaf);
            color: white;
        }
        .action-btn-primary:hover {
            background: var(--forest-canopy);
        }
        .action-btn-secondary {
            background: var(--clean-white);
            color: var(--earth-dark);
            border: 1px solid var(--soft-soil);
        }
        .action-btn-secondary:hover {
            border-color: var(--fresh-leaf);
            color: var(--fresh-leaf);
        }
        .action-btn-ghost {
            background: transparent;
            color: var(--moss-gray);
        }
        .action-btn-ghost:hover {
            color: var(--fresh-leaf);
        }

        /* Secondary Balance - Compact Metrics */
        .balance-secondary {
            background: var(--clean-white);
            border-radius: var(--radius-card);
            padding: var(--space-md) var(--space-lg);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-lg);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .balance-metric {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }
        .balance-metric-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .balance-metric-icon svg {
            width: 18px;
            height: 18px;
        }
        .balance-metric-icon-success {
            background: #E8F5E9;
            color: var(--success);
        }
        .balance-metric-icon-warning {
            background: #FFF3E0;
            color: var(--warning);
        }
        .balance-metric-content {
            display: flex;
            flex-direction: column;
        }
        .balance-metric-label {
            font-size: 0.75rem;
            color: var(--moss-gray);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .balance-metric-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--earth-dark);
        }
        .balance-divider {
            width: 1px;
            height: 40px;
            background: var(--soft-soil);
            flex-shrink: 0;
        }

        /* Section Header */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-md);
        }
        .section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--earth-dark);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        .section-title svg {
            width: 18px;
            height: 18px;
            color: var(--fresh-leaf);
        }
        .section-link {
            font-size: 0.8rem;
            color: var(--fresh-leaf);
            text-decoration: none;
            font-weight: 500;
        }
        .section-link:hover {
            text-decoration: underline;
        }

        /* Activity List */
        .activity-list {
            background: var(--clean-white);
            border-radius: var(--radius-card);
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .activity-item {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-md) var(--space-lg);
            border-bottom: 1px solid var(--soft-soil);
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .activity-content {
            flex: 1;
            min-width: 0;
        }
        .activity-title {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--earth-dark);
        }
        .activity-date {
            font-size: 0.75rem;
            color: var(--moss-gray);
            margin-top: 2px;
        }
        .activity-amount {
            font-size: 0.95rem;
            font-weight: 600;
            text-align: right;
            flex-shrink: 0;
        }
        .activity-amount-positive {
            color: var(--success);
        }
        .activity-amount-negative {
            color: var(--warning);
        }

        /* Active Withdrawal Card */
        .withdrawal-card {
            background: var(--clean-white);
            border-radius: var(--radius-card);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            border-left: 3px solid var(--warning);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .withdrawal-header {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-md);
        }
        .withdrawal-header svg {
            width: 18px;
            height: 18px;
            color: var(--warning);
        }
        .withdrawal-header h4 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--earth-dark);
        }
        .withdrawal-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-md);
        }
        .withdrawal-info p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--moss-gray);
        }
        .withdrawal-info p strong {
            color: var(--earth-dark);
        }

        /* ============================================
           MOBILE RESPONSIVE STYLES (max-width: 768px)
           ============================================ */
        @media (max-width: 768px) {
            /* Sidebar hidden by default on mobile */
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }

            /* Show mobile header */
            .mobile-header {
                display: flex !important;
            }

            /* Hide desktop sidebar on mobile */
            .sidebar-header {
                display: none;
            }

            /* Adjust main content area */
            .main-content {
                margin-top: 56px;
                margin-left: 0;
                padding: 1rem;
                width: 100%;
            }

            /* Page Header */
            .page-header {
                margin-bottom: 1.5rem;
            }
            .page-header h1 {
                font-size: 1.35rem;
                line-height: 1.3;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            .page-header p {
                font-size: 0.9rem;
            }

            /* Hero CTA - Full mobile optimization */
            .hero-cta {
                flex-direction: column;
                text-align: center;
                padding: 1.25rem;
                border-radius: 12px;
                gap: 1rem;
            }
            .hero-cta h2 {
                font-size: 1.2rem;
                line-height: 1.4;
            }
            .hero-cta p {
                font-size: 0.85rem;
                line-height: 1.5;
            }
            .hero-cta .btn-hero {
                width: 100%;
                justify-content: center;
                padding: 0.875rem 1.5rem;
                font-size: 0.95rem;
            }

            /* Saldo Cards - Stack vertically on mobile */
            .saldo-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin-bottom: 1.5rem;
            }
            .saldo-card {
                padding: 1.25rem;
            }
            .saldo-amount {
                font-size: 1.5rem;
            }

            /* Content Grid - Stack on mobile */
            .content-grid {
                display: flex !important;
                flex-direction: column;
                gap: 1.5rem;
            }

            /* Cards */
            .card-header {
                padding: 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .card-header h3 {
                font-size: 1rem;
            }
            .card-body {
                padding: 1rem;
            }

            /* Table - Make it scrollable but fit */
            .table-responsive {
                margin: 0 -1rem;
                padding: 0 1rem;
            }
            table {
                font-size: 0.8rem;
            }
            th, td {
                padding: 0.75rem 0.5rem;
                white-space: nowrap;
            }
            th:first-child, td:first-child {
                white-space: normal;
            }

            /* Quick Links */
            .quick-links {
                grid-template-columns: 1fr !important;
                margin: 0 !important;
            }
            .quick-link {
                padding: 0.875rem;
            }

            /* New Layout Mobile Styles */
            .balance-hero {
                padding: var(--space-lg);
            }
            .balance-hero-amount {
                font-size: 1.75rem;
            }
            .balance-hero-main {
                flex-direction: column;
                align-items: stretch;
            }
            .balance-hero-cta {
                justify-content: center;
                width: 100%;
            }
            .actions-bar {
                flex-wrap: wrap;
            }
            .action-btn {
                flex: 1 1 calc(50% - 4px);
                padding: 0.625rem 0.75rem;
                font-size: 0.8rem;
            }
            .action-btn-ghost {
                flex-basis: 100%;
            }
            .balance-secondary {
                flex-direction: column;
                align-items: stretch;
                gap: var(--space-md);
                padding: var(--space-md);
            }
            .balance-divider {
                display: none;
            }
            .balance-metric {
                padding: var(--space-sm) 0;
            }
            .activity-item {
                padding: var(--space-md);
            }
            .greeting-main {
                font-size: 1rem;
            }

            /* Flash Messages */
            .flash {
                padding: 0.875rem 1rem;
                font-size: 0.85rem;
            }

            /* Pengajuan Aktif */
            .pengajuan-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        /* Extra small screens (max-width: 480px) */
        @media (max-width: 480px) {
            .main-content {
                padding: 0.875rem;
            }

            .balance-hero {
                padding: var(--space-md);
            }
            .balance-hero-amount {
                font-size: 1.5rem;
            }
            .balance-hero-label {
                font-size: 0.75rem;
            }
            .action-btn {
                padding: 0.625rem 0.5rem;
                font-size: 0.75rem;
            }
            .action-btn svg {
                width: 16px;
                height: 16px;
            }
            .balance-metric-value {
                font-size: 1rem;
            }
            .activity-item {
                padding: var(--space-md) var(--space-sm);
            }
            .activity-amount {
                font-size: 0.85rem;
            }
            .greeting-main {
                font-size: 0.95rem;
            }
            .section-title {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Header -->
    <header class="mobile-header">
        <div class="mobile-header-logo">
            <svg class="icon-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2"/>
                <path d="M12 6c-1.5 0-3 2-3 5s1.5 5 3 5 3-2 3-5-1.5-5-3-5"/>
                <path d="M9 13c-2 0-4 1-4 3s2 3 4 3"/>
                <path d="M15 13c2 0 4 1 4 3s-2 3-4 3"/>
            </svg>
            <span><?= APP_NAME ?></span>
        </div>
        <button class="hamburger" id="hamburgerBtn" aria-label="Menu navigasi">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <!-- Sidebar Overlay (for mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>
                    <svg style="width:24px;height:24px;display:inline-block;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2"/>
                        <path d="M12 6c-1.5 0-3 2-3 5s1.5 5 3 5 3-2 3-5-1.5-5-3-5"/>
                        <path d="M9 13c-2 0-4 1-4 3s2 3 4 3"/>
                        <path d="M15 13c2 0 4 1 4 3s-2 3-4 3"/>
                    </svg>
                    <?= APP_NAME ?>
                </h2>
                <small>Bank Sampah Digital</small>
            </div>
            <nav>
                <a href="<?= base_url('nasabah/dashboard') ?>" class="active">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    Dashboard
                </a>
                <a href="<?= base_url('nasabah/setor') ?>">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    Setor Sampah
                </a>
                <a href="<?= base_url('nasabah/riwayat') ?>">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                    </svg>
                    Riwayat Transaksi
                </a>
                <a href="<?= base_url('nasabah/cairkan') ?>">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    Ajukan Pencairan
                </a>
                <a href="<?= base_url('nasabah/profil') ?>">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    Profil Saya
                </a>
                <a href="<?= base_url('logout') ?>" style="margin-top: 1rem; color: #FFCDD2;">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Flash Message -->
            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?php if ($flash['type'] === 'success'): ?>
                        <svg style="width:18px;height:18px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php elseif ($flash['type'] === 'error'): ?>
                        <svg style="width:18px;height:18px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <?php else: ?>
                        <svg style="width:18px;height:18px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <?php endif; ?>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- 1. COMPACT GREETING -->
            <div class="greeting-compact">
                <p class="greeting-main"><?= $greeting ?>, <?= htmlspecialchars($user['nama'] ?? 'Nasabah') ?></p>
                <p class="greeting-sub"><?= htmlspecialchars($nasabah['bank_sampah_nama'] ?? 'Bank Sampah') ?></p>
            </div>

            <!-- 2. PRIMARY BALANCE - Tabungan Hijau -->
            <div class="balance-hero">
                <div class="balance-hero-header">
                    <svg class="balance-hero-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2"/>
                        <path d="M12 6c-1.5 0-3 2-3 5s1.5 5 3 5 3-2 3-5-1.5-5-3-5"/>
                        <path d="M9 13c-2 0-4 1-4 3s2 3 4 3"/>
                        <path d="M15 13c2 0 4 1 4 3s-2 3-4 3"/>
                    </svg>
                    <span class="balance-hero-label">Tabungan Hijau Kamu</span>
                </div>
                <div class="balance-hero-main">
                    <div class="balance-hero-amount"><?= formatRupiah($saldo) ?></div>
                    <a href="<?= base_url('nasabah/setor') ?>" class="balance-hero-cta">
                        Setor Sampah
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- 3. PRIMARY ACTIONS -->
            <div class="actions-bar">
                <a href="<?= base_url('nasabah/setor') ?>" class="action-btn action-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    Setor
                </a>
                <a href="<?= base_url('nasabah/cairkan') ?>" class="action-btn action-btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    Cairkan
                </a>
                <a href="<?= base_url('nasabah/riwayat') ?>" class="action-btn action-btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Riwayat
                </a>
            </div>

            <!-- 4. SECONDARY BALANCE INFORMATION -->
            <div class="balance-secondary">
                <div class="balance-metric">
                    <div class="balance-metric-icon balance-metric-icon-success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <div class="balance-metric-content">
                        <span class="balance-metric-label">Bisa Dicairkan</span>
                        <span class="balance-metric-value"><?= formatRupiah($saldoTersedia) ?></span>
                    </div>
                </div>
                <div class="balance-divider"></div>
                <div class="balance-metric">
                    <div class="balance-metric-icon balance-metric-icon-warning">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="balance-metric-content">
                        <span class="balance-metric-label">Dalam Pengajuan</span>
                        <span class="balance-metric-value"><?= formatRupiah($totalTerikat) ?></span>
                    </div>
                </div>
            </div>

            <!-- 5. ACTIVE WITHDRAWAL REQUEST (only show when relevant) -->
            <?php if (!empty($pengajuanAktif)): ?>
                <div class="withdrawal-card">
                    <div class="withdrawal-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                        </svg>
                        <h4>Pengajuan Aktif</h4>
                    </div>
                    <?php foreach ($pengajuanAktif as $p): ?>
                        <div class="withdrawal-details">
                            <div class="withdrawal-info">
                                <p><strong><?= htmlspecialchars($p['no_pengajuan']) ?></strong></p>
                                <p><?= formatRupiah($p['jumlah']) ?> &bull; <?= htmlspecialchars($p['metode']) ?></p>
                            </div>
                            <span class="badge badge-<?= $p['status'] === 'DISETUJUI' ? 'success' : 'warning' ?>">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 6. RECENT ACTIVITY -->
            <div class="section-header">
                <h3 class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Aktivitas Terakhir
                </h3>
                <a href="<?= base_url('nasabah/riwayat') ?>" class="section-link">Lihat semua</a>
            </div>

            <div class="activity-list">
                <?php if (!empty($riwayatTerbaru)): ?>
                    <?php foreach ($riwayatTerbaru as $row): ?>
                        <div class="activity-item">
                            <div class="activity-icon badge-<?= strtolower($row['tipe']) === 'setor' ? 'success' : ($row['tipe'] === 'PENCAIRAN' ? 'warning' : 'info') ?>">
                                <?php
                                if ($row['tipe'] === 'SETOR') {
                                    echo '+';
                                } elseif ($row['tipe'] === 'PENCAIRAN') {
                                    echo '-';
                                } else {
                                    echo '~';
                                }
                                ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?= htmlspecialchars($row['tipe']) ?></div>
                                <div class="activity-date"><?= formatTanggalIndonesia($row['created_at']) ?></div>
                            </div>
                            <div class="activity-amount <?= $row['jumlah'] >= 0 ? 'activity-amount-positive' : 'activity-amount-negative' ?>">
                                <?= $row['jumlah'] >= 0 ? '+' : '' ?><?= formatRupiah($row['jumlah']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="padding: var(--space-xl);">
                        <div class="empty-state-icon">
                            <svg style="width:28px;height:28px;color:var(--fresh-leaf);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <h3>Belum Ada Aktivitas</h3>
                        <p>Mulai setor sampah untuk melihat aktivitas Anda di sini.</p>
                        <a href="<?= base_url('nasabah/setor') ?>" class="btn-cta">
                            <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="8" x2="12" y2="16"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                            </svg>
                            Setor Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Mobile Navigation JavaScript -->
    <script>
        (function() {
            const hamburger = document.getElementById('hamburgerBtn');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.querySelector('.main-content');

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('active');
                hamburger.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                hamburger.classList.remove('active');
                document.body.style.overflow = '';
            }

            hamburger.addEventListener('click', function() {
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            overlay.addEventListener('click', closeSidebar);

            // Close sidebar when clicking nav links on mobile
            sidebar.querySelectorAll('nav a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeSidebar();
                }
            });
        })();
    </script>
</body>
</html>
