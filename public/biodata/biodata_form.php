<?php
// FILE: public/biodata/biodata_form.php (CONTENT ONLY - NO HTML WRAPPER)
// This file will be included in main index.php

if (!defined('APP_ACCESS')) {
    die('Direct access not allowed');
}

// Get biodata data if editing
$biodata = null;
$isEdit = false;
if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM biodata WHERE id = ?");
    $stmt->execute([$id]);
    $biodata = $stmt->fetch();
    
    if (!$biodata) {
        setFlash('error', 'Biodata tidak ditemukan');
        redirect('index.php?page=biodata');
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
    display: flex;
    align-items: center;
    gap: 10px;
}
.card-body {
    padding: 30px;
}

/* Form */
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}
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
    z-index: 1;
}
input, select, textarea {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
    font-family: 'Segoe UI', Arial, sans-serif;
}
textarea {
    resize: vertical;
    min-height: 100px;
    padding-top: 15px;
}
input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #38bdf8;
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

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i>
            <?= $isEdit ? 'Edit Biodata' : 'Tambah Biodata Baru' ?>
        </h2>
        <a href="index.php?page=biodata" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="biodata/biodata_proses.php">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $biodata['id'] ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap"
                               value="<?= $isEdit ? e($biodata['nama_lengkap']) : '' ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" placeholder="Masukkan email"
                               value="<?= $isEdit ? e($biodata['email']) : '' ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>NIK <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="text" name="nik" placeholder="Masukkan NIK (16 digit)"
                               value="<?= $isEdit ? e($biodata['nik']) : '' ?>" 
                               pattern="\d{16}" maxlength="16" required>
                    </div>
                    <small class="form-text">NIK harus 16 digit angka</small>
                </div>

                <div class="form-group">
                    <label>Telepon <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" name="telepon" placeholder="Masukkan nomor telepon"
                               value="<?= $isEdit ? e($biodata['telepon']) : '' ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tempat Lahir <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-map-marker-alt input-icon"></i>
                        <input type="text" name="tempat_lahir" placeholder="Masukkan tempat lahir"
                               value="<?= $isEdit ? e($biodata['tempat_lahir']) : '' ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-calendar input-icon"></i>
                        <input type="date" name="tanggal_lahir"
                               value="<?= $isEdit ? $biodata['tanggal_lahir'] : '' ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jenis Kelamin <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-venus-mars input-icon"></i>
                        <select name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?= ($isEdit && $biodata['jenis_kelamin'] === 'L') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= ($isEdit && $biodata['jenis_kelamin'] === 'P') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Agama <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-pray input-icon"></i>
                        <select name="agama" required>
                            <option value="">Pilih Agama</option>
                            <?php
                            $agamas = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
                            foreach ($agamas as $agama) {
                                $selected = ($isEdit && $biodata['agama'] === $agama) ? 'selected' : '';
                                echo "<option value='$agama' $selected>$agama</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Status Perkawinan <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-ring input-icon"></i>
                        <select name="status_perkawinan" required>
                            <option value="">Pilih Status</option>
                            <option value="Belum Menikah" <?= ($isEdit && $biodata['status_perkawinan'] === 'Belum Menikah') ? 'selected' : '' ?>>Belum Menikah</option>
                            <option value="Menikah" <?= ($isEdit && $biodata['status_perkawinan'] === 'Menikah') ? 'selected' : '' ?>>Menikah</option>
                            <option value="Cerai" <?= ($isEdit && $biodata['status_perkawinan'] === 'Cerai') ? 'selected' : '' ?>>Cerai</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kewarganegaraan <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-flag input-icon"></i>
                        <input type="text" name="kewarganegaraan" placeholder="Masukkan kewarganegaraan"
                               value="<?= $isEdit ? e($biodata['kewarganegaraan']) : 'Indonesia' ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat Lengkap <span class="required">*</span></label>
                <div class="input-group">
                    <i class="fas fa-home input-icon"></i>
                    <textarea name="alamat" placeholder="Masukkan alamat lengkap" required><?= $isEdit ? e($biodata['alamat']) : '' ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Simpan' ?>
                </button>
                <a href="index.php?page=biodata" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>