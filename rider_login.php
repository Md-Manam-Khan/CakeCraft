<?php
require "config.php";
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    csrf_check();
    $login = trim(isset($_POST['login']) ? $_POST['login'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (login_blocked($conn, 'rider', $login)) 
        {
        $msg = "Too many failed attempts. Please wait 15 minutes and try again.";
    } 
    else 
        {
        $stmt = $conn->prepare("SELECT id, id_name, password FROM riders WHERE id_name = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $rider = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($rider) 
            {
            $hash = $rider['password'];
        } 
        else 
            {
            $hash = DUMMY_HASH;
        }
        if (password_verify($password, $hash) && $rider) 
            {
            login_clear($conn, 'rider', $login);
            session_regenerate_id(true);
            $_SESSION['rider_id'] = $rider['id'];
            $_SESSION['rider_name'] = $rider['id_name'];
            $conn->close();
            header("Location: rider.php");
            exit;
        } 
        else 
            {
            login_fail($conn, 'rider', $login);
            $msg = "Incorrect rider ID name or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Login - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="riderpage">
    <div class="pagewrap">
        <div class="form">
            <h2>Rider Login</h2>
            <p class="formtext">Delivery partners of CakeCraft only.</p>
            <?php if (!empty($msg)) 
                { ?>
                <p class="msgbox"><?php echo e($msg); ?></p>
            <?php } ?>
            <form action="rider_login.php" method="POST">
                <?php echo csrf_field(); ?>
                <label for="login">Rider ID Name</label>
                <input type="text" id="login" name="login" maxlength="50" placeholder="Enter your rider ID name" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" maxlength="72" placeholder="Enter your password" required>
                <button type="submit">Login</button>
            </form>
            <a href="index.html" class="backlink">← Back to Home</a>
        </div>
    </div>
</body>
</html>
