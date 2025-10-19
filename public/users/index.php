<?php
// FILE: public/users/index.php (CONTENT ONLY - NO HTML WRAPPER)
// This file will be included in main index.php

if (!defined('APP_ACCESS')) {
    die('Direct access not allowed');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (verify_csrf($_GET['token'] ?? '')) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            setFlash('success', 'User berhasil dihapus');
        } else {
            setFlash('error', 'Gagal menghapus user');
        }
        redirect('../index.php?page=users');
    }
}

// Get all users
$search = clean($_GET['search'] ?? '');
$query = "SELECT id, username, nama_lengkap, email, nik, created_at FROM users";
if ($search) {
    $query .= " WHERE username LIKE ? OR nama_lengkap LIKE ? OR email LIKE ? OR nik LIKE ?";
    $stmt = $db->prepare($query . " ORDER BY created_at DESC");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
} else {
    $stmt = $db->query($query . " ORDER BY created_at DESC");
}
$users = $stmt->fetchAll();
?>

<style>
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
.btn-success {
    background: #28a745;
    color: white;
    padding: 8px 15px;
    font-size: 13px;
}
.btn-success:hover {
    background: #218838;
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
.table-container {
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
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    background: #e7f1ff;
    color: #38bdf8;
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

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-users"></i> Daftar Users
        </h2>
        <div class="card-actions">
            <form method="GET" class="search-box">
                <input type="hidden" name="page" value="users">
                <input type="text" name="search" class="search-input" placeholder="Cari username, nama, email, NIK..." value="<?= e($search) ?>">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Cari
                </button>
                <?php if ($search): ?>
                    <a href="index.php?page=users" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                <?php endif; ?>
            </form>
            <a href="index.php?page=users&action=form" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah User
            </a>
        </div>
    </div>

    <div class="table-container">
        <?php if (count($users) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>NIK</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <span class="badge">
                                    <i class="fas fa-user"></i> <?= e($u['username']) ?>
                                </span>
                            </td>
                            <td><?= e($u['nama_lengkap']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['nik']) ?></td>
                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <div class="actions">
                                    <a href="index.php?page=users&action=form&id=<?= $u['id'] ?>" class="btn btn-success">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <h3>Tidak Ada Data</h3>
                <p><?= $search ? 'Tidak ada hasil untuk pencarian "' . e($search) . '"' : 'Belum ada user yang terdaftar' ?></p>
                <?php if (!$search): ?>
                    <a href="index.php?page=users&action=form" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Tambah User Pertama
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>