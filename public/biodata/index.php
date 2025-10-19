<?php
// FILE: public/biodata/index.php (CONTENT ONLY - NO HTML WRAPPER)
// This file will be included in main index.php

if (!defined('APP_ACCESS')) {
    die('Direct access not allowed');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (verify_csrf($_GET['token'] ?? '')) {
        try {
            $stmt = $db->prepare("DELETE FROM biodata WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Biodata berhasil dihapus');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menghapus biodata');
        }
        redirect('../index.php?page=biodata');
    }
}

// Get all biodata
$biodatas = $db->query("SELECT * FROM biodata ORDER BY created_at DESC")->fetchAll();
?>

<style>
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: linear-gradient(135deg, #38bdf8, #6366f1);
    color: white;
}
.stat-content h3 {
    font-size: 28px;
    color: #333;
    margin-bottom: 5px;
}
.stat-content p {
    color: #666;
    font-size: 14px;
}

/* Card Styles */
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
    flex-wrap: wrap;
    gap: 15px;
}
.card-title {
    font-size: 20px;
    color: #333;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}
.card-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

/* Search Box */
.search-box {
    display: flex;
    gap: 10px;
}
.search-input {
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    width: 250px;
    transition: border-color 0.3s;
}
.search-input:focus {
    outline: none;
    border-color: #38bdf8;
}

/* Buttons */
.btn {
    padding: 10px 20px;
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
    white-space: nowrap;
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
.btn-warning {
    background: #ffc107;
    color: #333;
    padding: 8px 15px;
    font-size: 13px;
}
.btn-warning:hover {
    background: #e0a800;
}
.btn-danger {
    background: #dc3545;
    color: white;
    padding: 8px 15px;
    font-size: 13px;
}
.btn-danger:hover {
    background: #c82333;
}

/* Table Styles */
.table-wrapper {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
}
thead {
    background: #f8f9fa;
}
th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    font-size: 14px;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}
td {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    color: #495057;
    font-size: 14px;
}
tbody tr:hover {
    background: #f8f9fa;
}
.badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.badge-male {
    background: #e3f2fd;
    color: #1976d2;
}
.badge-female {
    background: #fce4ec;
    color: #c2185b;
}
.actions {
    display: flex;
    gap: 8px;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}
.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #ddd;
}
.empty-state h3 {
    color: #666;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .search-input { width: 100%; }
    .card-header { flex-direction: column; align-items: stretch; }
}
</style>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-address-card"></i>
        </div>
        <div class="stat-content">
            <h3><?= count($biodatas) ?></h3>
            <p>Total Biodata</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-plus-circle"></i>
        </div>
        <div class="stat-content"><h3><?= count(array_filter($biodatas, function($b) {
    return date('Y-m-d', strtotime($b['created_at'])) == date('Y-m-d');
})) ?></h3>
        <p>Ditambahkan Hari Ini</p>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-list"></i>
            Daftar Biodata
        </h2>
        <div class="card-actions">
            <div class="search-box">
                <input type="text" class="search-input" id="searchInput" placeholder="Cari biodata...">
            </div>
            <a href="index.php?page=biodata&action=form" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Biodata
            </a>
        </div>
    </div>
    <div class="table-wrapper">
        <?php if (empty($biodatas)): ?>
            <div class="empty-state">
                <i class="fas fa-address-card"></i>
                <h3>Belum Ada Biodata</h3>
                <p>Klik tombol "Tambah Biodata" untuk menambahkan data baru</p>
                <a href="index.php?page=biodata&action=form" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Tambah Biodata
                </a>
            </div>
        <?php else: ?>
            <table id="biodataTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>NIK</th>
                        <th>TTL</th>
                        <th>JK</th>
                        <th>Agama</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($biodatas as $i => $data): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= e($data['nama_lengkap']) ?></strong></td>
                            <td><?= e($data['email']) ?></td>
                            <td><?= e($data['nik']) ?></td>
                            <td><?= e($data['tempat_lahir']) ?>, <?= date('d/m/Y', strtotime($data['tanggal_lahir'])) ?></td>
                            <td>
                                <span class="badge <?= $data['jenis_kelamin'] == 'L' ? 'badge-male' : 'badge-female' ?>">
                                    <?= $data['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                </span>
                            </td>
                            <td><?= e($data['agama']) ?></td>
                            <td><?= e($data['telepon']) ?></td>
                            <td><?= e($data['status_perkawinan']) ?></td>
                            <td><?= date('d/m/Y', strtotime($data['created_at'])) ?></td>
                            <td class="actions">
                                <a href="index.php?page=biodata&action=form&id=<?= $data['id'] ?>" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
// Search functionality
const searchInput = document.getElementById('searchInput');
const table = document.getElementById('biodataTable');

if (searchInput && table) {
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        }
    });
}
</script>