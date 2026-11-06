<?php
require "config.php";
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    csrf_check();
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $idname = trim(isset($_POST['idname']) ? $_POST['idname'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if ($name === '' || $email === '' || $phone === '' || $idname === '' || $password === '') 
        {
        $msg = "All fields are required.";
    } 
    else if (mb_strlen($name) < 2 || mb_strlen($name) > 100) 
        {
        $msg = "Full name must be between 2 and 100 characters.";
    } 
    else if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
        $msg = "Please enter a valid email address.";
    } 
    else if (!preg_match('/^\+?[0-9][0-9 \-]{6,18}$/', $phone)) 
        {
        $msg = "Please enter a valid phone number (digits only, 7 to 20 characters).";
    } 
    else if (!valid_idname($idname)) 
        {
        $msg = "ID Name must be 3-50 characters: letters, numbers, dot, underscore or dash, with at least one letter.";
    } 
    else if (!valid_password($password)) 
        {
        $msg = "Password must be between 8 and 72 characters.";
    } 
    else 
        {
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
            $hashpass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, id_name, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $idname, $hashpass);
            try 
                {
                $stmt->execute();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                $stmt->close();
                $conn->close();
                header("Location: dashboard.php");
                exit;
            } 
            catch (mysqli_sql_exception $ex) 
                {
                error_log("CakeCraft signup error: " . $ex->getMessage());
                if ($ex->getCode() == 1062) 
                    {
                    $msg = "Email or ID Name already exists.";
                } 
                else 
                    {
                    $msg = "Sorry, we could not create your account. Please try again.";
                }
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
                <p class="msgbox"><?php echo e($msg); ?></p>
            <?php } ?>
            <form action="signup.php" method="POST">
                <?php echo csrf_field(); ?>
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" maxlength="100" placeholder="Enter your full name" value="<?php echo e(isset($name) ? $name : ''); ?>" required>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="100" placeholder="Enter your email" value="<?php echo e(isset($email) ? $email : ''); ?>" required>
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" maxlength="20" placeholder="Enter your phone number" value="<?php echo e(isset($phone) ? $phone : ''); ?>" required>
                <label for="idname">ID Name</label>
                <input type="text" id="idname" name="idname" maxlength="50" placeholder="Choose an ID name" value="<?php echo e(isset($idname) ? $idname : ''); ?>" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="8" maxlength="72" placeholder="Create a password (min 8 characters)" required>
                <button type="submit">Sign Up</button>
            </form>
            <p class="swtext">Already have an account? <a href="signin.php">Sign In</a></p>
            <a href="index.html" class="backlink">← Back to Home</a>
        </div>
    </div>
</body>
</html>
