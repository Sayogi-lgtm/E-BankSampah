<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style type="text/css">
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        }

        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 440px;
            margin: 1rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            font-size: 1.5rem;
            color: #2e7d32;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background: #1b5e20;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }

        .login-footer p {
            color: #666;
            font-size: 0.85rem;
            margin: 0 0 0.75rem 0;
        }

        .back-link {
            display: inline-block;
            color: #2e7d32;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">🏘️</div>
                <h1><?= APP_NAME ?></h1>
                <p>Buat akun nasabah baru</p>
            </div>

            <?php if (isset($flash) && $flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url('register') ?>">
                <div class="form-group">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($oldInput['nama'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="no_hp" class="form-label">Nomor HP</label>
                    <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($oldInput['no_hp'] ?? '') ?>" required autocomplete="tel">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email (opsional)</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi" required>
                </div>

                <button type="submit" class="btn-login">Daftar</button>
            </form>

            <div class="login-footer">
                <p>Sudah punya akun?</p>
                <a href="<?= base_url('login') ?>" class="back-link">Masuk di sini</a>
            </div>
        </div>
    </div>
</body>
</html>
