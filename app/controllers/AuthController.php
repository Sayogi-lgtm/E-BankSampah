<?php
/**
 * Auth Controller
 *
 * Controller untuk autentikasi: login, register, dan logout
 * Compatible dengan PHP 7.3+
 */

class AuthController
{
    /** @var User */
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Tampilkan form login (GET) atau proses login (POST)
     *
     * @return void
     */
    public function login(): void
    {
        if (isLoggedIn()) {
            redirectToRoleDashboard();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }

        $this->showLoginForm();
    }

    /**
     * Tampilkan form register (GET) atau proses register (POST)
     *
     * @return void
     */
    public function register(): void
    {
        if (isLoggedIn()) {
            redirectToRoleDashboard();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRegister();
            return;
        }

        $this->showRegisterForm();
    }

    /**
     * Tampilkan form login
     *
     * @param string|null $error
     * @param array|null $oldInput
     * @return void
     */
    private function showLoginForm($error = null, $oldInput = null): void
    {
        $flash = getFlashMessage();
        include VIEW_PATH . '/auth/login.php';
    }

    /**
     * Tampilkan form register
     *
     * @param string|null $error
     * @param array|null $oldInput
     * @return void
     */
    private function showRegisterForm($error = null, $oldInput = null): void
    {
        $flash = getFlashMessage();
        include VIEW_PATH . '/auth/register.php';
    }

    /**
     * Proses login
     *
     * @return void
     */
    private function processLogin(): void
    {
        $noHp = trim($_POST['no_hp'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($noHp) || empty($password)) {
            $this->showLoginForm('No HP dan kata sandi harus diisi.', ['no_hp' => $noHp]);
            return;
        }

        $user = $this->userModel->findByNoHp($noHp);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->showLoginForm('No HP atau kata sandi salah.', ['no_hp' => $noHp]);
            return;
        }

        if (!User::isActive($user)) {
            $this->showLoginForm('Akun Anda tidak aktif. Hubungi administrator.', ['no_hp' => $noHp]);
            return;
        }

        setUserSession($user);
        $this->userModel->updateLastLogin($user['id']);
        redirectToRoleDashboard();
    }

    /**
     * Proses pendaftaran akun baru
     *
     * @return void
     */
    private function processRegister(): void
    {
        $nama = trim($_POST['nama'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirmation'] ?? '';

        $oldInput = [
            'nama' => $nama,
            'no_hp' => $noHp,
            'email' => $email,
        ];

        if (empty($nama) || empty($noHp) || empty($password)) {
            $this->showRegisterForm('Nama, nomor HP, dan kata sandi harus diisi.', $oldInput);
            return;
        }

        if (strlen($password) < 6) {
            $this->showRegisterForm('Kata sandi minimal 6 karakter.', $oldInput);
            return;
        }

        if ($password !== $confirmPassword) {
            $this->showRegisterForm('Konfirmasi kata sandi tidak cocok.', $oldInput);
            return;
        }

        if ($this->userModel->findByNoHp($noHp)) {
            $this->showRegisterForm('Nomor HP sudah terdaftar. Silakan gunakan nomor lain atau login.', $oldInput);
            return;
        }

        $userCreated = $this->userModel->create([
            'nama' => $nama,
            'no_hp' => $noHp,
            'email' => $email !== '' ? $email : null,
            'password_hash' => User::hashPassword($password),
            'role' => 'NASABAH',
            'status' => 'AKTIF',
            'desa_id' => null,
            'bank_sampah_id' => null,
        ]);

        if (!$userCreated) {
            $this->showRegisterForm('Pendaftaran gagal. Silakan coba lagi.', $oldInput);
            return;
        }

        setFlashMessage('success', 'Pendaftaran berhasil. Silakan masuk dengan akun Anda.');
        redirect('login');
    }

    /**
     * Logout user
     *
     * @return void
     */
    public function logout(): void
    {
        $flash = $_SESSION['flash'] ?? null;
        destroySession();

        if ($flash) {
            $_SESSION['flash'] = $flash;
        }

        setFlashMessage('success', 'Anda telah logout. Sampai jumpa kembali!');
        redirect('login');
    }
}
