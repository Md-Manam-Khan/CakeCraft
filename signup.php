<?php
require "config.php";
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $idname = trim($_POST['idname']);
    $password = $_POST['password'];
    if (empty($name) || empty($email) || empty($phone) || empty($idname) || empty($password)) 
        {
        $msg = "All fields are required.";
    } 
    else 
        {
        $hashpass = password_hash($password, PASSWORD_DEFAULT);
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR id_name = ?");
        $check->bind_param("ss", $email, $idname);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) 
            {
            $msg = "Email or ID Name already exists.";
        }
        $check->close();
        if (empty($msg)) 
            {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, id_name, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $idname, $hashpass);
            if ($stmt->execute()) 
                {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                $stmt->close();
                $conn->close();
                header("Location: dashboard.php");
                exit;
            } 
            else 
                {
                $msg = "Error: " . $stmt->error;
                $stmt->close();
            }
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="signuppg">
    <div class="pagewrap">
        <div class="form">
            <h2>Create Your Account</h2>
            <p class="formtext">Join CakeCraft and start creating your dream cakes!</p>
            <?php if (!empty($msg)) { ?>
                <p class="msgbox"><?php echo htmlspecialchars($msg); ?></p>
            <?php } ?>
            <form action="signup.php" method="POST">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                <label for="idname">ID Name</label>
                <input type="text" id="idname" name="idname" placeholder="Choose an ID name" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" minlength="6" required>
                <button type="submit">Sign Up</button>
            </form>
            <p class="swtext">Already have an account? <a href="signin.php">Sign In</a></p>
            <a href="index.html" class="backlink">← Back to Home</a>
        </div>
    </div>
</body>
</html>