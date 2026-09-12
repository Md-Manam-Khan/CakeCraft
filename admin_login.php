<?php
session_start();
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    $auser = "admin";
    $apass = "admin123";
    if ($_POST['username'] === $auser && $_POST['password'] === $apass)
        {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } 
    else 
        {
        $msg = "Invalid admin credentials.";
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
                <p class="msgbox"><?php echo htmlspecialchars($msg); ?></p>
            <?php } ?>
            <form action="admin_login.php" method="POST">
                <label for="username">Admin Username</label>
                <input type="text" id="username" name="username" placeholder="Enter admin username" required>
                <label for="password">Admin Password</label>
                <input type="password" id="password" name="password" placeholder="Enter admin password" required>
                <button type="submit">Login</button>
            </form>
            <a href="index.html" class="backlink">← Back to Home</a>
        </div>
    </div>
</body>
</html>