<?php
// FILE: public/index.php (TEMPLATE UTAMA - ENHANCED)
define('APP_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

startSession();
requireLogin();

$user = currentUser();
$db = getDB();
$username = $user['username'];

// Ambil menu & sub-menu berdasarkan username login
$sql = "
SELECT 
    m.kd_menu,
    m.nama_menu,
    m.judul_menu,
    m.urutan,
    m.icon AS menu_icon,
    s.kd_sub_menu,
    s.nama_sub_menu,
    s.link,
    s.icon AS sub_icon
FROM public.menu_user u
JOIN public.menu m ON u.kd_menu = m.kd_menu
LEFT JOIN public.menu_sub s ON u.kd_sub_menu = s.kd_sub_menu
WHERE u.username = :username
ORDER BY m.urutan, s.nama_sub_menu;
";
$stmt = $db->prepare($sql);
$stmt->execute(['username' => $username]);
$menuData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bentuk struktur data menu → submenus
$menus = [];
foreach ($menuData as $row) {
    $kd_menu = $row['kd_menu'];
    if (!isset($menus[$kd_menu])) {
        $menus[$kd_menu] = [
            'nama_menu' => $row['nama_menu'],
            'judul_menu' => $row['judul_menu'],
            'icon' => $row['menu_icon'],
            'submenus' => []
        ];
    }
    if ($row['kd_sub_menu']) {
        $menus[$kd_menu]['submenus'][] = [
            'nama_sub_menu' => $row['nama_sub_menu'],
            'link' => $row['link'],
            'icon' => $row['sub_icon']
        ];
    }
}

$flash = getFlash();

// ROUTING SYSTEM
$page = clean($_GET['page'] ?? 'dashboard');
$action = clean($_GET['action'] ?? 'index');
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Array of available pages
$availablePages = [
    'dashboard' => ['title' => 'Dashboard', 'file' => null, 'folder' => null],
    'users' => ['title' => 'Manajemen Users', 'file' => 'users/index.php', 'folder' => 'users'],
    'biodata' => ['title' => 'Manajemen Biodata', 'file' => 'biodata/index.php', 'folder' => 'biodata']
];

// Validasi page
if (!isset($availablePages[$page])) {
    $page = 'dashboard';
}

$pageTitle = $availablePages[$page]['title'];
$currentPage = $page;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/global-styles.css">
<style>
/* Global Reset & Layout */
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #333;
    overflow-x: hidden;
    font-size: 85%;
}

/* Sidebar */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 230px;
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    color: #fff;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 4px 0 20px rgba(0,0,0,0.3);
    overflow-y: auto;
    z-index: 100;
}
.sidebar.collapsed { width: 68px; }

.sidebar-header {
    padding: 17px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    background: rgba(56, 189, 248, 0.1);
}
.sidebar-header i { 
    font-size: 24px; 
    color: #38bdf8;
    text-shadow: 0 0 10px rgba(56, 189, 248, 0.5);
}
.sidebar-header h2 {
    font-size: 17px;
    font-weight: 700;
    transition: opacity 0.3s;
    background: linear-gradient(135deg, #38bdf8, #818cf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.sidebar.collapsed .sidebar-header h2 { opacity: 0; display: none; }

.sidebar-menu { list-style: none; padding: 13px 0; }

.menu-item { margin-bottom: 4px; }
.menu-item > a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 17px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    font-size: 13px;
    border-left: 3px solid transparent;
}
.menu-item > a:hover {
    background: linear-gradient(90deg, rgba(56, 189, 248, 0.2), transparent);
    color: #fff;
    border-left-color: #38bdf8;
    transform: translateX(2px);
}
.menu-item > a.active {
    background: linear-gradient(90deg, rgba(56, 189, 248, 0.25), transparent);
    color: #fff;
    border-left-color: #38bdf8;
    box-shadow: 0 2px 10px rgba(56, 189, 248, 0.3);
}
.menu-item i {
    font-size: 15px;
    min-width: 20px;
    text-align: center;
}
.menu-item span {
    white-space: nowrap;
    transition: opacity 0.3s;
    font-weight: 500;
}
.sidebar.collapsed .menu-item span { display: none; }

.menu-arrow {
    margin-left: auto;
    transition: transform 0.3s ease;
    font-size: 11px;
}
.menu-item.open > a .menu-arrow {
    transform: rotate(90deg);
}

/* Submenu */
.submenu {
    max-height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
    background: rgba(0,0,0,0.2);
}
.menu-item.open .submenu {
    max-height: 500px;
    padding: 4px 0;
}
.submenu li a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 38px;
    font-size: 12px;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    transition: all 0.3s;
    border-left: 2px solid transparent;
}
.submenu li a:hover {
    background: rgba(56,189,248,0.15);
    color: #fff;
    border-left-color: #38bdf8;
    transform: translateX(2px);
}

/* Header */
.header {
    position: fixed;
    top: 0;
    left: 230px;
    right: 0;
    height: 60px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
    transition: left 0.3s ease;
    z-index: 50;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.sidebar.collapsed ~ .header { left: 68px; }
.toggle-btn {
    border: none;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    cursor: pointer;
    font-size: 17px;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}
.toggle-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.header-title h1 {
    font-size: 20px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    border-radius: 25px;
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.user-info:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}
.user-avatar {
    width: 34px; 
    height: 34px; 
    border-radius: 50%;
    background: linear-gradient(135deg, #38bdf8, #6366f1);
    display: flex; 
    align-items: center; 
    justify-content: center;
    color: white; 
    font-weight: 700; 
    font-size: 15px;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}
.user-details { font-size: 11px; }
.user-name { 
    font-weight: 600; 
    color: #1e293b;
    font-size: 12px;
}
.user-role { 
    color: #64748b;
    font-size: 10px;
}

.logout-btn {
    padding: 8px 15px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}
.logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}
.logout-btn i {
    font-size: 13px;
}

/* Main Content */
.main-content {
    margin-left: 230px;
    margin-top: 60px;
    padding: 25px;
    transition: margin-left 0.3s ease;
    min-height: calc(100vh - 60px);
}
.sidebar.collapsed ~ .main-content { margin-left: 68px; }

/* Alert Messages */
.alert {
    padding: 10px 13px;
    border-radius: 8px;
    margin-bottom: 17px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    border: 1px solid transparent;
    animation: slideIn 0.3s ease;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.alert-success { 
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    border-color: #6ee7b7;
}
.alert-danger { 
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #7f1d1d;
    border-color: #fca5a5;
}
.alert-warning { 
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
    border-color: #fcd34d;
}
.alert-info { 
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #0c2d6b;
    border-color: #93c5fd;
}
.alert-error { 
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #7f1d1d;
    border-color: #fca5a5;
}

/* Scrollbar */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.3);
}

/* Dashboard Content Styling */
.dashboard-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}
.dashboard-card h2 {
    margin-bottom: 17px;
    font-size: 19px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.dashboard-card p {
    color: #64748b;
    line-height: 1.6;
    font-size: 13px;
}
</style>
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-layer-group"></i>
        <h2><?= APP_NAME ?></h2>
    </div>
    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="index.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-dashboard"></i><span>Dashboard</span>
            </a>
        </li>

        <?php foreach ($menus as $menu): ?>
        <li class="menu-item">
            <a>
                <i class="fas fa-<?= e($menu['icon']) ?>"></i>
                <span><?= e($menu['nama_menu']) ?></span>
                <?php if (!empty($menu['submenus'])): ?>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                <?php endif; ?>
            </a>

            <?php if (!empty($menu['submenus'])): ?>
            <ul class="submenu">
                <?php foreach ($menu['submenus'] as $sub): ?>
                <li>
                    <a href="<?= e($sub['link']) ?>">
                        <i class="fas fa-<?= e($sub['icon']) ?>"></i>
                        <span><?= e($sub['nama_sub_menu']) ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</aside>

<!-- Header -->
<header class="header">
    <button class="toggle-btn" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>
    <div class="header-title">
        <h1><?= e($pageTitle) ?></h1>
    </div>
    <div class="header-right">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr(e($user['nama_lengkap']),0,1)) ?></div>
            <div class="user-details">
                <div class="user-name"><?= e($user['nama_lengkap']) ?></div>
                <div class="user-role"><?= e($user['username']) ?></div>
            </div>
        </div>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $flash['message'] ?>
        </div>
    <?php endif; ?>

    <?php
    // CONTENT ROUTING
    if ($page === 'dashboard') {
        // Dashboard content
        ?>
        <div class="dashboard-card">
            <h2>Selamat datang di dashboard utama!</h2>
            <p>Gunakan menu di sebelah kiri untuk navigasi ke berbagai modul yang tersedia.</p>
        </div>
        <?php
    } else {
        // Include module files based on action parameter
        $pageConfig = $availablePages[$page];
        $folder = $pageConfig['folder'];
        
        // Determine which file to load
        if ($action === 'form') {
            // Load form file: users_form.php or biodata_form.php
            $formFile = $folder . '/' . $page . '_form.php';
            if (file_exists($formFile)) {
                include $formFile;
            } else {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Form tidak ditemukan</div>';
            }
        } else {
            // Load list/index file: users/index.php or biodata/index.php
            $listFile = $pageConfig['file'];
            if ($listFile && file_exists($listFile)) {
                include $listFile;
            } else {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Module tidak ditemukan</div>';
            }
        }
    }
    ?>
</main>

<script>
// Sidebar Toggle
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleSidebar');
toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
});
window.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('sidebarState') === 'collapsed') sidebar.classList.add('collapsed');
    
    // Restore menu state
    const openMenuIndex = localStorage.getItem('openMenu');
    if (openMenuIndex !== null) {
        const menuItems = document.querySelectorAll('.menu-item');
        if (menuItems[openMenuIndex]) {
            menuItems[openMenuIndex].classList.add('open');
        }
    }
    
    // Keep current submenu open based on current URL
    const currentUrl = window.location.href;
    document.querySelectorAll('.submenu li a').forEach(link => {
        if (currentUrl.includes(link.getAttribute('href'))) {
            const menuItem = link.closest('.menu-item');
            if (menuItem) {
                menuItem.classList.add('open');
                const menuIndex = Array.from(menuItem.parentElement.children).indexOf(menuItem);
                localStorage.setItem('openMenu', menuIndex);
            }
            // Add active class to current submenu link
            link.style.background = 'rgba(56,189,248,0.25)';
            link.style.color = '#fff';
            link.style.borderLeftColor = '#38bdf8';
        }
    });
});

// Expand/Collapse Submenu with state persistence
document.querySelectorAll('.menu-item > a').forEach(a => {
    a.addEventListener('click', e => {
        const parent = a.parentElement;
        const submenu = parent.querySelector('.submenu');
        if (submenu) {
            e.preventDefault();
            const wasOpen = parent.classList.contains('open');
            
            // Close all other menus
            document.querySelectorAll('.menu-item').forEach(item => {
                if (item !== parent) {
                    item.classList.remove('open');
                }
            });
            
            // Toggle current menu
            parent.classList.toggle('open');
            
            // Save menu state
            const menuIndex = Array.from(parent.parentElement.children).indexOf(parent);
            if (parent.classList.contains('open')) {
                localStorage.setItem('openMenu', menuIndex);
            } else {
                localStorage.removeItem('openMenu');
            }
        }
    });
});

// Auto-hide alert
const alert = document.querySelector('.alert');
if (alert) {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.3s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}
</script>
</body>
</html>