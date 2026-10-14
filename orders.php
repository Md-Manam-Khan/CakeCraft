<?php
require "config.php";
if (!isset($_SESSION['user_id'])) 
    {
    header("Location: signin.php");
    exit;
}
$stmt = $conn->prepare("SELECT id, cake_details, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashpage">
    <div class="pagewrap">
        <div class="form bigform">
            <h2>My Orders</h2>
            <?php if ($orders->num_rows === 0) 
                { ?>
                <p class="formtext">You haven't placed any orders yet.</p>
            <?php } 
            else 
            { ?>
                <?php while ($order = $orders->fetch_assoc()) 
                    { ?>
                    <div class="ordercard">
                        <p><?php echo htmlspecialchars($order['cake_details']); ?></p>
                        <span class="statustxt status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                        <?php
                        $stmt2 = $conn->prepare("SELECT update_text, created_at FROM order_updates WHERE order_id = ? ORDER BY id ASC");
                        $stmt2->bind_param("i", $order['id']);
                        $stmt2->execute();
                        $ups = $stmt2->get_result();
                        if ($ups->num_rows > 0) 
                            { ?>
                            <div class="updatebox">
                                <p class="updatetitle">Rider Updates</p>
                                <?php while ($up = $ups->fetch_assoc()) 
                                    { ?>
                                    <p class="updateline"><?php echo htmlspecialchars($up['update_text']); ?> <span class="updatetime"><?php echo date("d M, h:i A", strtotime($up['created_at'])); ?></span></p>
                                <?php } ?>
                            </div>
                        <?php } 
                        $stmt2->close(); ?>
                    </div>
                <?php } ?>
            <?php } ?>
            <a href="dashboard.php" class="backlink">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>