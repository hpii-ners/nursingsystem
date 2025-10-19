<?php
// FILE: public/users/users_form.php (CONTENT ONLY - NO HTML WRAPPER)
// This file will be included in main index.php

if (!defined('APP_ACCESS')) {
    die('Direct access not allowed');
}

// Get user data if editing
$editUser = null;
$isEdit = false;
if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $editUser = $stmt->fetch();
    
    if (!$editUser) {
        setFlash('error', 'User tidak ditemukan');
        redirect('index.php?page=users');
    }
}
?>

<style>
/* Card */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
}
.card-header {
    padding: 20px 30px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-title {
    font-size: 20px;
    color: #333;
    font-weight: 600;
}
.card-body {
    padding: 30px;
}

/* Form */
.form-group {
    margin-bottom: 20px;
}
label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
    font-size: 14px;
}
.required {
    color: #dc3545;
}
.input-group {
    position: relative;
}
.input-group i.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
}
input, select {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}
input#password {
    padding-right: 45px;
}
input:focus, select:focus {
    outline: none;
    border-color: #38bdf8;
}
input[readonly] {
    background-color: #f5f7fa;
    cursor: not-allowed;
}
.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    cursor: pointer;
    transition: color 0.3s;
    font-size: 16px;
    z-index: 10;
}
.toggle-password:hover {
    color: #38bdf8;
}
.form-text {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
    display: block;
}

/* Buttons */
.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    font-size: 14px;
}
.btn-primary {
    background: linear-gradient(135deg, #38bdf8, #6366f1);
    color: white;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(56, 189, 248, 0.4);
}
.btn-secondary {
    background: #6c757d;
    color: white;
}
.btn-secondary:hover {
    background: #5a6268;
}
.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}
</style>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i>
            <?= $isEdit ? 'Edit User' : 'Tambah User Baru' ?>
        </h2>
        <a href="index.php?page=users" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="users/users_proses.php">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Username <span class="required">*</span></label>
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="username" placeholder="Masukkan username" 
                           value="<?= $isEdit ? e($editUser['username']) : '' ?>" 
                           required <?= $isEdit ? 'readonly' : '' ?>>
                </div>
                <?php if ($isEdit): ?>
                    <small class="form-text">Username tidak dapat diubah</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Nama Lengkap <span class="required">*</span></label>
                <div class="input-group">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" 
                           value="<?= $isEdit ? e($editUser['nama_lengkap']) : '' ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" placeholder="Masukkan email" 
                           value="<?= $isEdit ? e($editUser['email']) : '' ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>NIK <span class="required">*</span></label>
                <div class="input-group">
                    <i class="fas fa-id-badge input-icon"></i>
                    <input type="text" name="nik" placeholder="Masukkan NIK (16 digit)" 
                           value="<?= $isEdit ? e($editUser['nik']) : '' ?>" 
                           maxlength="16" pattern="\d{16}" required>
                </div>
                <small class="form-text">NIK harus 16 digit angka</small>
            </div>

            <div class="form-group">
                <label>Password <?= $isEdit ? '' : '<span class="required">*</span>' ?></label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" 
                           placeholder="<?= $isEdit ? 'Kosongkan jika tidak ingin mengubah' : 'Masukkan password' ?>" 
                           <?= $isEdit ? '' : 'required' ?>>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
                <?php if ($isEdit): ?>
                    <small class="form-text">Kosongkan jika tidak ingin mengubah password</small>
                <?php else: ?>
                    <small class="form-text">Minimal 6 karakter</small>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Simpan' ?>
                </button>
                <a href="index.php?page=users" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle Password Visibility
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}
</script>