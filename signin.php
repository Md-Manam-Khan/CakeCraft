<?php
require "config.php";
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_name = ? OR email = ? OR phone = ?");
    $stmt->bind_param("sss", $login, $login, $login);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) 
        {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) 
            {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $stmt->close();
            $conn->close();
            header("Location: dashboard.php");
            exit;
        } 
        else 
        {
            $msg = "Incorrect password.";
        }
    } 
    else 
    {
        $msg = "No account found with that ID/Email/Phone.";
    }
    $stmt->close();
    $conn->close();
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
                <p class="msgbox"><?php echo $msg; ?></p>
            <?php } ?>
            <form action="signin.php" method="POST">
                <label for="login">ID Name, Email or Phone Number</label>
                <input type="text" id="login" name="login" placeholder="Enter ID name, email or phone number" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <button type="submit">Sign In</button>
            </form>
            <p class="swtext">Don't have an account? <a href="signup.php">Sign Up</a></p>
            <a href="index.html" class="backlink">← Back to Home</a>
        </div>
    </div>
</body>
</html>