<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>LOGIN DEBUG - Full Diagnosis</h1>";
echo "<hr>";

// 1. Check if config/db.php exists
echo "<h3>1. Check config/db.php</h3>";
$db_path = '../config/db.php';
if (file_exists($db_path)) {
    echo "✅ File exists: " . realpath($db_path) . "<br>";
    require_once $db_path;
    echo "✅ Database functions loaded<br>";
} else {
    echo "❌ File NOT found: " . $db_path . "<br>";
    exit;
}

echo "<hr>";

// 2. Test database connection
echo "<h3>2. Test Database Connection</h3>";
try {
    $test_query = "SELECT 1";
    $result = $pdo->query($test_query);
    echo "✅ Database connection OK<br>";
} catch (Exception $e) {
    echo "❌ Database connection FAILED: " . $e->getMessage() . "<br>";
    exit;
}

echo "<hr>";

// 3. Check if users table exists
echo "<h3>3. Check public.users Table</h3>";
$table_query = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'users' AND table_schema = 'public')";
$result = $pdo->query($table_query);
$table_exists = $result->fetchColumn();

if ($table_exists) {
    echo "✅ Table 'users' exists<br>";
} else {
    echo "❌ Table 'users' NOT found<br>";
    exit;
}

echo "<hr>";

// 4. Count total users
echo "<h3>4. Total Users in Database</h3>";
$count_query = "SELECT COUNT(*) FROM public.users";
$count_result = $pdo->query($count_query);
$total_users = $count_result->fetchColumn();
echo "Total users: " . $total_users . "<br>";

if ($total_users == 0) {
    echo "⚠️ WARNING: No users in database!<br>";
}

echo "<hr>";

// 5. Show all users (anonymized)
echo "<h3>5. All Users in Database</h3>";
$users_query = "SELECT id, username, email, nama_lengkap, LENGTH(password) as password_length FROM public.users";
$users_result = $pdo->query($users_query);
$users = $users_result->fetchAll(PDO::FETCH_ASSOC);

if (!empty($users)) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Nama</th><th>Password Hash Length</th></tr>";
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>" . $u['id'] . "</td>";
        echo "<td>" . htmlspecialchars($u['username']) . "</td>";
        echo "<td>" . htmlspecialchars($u['email']) . "</td>";
        echo "<td>" . htmlspecialchars($u['nama_lengkap']) . "</td>";
        echo "<td>" . $u['password_length'] . " chars</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found";
}

echo "<hr>";

// 6. Test login with admin
echo "<h3>6. Test Login with admin / Admin123!</h3>";
$test_username = 'admin';
$test_password = 'Admin123!';

$login_query = "SELECT id, username, password, nama_lengkap, email, nik FROM public.users WHERE username = ?";
$stmt = $pdo->prepare($login_query);
$stmt->execute([$test_username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ User 'admin' found in database<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Password hash (first 50 chars): " . substr($user['password'], 0, 50) . "...<br>";
    
    $verify = password_verify($test_password, $user['password']);
    if ($verify) {
        echo "✅ Password verification: SUCCESS<br>";
        echo "✅ LOGIN SHOULD WORK!<br>";
    } else {
        echo "❌ Password verification: FAILED<br>";
        echo "Password entered: " . $test_password . "<br>";
    }
} else {
    echo "❌ User 'admin' NOT found<br>";
}

echo "<hr>";

// 7. Test fetchOne function
echo "<h3>7. Test fetchOne() Function</h3>";
try {
    $test_user = fetchOne("SELECT * FROM public.users WHERE username = ?", ['admin']);
    if ($test_user) {
        echo "✅ fetchOne() works correctly<br>";
    } else {
        echo "❌ fetchOne() returned null<br>";
    }
} catch (Exception $e) {
    echo "❌ fetchOne() error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 8. Test session
echo "<h3>8. Test Session</h3>";
session_start();
$_SESSION['test'] = 'works';
if (isset($_SESSION['test'])) {
    echo "✅ Session works<br>";
} else {
    echo "❌ Session doesn't work<br>";
}

echo "<hr>";

echo "<h2>SUMMARY</h2>";
echo "<p>If all checks above are ✅, then login.php should work.</p>";
echo "<p>If any check is ❌, that's the problem.</p>";
echo "<p><a href='login.php'>Back to Login</a></p>";
?>