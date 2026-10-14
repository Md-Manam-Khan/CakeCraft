<?php
require "config.php";
if (!isset($_SESSION['rider_id'])) 
    {
    header("Location: rider_login.php");
    exit;
}
$msg1 = "";
$rname = $_SESSION['rider_name'];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && ($_POST['action'] === 'update' || $_POST['action'] === 'done')) 
    {
    $order_id = (int) $_POST['order_id'];
    $check = $conn->prepare("SELECT id FROM orders WHERE id = ? AND status = 'accepted'");
    $check->bind_param("i", $order_id);
    $check->execute();
    $found = $check->get_result();
    if ($found->num_rows > 0) 
        {
        if ($_POST['action'] === 'done') 
            {
            $said = "Delivery done";
        } 
        else 
            {
            $said = trim($_POST['text']);
        }
        if (empty($said)) 
            {
            $msg1 = "Write an update first.";
        } 
        else 
            {
            $insert = $conn->prepare("INSERT INTO order_updates (order_id, update_text) VALUES (?, ?)");
            $insert->bind_param("is", $order_id, $said);
            $insert->execute();
            $insert->close();
            if ($_POST['action'] === 'done') 
                {
                $stat = 'delivered';
                $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $update->bind_param("si", $stat, $order_id);
                $update->execute();
                $update->close();
                $msg1 = "Order #$order_id marked as delivered.";
            } 
            else 
                {
                $msg1 = "Update sent for order #$order_id.";
            }
        }
    }
    $check->close();
}
$stmt = $conn->prepare("SELECT orders.id, orders.cake_details, users.full_name FROM orders JOIN users ON orders.user_id = users.id WHERE orders.status = 'accepted' ORDER BY orders.created_at ASC");
$stmt->execute();
$myorders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Panel - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="riderpage">
    <a href="rider_logout.php" class="devbtn logoutbtn">Logout</a>
    <div class="adminwrap">
        <h1 class="dashtitle" style="text-align:center;">Rider Panel</h1>
        <p class="dashsub" style="text-align:center;">Hi, <?php echo htmlspecialchars($rname); ?>!</p>
        <div class="adminboxrow">
            <div class="adminbox riderbox">
                <h2>Ongoing Orders</h2>
                <?php if (!empty($msg1)) 
                    { ?>
                    <p class="msgbox"><?php echo htmlspecialchars($msg1); ?></p>
                <?php } ?>
                <?php if ($myorders->num_rows === 0) 
                    { ?>
                    <p class="formtext">No ongoing orders right now.</p>
                <?php } 
                else 
                { ?>
                    <?php while ($order = $myorders->fetch_assoc()) 
                        { ?>
                        <div class="ordercard">
                            <p><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['cake_details']); ?></p>
                            <?php
                            $stmt2 = $conn->prepare("SELECT update_text, created_at FROM order_updates WHERE order_id = ? ORDER BY id ASC");
                            $stmt2->bind_param("i", $order['id']);
                            $stmt2->execute();
                            $ups = $stmt2->get_result();
                            if ($ups->num_rows > 0) 
                                { ?>
                                <div class="updatebox">
                                    <p class="updatetitle">Updates sent</p>
                                    <?php while ($up = $ups->fetch_assoc()) 
                                        { ?>
                                        <p class="updateline"><?php echo htmlspecialchars($up['update_text']); ?> <span class="updatetime"><?php echo date("d M, h:i A", strtotime($up['created_at'])); ?></span></p>
                                    <?php } ?>
                                </div>
                            <?php } 
                            $stmt2->close(); ?>
                            <form action="rider.php" method="POST" class="updateform">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="text" name="text" maxlength="200" placeholder="e.g. Received, Reached, On the way">
                                <div class="updatebtns">
                                    <button type="submit" name="action" value="update" class="okbtn">Send Update</button>
                                    <button type="submit" name="action" value="done" class="donebtn">Delivery Done</button>
                                </div>
                            </form>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>