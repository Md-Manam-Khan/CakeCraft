<?php
require "config.php";
if (!isset($_SESSION['rider_id'])) 
    {
    header("Location: rider_login.php");
    exit;
}
$msg1 = "";
$rname = $_SESSION['rider_name'];
$my_id = (int) $_SESSION['rider_id'];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && ($_POST['action'] === 'accept_job' || $_POST['action'] === 'decline_job')) 
    {
    $order_id = (int) $_POST['order_id'];
    if ($_POST['action'] === 'accept_job') 
        {
        $newstat = 'accepted';
    } 
    else 
        {
        $newstat = 'declined';
    }
    $update = $conn->prepare("UPDATE orders SET rider_status = ? WHERE id = ? AND rider_id = ? AND rider_status = 'pending' AND status = 'accepted'");
    $update->bind_param("sii", $newstat, $order_id, $my_id);
    $update->execute();
    if ($update->affected_rows > 0) 
        {
        if ($newstat === 'accepted') 
            {
            $msg1 = "You accepted order #$order_id. You can now send updates.";
        } 
        else 
            {
            $msg1 = "You declined order #$order_id.";
        }
    } 
    else 
        {
        $msg1 = "That assignment is no longer available.";
    }
    $update->close();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && ($_POST['action'] === 'update' || $_POST['action'] === 'done')) 
    {
    $order_id = (int) $_POST['order_id'];
    $check = $conn->prepare("SELECT id FROM orders WHERE id = ? AND status = 'accepted' AND rider_id = ? AND rider_status = 'accepted'");
    $check->bind_param("ii", $order_id, $my_id);
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
$stmt = $conn->prepare("SELECT orders.id, orders.cake_details, orders.address, orders.total_price, users.full_name FROM orders JOIN users ON orders.user_id = users.id WHERE orders.status = 'accepted' AND orders.rider_id = ? AND orders.rider_status = 'accepted' ORDER BY orders.created_at ASC");
$stmt->bind_param("i", $my_id);
$stmt->execute();
$myorders = $stmt->get_result();
$stmtn = $conn->prepare("SELECT orders.id, orders.cake_details, orders.address, orders.total_price, users.full_name FROM orders JOIN users ON orders.user_id = users.id WHERE orders.status = 'accepted' AND orders.rider_id = ? AND orders.rider_status = 'pending' ORDER BY orders.created_at ASC");
$stmtn->bind_param("i", $my_id);
$stmtn->execute();
$newjobs = $stmtn->get_result();
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
        <?php if (!empty($msg1)) 
            { ?>
            <p class="msgbox" style="max-width:700px;margin:0 auto 20px;"><?php echo htmlspecialchars($msg1); ?></p>
        <?php } ?>
        <div class="adminboxrow" style="flex-direction:column;align-items:center;">
            <div class="adminbox riderbox">
                <h2>New Assignments</h2>
                <?php if ($newjobs->num_rows === 0) 
                    { ?>
                    <p class="formtext">No new assignments right now.</p>
                <?php } 
                else 
                { ?>
                    <?php while ($job = $newjobs->fetch_assoc()) 
                        { ?>
                        <div class="ordercard">
                            <p class="ordernum">Order #<?php echo $job['id']; ?></p>
                            <p><strong><?php echo htmlspecialchars($job['full_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($job['cake_details']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($job['address']); ?></p>
                            <p><strong>Total:</strong> <?php echo tk($job['total_price']); ?></p>
                            <div class="orderbtns">
                                <form action="rider.php" method="POST">
                                    <input type="hidden" name="action" value="accept_job">
                                    <input type="hidden" name="order_id" value="<?php echo $job['id']; ?>">
                                    <button type="submit" class="okbtn">Accept</button>
                                </form>
                                <form action="rider.php" method="POST">
                                    <input type="hidden" name="action" value="decline_job">
                                    <input type="hidden" name="order_id" value="<?php echo $job['id']; ?>">
                                    <button type="submit" class="nobtn">Decline</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="adminbox riderbox">
                <h2>Ongoing Orders</h2>
                <?php if ($myorders->num_rows === 0) 
                    { ?>
                    <p class="formtext">No ongoing orders right now.</p>
                <?php } 
                else 
                { ?>
                    <?php while ($order = $myorders->fetch_assoc()) 
                        { ?>
                        <div class="ordercard">
                            <p class="ordernum">Order #<?php echo $order['id']; ?></p>
                            <p><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['cake_details']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                            <p><strong>Total:</strong> <?php echo tk($order['total_price']); ?></p>
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