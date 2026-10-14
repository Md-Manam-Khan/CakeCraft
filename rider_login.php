<?php
require "config.php";
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM riders WHERE id_name = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) 
        {
        $rider = $res->fetch_assoc();
        if (password_verify($password, $rider['password'])) 
            {
            $_SESSION['rider_id'] = $rider['id'];
            $_SESSION['rider_name'] = $rider['id_name'];
            $stmt->close();
            $conn->close();
            header("Location: rider.php");
            exit;
        } 
        else 
        {
            $msg = "Incorrect password.";
        }
    } 
    else 
    {
        $msg = "No rider found with that ID name.";
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
                <p class="msgbox"><?php echo htmlspecialchars($msg); ?></p>
            <?php } ?>
            <form action="rider_login.php" method="POST">
                <label for="login">Rider ID Name</label>
                <input type="text" id="login" name="login" placeholder="Enter your rider ID name" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <button type="submit">Login</button>
            </form>
            <a href="index.html" class="backlink">← Back to Home</a>
        </div>
    </div>
</body>
</html>