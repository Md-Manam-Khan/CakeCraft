<?php
require "config.php";
if (!empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id'])) 
    {
    header("Location: admin.php");
    exit;
}
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    csrf_check();
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (login_blocked($conn, 'admin', $username)) 
        {
        $msg = "Too many failed attempts. Please wait 15 minutes and try again.";
    } 
    else 
        {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $admin = $res->fetch_assoc();
        $stmt->close();
        if ($admin) 
            {
            $hash = $admin['password'];
        } 
        else 
            {
            $hash = DUMMY_HASH;
        }
        if (password_verify($password, $hash) && $admin) 
            {
            login_clear($conn, 'admin', $username);
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $conn->close();
            header("Location: admin.php");
            exit;
        } 
        else 
            {
            login_fail($conn, 'admin', $username);
            $msg = "Invalid admin credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="adminpage">
    <div class="pagewrap">
        <div class="form">
            <h2>Admin Login</h2>
            <p class="formtext">Restricted access — CakeCraft staff only.</p>
            <?php if (!empty($msg)) { ?>
                <p class="msgbox"><?php echo e($msg); ?></p>
            <?php } ?>
            <form action="admin_login.php" method="POST" autocomplete="off">
                <?php echo csrf_field(); ?>
                <label for="username">Admin Username</label>
                <input type="text" id="username" name="username" maxlength="50" placeholder="Enter admin username" required>
                <label for="password">Admin Password</label>
                <input type="password" id="password" name="password" maxlength="72" placeholder="Enter admin password" required>
                <button type="submit">Login</button>
            </form>
            <a href="index.html" class="backlink">← Back to Home</a>
        </div>
    </div>
</body>
</html>