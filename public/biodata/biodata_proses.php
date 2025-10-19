<?php
// FILE: public/biodata/biodata_proses.php
define('APP_ACCESS', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

startSession();
requireLogin();

$db = getDB();
$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $action !== 'delete') {
    redirect('../index.php?page=biodata');
}

switch ($action) {
    case 'create':
        $data = [
            'nama_lengkap' => clean($_POST['nama_lengkap']),
            'email' => clean($_POST['email']),
            'nik' => clean($_POST['nik']),
            'telepon' => clean($_POST['telepon']),
            'tempat_lahir' => clean($_POST['tempat_lahir']),
            'tanggal_lahir' => $_POST['tanggal_lahir'],
            'jenis_kelamin' => $_POST['jenis_kelamin'],
            'agama' => $_POST['agama'],
            'status_perkawinan' => $_POST['status_perkawinan'],
            'kewarganegaraan' => clean($_POST['kewarganegaraan']),
            'alamat' => clean($_POST['alamat'])
        ];
        
        try {
            $stmt = $db->prepare("INSERT INTO biodata (nama_lengkap, email, nik, telepon, tempat_lahir, 
                                   tanggal_lahir, jenis_kelamin, agama, status_perkawinan, kewarganegaraan, 
                                   alamat, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->execute([
                $data['nama_lengkap'], $data['email'], $data['nik'], $data['telepon'],
                $data['tempat_lahir'], $data['tanggal_lahir'], $data['jenis_kelamin'],
                $data['agama'], $data['status_perkawinan'], $data['kewarganegaraan'],
                $data['alamat']
            ]);
            
            setFlash('success', 'Biodata berhasil ditambahkan');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menambah biodata: ' . $e->getMessage());
        }
        redirect('../index.php?page=biodata');
        break;
        
    case 'update':
        $id = (int)$_POST['id'];
        $data = [
            'nama_lengkap' => clean($_POST['nama_lengkap']),
            'email' => clean($_POST['email']),
            'nik' => clean($_POST['nik']),
            'telepon' => clean($_POST['telepon']),
            'tempat_lahir' => clean($_POST['tempat_lahir']),
            'tanggal_lahir' => $_POST['tanggal_lahir'],
            'jenis_kelamin' => $_POST['jenis_kelamin'],
            'agama' => $_POST['agama'],
            'status_perkawinan' => $_POST['status_perkawinan'],
            'kewarganegaraan' => clean($_POST['kewarganegaraan']),
            'alamat' => clean($_POST['alamat'])
        ];
        
        try {
            $stmt = $db->prepare("UPDATE biodata SET nama_lengkap = ?, email = ?, nik = ?, telepon = ?, 
                                   tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, agama = ?, 
                                   status_perkawinan = ?, kewarganegaraan = ?, alamat = ? WHERE id = ?");
            
            $stmt->execute([
                $data['nama_lengkap'], $data['email'], $data['nik'], $data['telepon'],
                $data['tempat_lahir'], $data['tanggal_lahir'], $data['jenis_kelamin'],
                $data['agama'], $data['status_perkawinan'], $data['kewarganegaraan'],
                $data['alamat'], $id
            ]);
            
            setFlash('success', 'Biodata berhasil diupdate');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal update biodata: ' . $e->getMessage());
        }
        redirect('../index.php?page=biodata');
        break;
        
    case 'delete':
        $id = (int)$_GET['id'];
        
        try {
            $stmt = $db->prepare("DELETE FROM biodata WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Biodata berhasil dihapus');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal hapus biodata: ' . $e->getMessage());
        }
        redirect('../index.php?page=biodata');
        break;
        
    default:
        redirect('../index.php?page=biodata');
}