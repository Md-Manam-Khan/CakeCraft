<?php
require "config.php";
if (!isset($_SESSION['user_id'])) 
    {
    header("Location: signin.php");
    exit;
}
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    $flavor = trim($_POST['flavor']);
    $size = trim($_POST['size']);
    $note = trim($_POST['note']);
    if (empty($flavor) || empty($size)) 
        {
        $msg = "Flavor and size are required.";
    } 
    else 
        {
        if (empty($note)) 
            {
            $notetxt = "None";
        } 
        else 
            {
            $notetxt = $note;
        }
        $details = "Flavor: $flavor | Size: $size | Note: " . $notetxt;
        $stmt = $conn->prepare("INSERT INTO orders (user_id, cake_details, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("is", $_SESSION['user_id'], $details);
        if ($stmt->execute()) 
            {
            $msg = "Order placed successfully! Track it under 'My Orders'.";
        } 
        else 
            {
            $msg = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Cake - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashpage">
    <div class="pagewrap">
        <div class="form">
            <h2>Customize Your Cake</h2>
            <p class="formtext">Tell us what you'd like and we'll get baking!</p>
            <?php if (!empty($msg)) { ?>
                <p class="msgbox"><?php echo htmlspecialchars($msg); ?></p>
            <?php } ?>
            <form action="customize.php" method="POST">
                <label for="flavor">Cake Flavor</label>
                <input type="text" id="flavor" name="flavor" placeholder="e.g. Chocolate, Vanilla, Red Velvet" required>
                <label for="size">Cake Size</label>
                <input type="text" id="size" name="size" placeholder="e.g. Small, Medium, Large" required>
                <label for="note">Special Instructions</label>
                <textarea id="note" name="note" placeholder="Any message on the cake, allergies, etc. (optional)" rows="4"></textarea>
                <button type="submit">Place Order</button>
            </form>
            <a href="dashboard.php" class="backlink">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>