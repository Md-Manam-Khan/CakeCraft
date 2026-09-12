<?php
require "config.php";
if (!isset($_SESSION['admin_logged_in'])) 
    {
    header("Location: admin_login.php");
    exit;
}
$msg1 = "";
$msg2 = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_item') 
    {
    $item_name = trim($_POST['item_name']);
    $quantity = (int) $_POST['quantity'];
    $check = $conn->prepare("SELECT id, quantity FROM items WHERE item_name = ?");
    $check->bind_param("s", $item_name);
    $check->execute();
    $found = $check->get_result();
    if ($found->num_rows > 0) 
        {
        $row = $found->fetch_assoc();
        $qty2 = $row['quantity'] + $quantity;
        $update = $conn->prepare("UPDATE items SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $qty2, $row['id']);
        $update->execute();
        $update->close();
        $msg1 = "Updated stock for '$item_name'.";
    } 
    else 
    {
        $insert = $conn->prepare("INSERT INTO items (item_name, quantity) VALUES (?, ?)");
        $insert->bind_param("si", $item_name, $quantity);
        $insert->execute();
        $insert->close();
        $msg1 = "Added new item '$item_name'.";
    }
    $check->close();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && ($_POST['action'] === 'accept' || $_POST['action'] === 'decline')) {
    $order_id = (int) $_POST['order_id'];
    if ($_POST['action'] === 'accept') 
        {
        $stat = 'accepted';
    } 
    else 
        {
        $stat = 'declined';
    }
    $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update->bind_param("si", $stat, $order_id);
    $update->execute();
    $update->close();
    $msg2 = "Order #$order_id has been $stat.";
}
$items_res = $conn->query("SELECT * FROM items ORDER BY item_name ASC");
$orders_res = $conn->query("
    SELECT orders.id, orders.cake_details, orders.created_at, users.full_name
    FROM orders
    JOIN users ON orders.user_id = users.id
    WHERE orders.status = 'pending'
    ORDER BY orders.created_at ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="adminpage">
    <a href="admin_logout.php" class="devbtn logoutbtn">Logout</a>
    <div class="adminwrap">
        <h1 class="dashtitle">Admin Panel</h1>
        <div class="adminboxrow">
            <div class="adminbox">
                <h2>Items</h2>
                <?php if (!empty($msg1)) 
                    { ?>
                    <p class="msgbox"><?php echo htmlspecialchars($msg1); ?></p>
                <?php } ?>
                <form action="admin.php" method="POST" class="smallform">
                    <input type="hidden" name="action" value="add_item">
                    <label for="item_name">Item Name</label>
                    <input type="text" id="item_name" name="item_name" placeholder="e.g. Flour, Sugar, Eggs" required>
                    <label for="quantity">Quantity to Add</label>
                    <input type="number" id="quantity" name="quantity" min="1" placeholder="e.g. 10" required>
                    <button type="submit" class="addbtn">Add / Update Stock</button>
                </form>
                <div class="itemlist">
                    <?php if ($items_res->num_rows === 0) 
                        { ?>
                        <p class="formtext">No items added yet.</p>
                    <?php } 
                    else 
                    { ?>
                        <?php while ($item = $items_res->fetch_assoc()) 
                            { ?>
                            <div class="itemline">
                                <span><?php echo htmlspecialchars($item['item_name']); ?></span>
                                <span class="qtytxt"><?php echo $item['quantity']; ?> left</span>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <div class="adminbox">
                <h2>Current Orders</h2>
                <?php if (!empty($msg2)) 
                    { ?>
                    <p class="msgbox"><?php echo htmlspecialchars($msg2); ?></p>
                <?php } ?>
                <?php if ($orders_res->num_rows === 0) 
                    { ?>
                    <p class="formtext">No pending orders right now.</p>
                <?php } 
                else 
                { ?>
                    <?php while ($order = $orders_res->fetch_assoc()) 
                        { ?>
                        <div class="ordercard">
                            <p><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['cake_details']); ?></p>
                            <div class="orderbtns">
                                <form action="admin.php" method="POST">
                                    <input type="hidden" name="action" value="accept">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="okbtn">Accept</button>
                                </form>
                                <form action="admin.php" method="POST">
                                    <input type="hidden" name="action" value="decline">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="nobtn">Decline</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>