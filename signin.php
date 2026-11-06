<?php
require "config.php";
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    csrf_check();
    $login = trim(isset($_POST['login']) ? $_POST['login'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (login_blocked($conn, 'user', $login)) 
        {
        $msg = "Too many failed attempts. Please wait 15 minutes and try again.";
    } 
    else 
        {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE id_name = ? OR email = ? OR phone = ?");
        $stmt->bind_param("sss", $login, $login, $login);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user) 
            {
            $hash = $user['password'];
        } 
        else 
            {
            $hash = DUMMY_HASH;
        }
        if (password_verify($password, $hash) && $user) 
            {
            login_clear($conn, 'user', $login);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $conn->close();
            header("Location: dashboard.php");
            exit;
        } 
        else 
            {
            login_fail($conn, 'user', $login);
            $msg = "Incorrect ID name / email / phone or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="signinpg">
    <div class="pagewrap">
        <div class="form">
            <h2>Sign In</h2>
            <p class="formtext">Welcome back to CakeCraft!</p>
            <?php if (!empty($msg)) 
                { ?>
                <p class="msgbox"><?php echo e($msg); ?></p>
            <?php } ?>
            <form action="signin.php" method="POST">
                <?php echo csrf_field(); ?>
                <label for="login">ID Name, Email or Phone Number</label>
                <input type="text" id="login" name="login" maxlength="100" placeholder="Enter ID name, email or phone number" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" maxlength="72" placeholder="Enter your password" required>
                <button type="submit">Sign In</button>
            </form>
            <p class="swtext">Don't have an account? <a href="signup.php">Sign Up</a></p>
            <a href="index.html" class="backlink">← Back to Home</a>
        </div>
    </div>
</body>
</html>
