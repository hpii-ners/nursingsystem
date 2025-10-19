<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

require_once '../config/db.php';

$errors = [];
$success = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi dan sanitasi input
    $form_data = [
        'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'nik' => trim($_POST['nik'] ?? ''),
        'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
        'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
        'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
        'agama' => $_POST['agama'] ?? '',
        'alamat' => trim($_POST['alamat'] ?? ''),
        'telepon' => trim($_POST['telepon'] ?? ''),
        'status_perkawinan' => $_POST['status_perkawinan'] ?? '',
        'kewarganegaraan' => trim($_POST['kewarganegaraan'] ?? ''),
    ];
    
    // Validasi form
    if (empty($form_data['nama_lengkap'])) {
        $errors[] = 'Nama lengkap harus diisi';
    }
    if (empty($form_data['email'])) {
        $errors[] = 'Email harus diisi';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    if (empty($form_data['password'])) {
        $errors[] = 'Password harus diisi';
    } elseif (strlen($form_data['password']) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }
    if ($form_data['password'] !== $form_data['confirm_password']) {
        $errors[] = 'Password tidak cocok';
    }
    if (empty($form_data['nik'])) {
        $errors[] = 'NIK harus diisi';
    }
    if (empty($form_data['telepon'])) {
        $errors[] = 'Nomor telepon harus diisi';
    }
    
    // Jika tidak ada error, lakukan registrasi
    if (empty($errors)) {
        try {
            // Cek email sudah terdaftar menggunakan PDO dengan PostgreSQL
            $checkEmail = $pdo->prepare("SELECT id FROM public.biodata WHERE email = ?");
            $checkEmail->execute([$form_data['email']]);
            
            if ($checkEmail->rowCount() > 0) {
                $errors[] = 'Email sudah terdaftar. Gunakan email lain';
            } else {
                // Insert data ke tabel biodata
                $stmt = $pdo->prepare("
                    INSERT INTO public.biodata 
                    (nama_lengkap, email, password, nik, tempat_lahir, tanggal_lahir, 
                     jenis_kelamin, agama, alamat, telepon, status_perkawinan, kewarganegaraan, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $form_data['nama_lengkap'],
                    $form_data['email'],
                    password_hash($form_data['password'], PASSWORD_DEFAULT),
                    $form_data['nik'],
                    $form_data['tempat_lahir'],
                    $form_data['tanggal_lahir'],
                    $form_data['jenis_kelamin'],
                    $form_data['agama'],
                    $form_data['alamat'],
                    $form_data['telepon'],
                    $form_data['status_perkawinan'],
                    $form_data['kewarganegaraan']
                ]);
                
                $success = 'Pendaftaran berhasil! Silakan login dengan akun Anda.';
                // Clear form
                $form_data = [];
            }
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan database: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - NIS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .register-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .register-header p {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 30px;
        }
        
        .register-body {
            padding: 40px 30px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
            border-left: 4px solid #c33;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
            border-left: 4px solid #3c3;
        }
        
        .alert ul {
            margin-left: 20px;
            margin-top: 8px;
        }
        
        .alert li {
            margin-bottom: 5px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
        }
        
        label {
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        label .required {
            color: #e74c3c;
        }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 16px;
            padding: 5px;
            transition: color 0.3s ease;
        }
        
        .toggle-password:hover {
            color: #667eea;
        }
        
        input[type="password"] {
            padding-right: 45px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            flex: 1;
            justify-content: center;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            flex: 1;
            justify-content: center;
            border: 2px solid #e0e0e0;
        }
        
        .btn-secondary:hover {
            background: #e8e8e8;
            border-color: #ccc;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .register-body {
                padding: 30px 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <!-- Header -->
            <div class="register-header">
                <div class="logo">✍️</div>
                <h1>Registrasi</h1>
                <p>Buat akun baru untuk melanjutkan</p>
            </div>
            
            <!-- Body -->
            <div class="register-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <strong>❌ Gagal Mendaftar:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <strong>✅ Berhasil!</strong><br>
                        <?= htmlspecialchars($success) ?>
                        <br><br>
                        <a href="login.php" class="btn btn-primary" style="width: auto;">Ke Halaman Login</a>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($success)): ?>
                    <form method="POST" action="" novalidate>
                        <!-- Akun Section -->
                        <div class="section-title">👤 Informasi Akun</div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="nama_lengkap" 
                                    name="nama_lengkap" 
                                    placeholder="Masukkan nama lengkap" 
                                    required
                                    value="<?= htmlspecialchars($form_data['nama_lengkap'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    placeholder="Masukkan email" 
                                    required
                                    value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        placeholder="Minimal 6 karakter" 
                                        required
                                    >
                                    <button type="button" class="toggle-password" onclick="togglePassword('password', 'eyeIcon1')">
                                        <span id="eyeIcon1">👁️</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Password <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <input 
                                        type="password" 
                                        id="confirm_password" 
                                        name="confirm_password" 
                                        placeholder="Ketik ulang password" 
                                        required
                                    >
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', 'eyeIcon2')">
                                        <span id="eyeIcon2">👁️</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Biodata Section -->
                        <div class="section-title">📋 Informasi Biodata</div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nik">NIK <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="nik" 
                                    name="nik" 
                                    placeholder="Nomor Identitas Kependudukan" 
                                    required
                                    value="<?= htmlspecialchars($form_data['nik'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="tempat_lahir">Tempat Lahir</label>
                                <input 
                                    type="text" 
                                    id="tempat_lahir" 
                                    name="tempat_lahir" 
                                    placeholder="Kota/Kabupaten kelahiran"
                                    value="<?= htmlspecialchars($form_data['tempat_lahir'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                <input 
                                    type="date" 
                                    id="tanggal_lahir" 
                                    name="tanggal_lahir"
                                    value="<?= htmlspecialchars($form_data['tanggal_lahir'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select id="jenis_kelamin" name="jenis_kelamin">
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-laki" <?= ($form_data['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="Perempuan" <?= ($form_data['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="agama">Agama</label>
                                <select id="agama" name="agama">
                                    <option value="">-- Pilih --</option>
                                    <option value="Islam" <?= ($form_data['agama'] ?? '') === 'Islam' ? 'selected' : '' ?>>Islam</option>
                                    <option value="Kristen" <?= ($form_data['agama'] ?? '') === 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                                    <option value="Katolik" <?= ($form_data['agama'] ?? '') === 'Katolik' ? 'selected' : '' ?>>Katolik</option>
                                    <option value="Hindu" <?= ($form_data['agama'] ?? '') === 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                                    <option value="Buddha" <?= ($form_data['agama'] ?? '') === 'Buddha' ? 'selected' : '' ?>>Buddha</option>
                                    <option value="Konghucu" <?= ($form_data['agama'] ?? '') === 'Konghucu' ? 'selected' : '' ?>>Konghucu</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status_perkawinan">Status Perkawinan</label>
                                <select id="status_perkawinan" name="status_perkawinan">
                                    <option value="">-- Pilih --</option>
                                    <option value="Belum Menikah" <?= ($form_data['status_perkawinan'] ?? '') === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                                    <option value="Menikah" <?= ($form_data['status_perkawinan'] ?? '') === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                                    <option value="Cerai Hidup" <?= ($form_data['status_perkawinan'] ?? '') === 'Cerai Hidup' ? 'selected' : '' ?>>Cerai Hidup</option>
                                    <option value="Cerai Mati" <?= ($form_data['status_perkawinan'] ?? '') === 'Cerai Mati' ? 'selected' : '' ?>>Cerai Mati</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="kewarganegaraan">Kewarganegaraan</label>
                                <input 
                                    type="text" 
                                    id="kewarganegaraan" 
                                    name="kewarganegaraan" 
                                    placeholder="Contoh: Indonesia"
                                    value="<?= htmlspecialchars($form_data['kewarganegaraan'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="telepon">Nomor Telepon <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="telepon" 
                                    name="telepon" 
                                    placeholder="Contoh: 0812345678" 
                                    required
                                    value="<?= htmlspecialchars($form_data['telepon'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="form-group full">
                                <label for="alamat">Alamat</label>
                                <textarea 
                                    id="alamat" 
                                    name="alamat" 
                                    placeholder="Masukkan alamat lengkap"
                                ><?= htmlspecialchars($form_data['alamat'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Buttons -->
                        <div class="button-group">
                            <button type="submit" class="btn btn-primary">🚀 Daftar</button>
                            <a href="login.php" class="btn btn-secondary">❌ Batal</a>
                        </div>
                    </form>
                <?php endif; ?>
                
                <!-- Login Link -->
                <div class="login-link">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.textContent = '👁️‍🗨️';
            } else {
                field.type = 'password';
                icon.textContent = '👁️';
            }
        }
    </script>
</body>
</html>