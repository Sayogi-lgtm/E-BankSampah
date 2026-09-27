<?php
/**
 * Nasabah Controller
 *
 * Controller untuk modul Nasabah
 * Compatible dengan PHP 7.3+
 */

class NasabahController
{
    /** @var Nasabah */
    private $nasabahModel;

    /** @var MutasiSaldo */
    private $mutasiModel;

    /** @var PengajuanPencairan */
    private $pencairanModel;

    /** @var KategoriSampah */
    private $kategoriModel;

    /** @var HargaSampah */
    private $hargaModel;

    public function __construct()
    {
        // Require login untuk semua method
        requireLogin();
        // Require role NASABAH
        requireRole(['NASABAH'], 'dashboard');

        // Load models
        require_once MODEL_PATH . '/Nasabah.php';
        require_once MODEL_PATH . '/MutasiSaldo.php';
        require_once MODEL_PATH . '/PengajuanPencairan.php';
        require_once MODEL_PATH . '/KategoriSampah.php';
        require_once MODEL_PATH . '/HargaSampah.php';

        $this->nasabahModel = new Nasabah();
        $this->mutasiModel = new MutasiSaldo();
        $this->pencairanModel = new PengajuanPencairan();
        $this->kategoriModel = new KategoriSampah();
        $this->hargaModel = new HargaSampah();
    }

    /**
     * Get data Nasabah dari session user_id
     * Mengambil dari database untuk memastikan data valid
     *
     * @return array|null
     */
    private function getCurrentNasabah()
    {
        $userId = currentUserId();
        if (!$userId) {
            return null;
        }
        return $this->nasabahModel->findByUserId($userId);
    }

    /**
     * Dashboard - Tampilkan ringkasan saldo dan aktivitas terbaru
     */
    public function dashboard(): void
    {
        $nasabah = $this->getCurrentNasabah();

        if (!$nasabah) {
            setFlashMessage('error', 'Data akun tidak ditemukan.');
            redirect('logout');
            return;
        }

        $saldo = $this->nasabahModel->getSaldo($nasabah['id']);
        $saldoTersedia = $this->nasabahModel->getSaldoTersedia($nasabah['id']);
        $riwayatTerbaru = $this->mutasiModel->getRiwayat($nasabah['id'], 5);

        // Ambil pengajuan aktif
        $pengajuanAktif = $this->pencairanModel->getAktifByNasabah($nasabah['id']);
        $totalTerikat = $this->pencairanModel->getTotalTerikat($nasabah['id']);

        $user = currentUser();
        $flash = getFlashMessage();

        include VIEW_PATH . '/nasabah/dashboard.php';
    }

    /**
     * Riwayat - Tampilkan semua riwayat mutasi saldo
     */
    public function riwayat(): void
    {
        $nasabah = $this->getCurrentNasabah();

        if (!$nasabah) {
            setFlashMessage('error', 'Data akun tidak ditemukan.');
            redirect('logout');
            return;
        }

        // Pagination
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Ambil data
        $riwayat = $this->mutasiModel->getRiwayatLengkap($nasabah['id'], $perPage, $offset);
        $totalData = $this->mutasiModel->countRiwayat($nasabah['id']);
        $totalPages = max(1, ceil($totalData / $perPage));

        $user = currentUser();
        $flash = getFlashMessage();

        // Pass all variables to view
        extract([
            'nasabah' => $nasabah,
            'page' => $page,
            'offset' => $offset,
            'riwayat' => $riwayat,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'user' => $user,
            'flash' => $flash
        ]);

        include VIEW_PATH . '/nasabah/riwayat.php';
    }

    /**
     * Cairkan - Form dan proses pengajuan pencairan
     */
    public function cairkan(): void
    {
        $nasabah = $this->getCurrentNasabah();

        if (!$nasabah) {
            setFlashMessage('error', 'Data akun tidak ditemukan.');
            redirect('logout');
            return;
        }

        $saldo = $this->nasabahModel->getSaldo($nasabah['id']);
        $saldoTersedia = $this->nasabahModel->getSaldoTersedia($nasabah['id']);

        // Ambil pengajuan aktif
        $pengajuanAktif = $this->pencairanModel->getAktifByNasabah($nasabah['id']);
        $totalTerikat = $this->pencairanModel->getTotalTerikat($nasabah['id']);

        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];
        $oldInput = [];

        // Proses form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->prosesPengajuan($nasabah, $saldoTersedia, $oldInput);
        }

        // Jika tidak ada error dan berhasil
        if (empty($errors) && isset($_POST['submit'])) {
            // Akan redirect, tidak perlu render view
            return;
        }

        include VIEW_PATH . '/nasabah/cairkan.php';
    }

    /**
     * Proses pengajuan pencairan
     *
     * @param array $nasabah
     * @param float $saldoTersedia
     * @param array $oldInput
     * @return array Error messages
     */
    private function prosesPengajuan(array $nasabah, float $saldoTersedia, array &$oldInput): array
    {
        // Ambil data dari form
        $oldInput = [
            'jumlah' => $_POST['jumlah'] ?? '',
            'metode' => $_POST['metode'] ?? '',
            'tujuan_transfer' => $_POST['tujuan_transfer'] ?? ''
        ];

        // Validasi
        $errors = PengajuanPencairan::validate($oldInput, $saldoTersedia);

        if (!empty($errors)) {
            return $errors;
        }

        // Konversi jumlah ke float
        $jumlah = (float) str_replace(['.', ','], ['', '.'], $oldInput['jumlah']);

        // Siapkan data untuk disimpan
        $data = [
            'nasabah_id' => $nasabah['id'],
            'jumlah' => $jumlah,
            'metode' => $oldInput['metode'],
            'tujuan_transfer' => ($oldInput['metode'] === 'TRANSFER') ? trim($oldInput['tujuan_transfer']) : null
        ];

        // Simpan pengajuan
        $pengajuanId = $this->pencairanModel->create($data);

        if ($pengajuanId) {
            setFlashMessage('success', 'Pengajuan pencairan berhasil diajukan. Nomor: ' . $this->pencairanModel->generateNoPengajuan());
            redirect('nasabah/cairkan');
            return [];
        } else {
            $errors[] = 'Gagal menyimpan pengajuan. Silakan coba lagi.';
            return $errors;
        }
    }

    /**
     * Setor Sampah - Form dan proses penyetoran sampah
     */
    public function setor(): void
    {
        $nasabah = $this->getCurrentNasabah();

        if (!$nasabah) {
            setFlashMessage('error', 'Data akun tidak ditemukan.');
            redirect('logout');
            return;
        }

        if (!$nasabah['bank_sampah_id']) {
            setFlashMessage('warning', 'Anda belum terdaftar di bank sampah manapun.');
            redirect('nasabah/dashboard');
            return;
        }

        $saldo = $this->nasabahModel->getSaldo($nasabah['id']);
        $hargaSampah = $this->hargaModel->getLatestByBank($nasabah['bank_sampah_id']);
        
        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];
        $oldInput = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->prosesSetor($nasabah, $hargaSampah, $oldInput);
        }

        if (empty($errors) && isset($_POST['submit'])) {
            return;
        }

        extract([
            'nasabah' => $nasabah,
            'saldo' => $saldo,
            'hargaSampah' => $hargaSampah,
            'user' => $user,
            'flash' => $flash,
            'errors' => $errors,
            'oldInput' => $oldInput
        ]);

        include VIEW_PATH . '/nasabah/setor.php';
    }

    /**
     * Proses penyetoran sampah
     *
     * @param array $nasabah
     * @param array $hargaSampah
     * @param array $oldInput
     * @return array Error messages
     */
    private function prosesSetor(array $nasabah, array $hargaSampah, array &$oldInput): array
    {
        $oldInput = [
            'kategori_id' => $_POST['kategori_id'] ?? '',
            'berat' => $_POST['berat'] ?? ''
        ];

        $errors = [];

        if (empty($oldInput['kategori_id'])) {
            $errors[] = 'Kategori sampah harus dipilih.';
        }

        if (empty($oldInput['berat'])) {
            $errors[] = 'Berat sampah harus diisi.';
        } elseif (!is_numeric($oldInput['berat']) || (float)$oldInput['berat'] <= 0) {
            $errors[] = 'Berat sampah harus berupa angka positif.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        $kategoriId = (int)$oldInput['kategori_id'];
        $berat = (float)$oldInput['berat'];
        $harga = 0;

        foreach ($hargaSampah as $h) {
            if ($h['kategori_id'] == $kategoriId) {
                $harga = $h['harga_beli'];
                break;
            }
        }

        if ($harga <= 0) {
            $errors[] = 'Harga untuk kategori yang dipilih tidak ditemukan.';
            return $errors;
        }

        $totalNilai = $berat * $harga;

        if ($this->nasabahModel->updateSaldo($nasabah['id'], $totalNilai)) {
            $this->mutasiModel->create([
                'nasabah_id' => $nasabah['id'],
                'tipe' => 'SETOR',
                'jumlah' => $totalNilai,
                'keterangan' => "Setor $berat kg sampah kategori #{$kategoriId}"
            ]);

            setFlashMessage('success', "Setor sampah berhasil! Anda mendapat Rp " . number_format($totalNilai, 0, ',', '.'));
            redirect('nasabah/setor');
            return [];
        }

        $errors[] = 'Gagal memproses penyetoran. Silakan coba lagi.';
        return $errors;
    }

    /**
     * Profil Saya - Tampilkan dan edit profil
     */
    public function profil(): void
    {
        $nasabah = $this->getCurrentNasabah();

        if (!$nasabah) {
            setFlashMessage('error', 'Data akun tidak ditemukan.');
            redirect('logout');
            return;
        }

        $user = currentUser();
        $flash = getFlashMessage();
        $errors = [];
        $oldInput = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->prosesProfil($nasabah, $oldInput);
        }

        if (empty($errors) && isset($_POST['submit'])) {
            return;
        }

        extract([
            'nasabah' => $nasabah,
            'user' => $user,
            'flash' => $flash,
            'errors' => $errors,
            'oldInput' => empty($oldInput) ? $nasabah : $oldInput
        ]);

        include VIEW_PATH . '/nasabah/profil.php';
    }

    /**
     * Proses update profil
     *
     * @param array $nasabah
     * @param array $oldInput
     * @return array Error messages
     */
    private function prosesProfil(array $nasabah, array &$oldInput): array
    {
        $oldInput = [
            'nama' => trim($_POST['nama'] ?? ''),
            'no_hp' => trim($_POST['no_hp'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'rt' => trim($_POST['rt'] ?? ''),
            'rw' => trim($_POST['rw'] ?? ''),
            'no_rekening' => trim($_POST['no_rekening'] ?? ''),
            'nama_bank' => trim($_POST['nama_bank'] ?? '')
        ];

        $errors = [];

        if (empty($oldInput['nama'])) {
            $errors[] = 'Nama harus diisi.';
        }

        if (empty($oldInput['no_hp'])) {
            $errors[] = 'Nomor HP harus diisi.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($this->nasabahModel->update($nasabah['id'], [
            'nama' => $oldInput['nama'],
            'no_hp' => $oldInput['no_hp'],
            'alamat' => $oldInput['alamat'],
            'rt' => $oldInput['rt'],
            'rw' => $oldInput['rw'],
            'no_rekening' => $oldInput['no_rekening'],
            'nama_bank' => $oldInput['nama_bank']
        ])) {
            setFlashMessage('success', 'Profil berhasil diperbarui.');
            redirect('nasabah/profil');
            return [];
        }

        $errors[] = 'Gagal memperbarui profil. Silakan coba lagi.';
        return $errors;
    }

    /**
     * Redirect index ke dashboard
     */
    public function index(): void
    {
        $this->dashboard();
    }
}
