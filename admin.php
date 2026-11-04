<?php
require "config.php";
if (!isset($_SESSION['admin_logged_in'])) 
    {
    header("Location: admin_login.php");
    exit;
}
$msg1 = "";
$msg2 = "";
$msg3 = "";
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
        $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update->bind_param("si", $stat, $order_id);
        $update->execute();
        $update->close();
        $msg2 = "Order #$order_id has been accepted.";
    } 
    else 
        {
        $stat = 'declined';
        $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update->bind_param("si", $stat, $order_id);
        $update->execute();
        $update->close();
        $msg2 = "Order #$order_id has been declined.";
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_rider') 
    {
    $rname = trim($_POST['rname']);
    $rpass = $_POST['rpass'];
    if (empty($rname) || empty($rpass)) 
        {
        $msg3 = "Rider ID name and password are required.";
    } 
    else 
        {
        $check = $conn->prepare("SELECT id FROM riders WHERE id_name = ?");
        $check->bind_param("s", $rname);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) 
            {
            $msg3 = "That rider ID name already exists.";
        } 
        else 
            {
            $hashpass = password_hash($rpass, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO riders (id_name, password) VALUES (?, ?)");
            $insert->bind_param("ss", $rname, $hashpass);
            $insert->execute();
            $insert->close();
            $msg3 = "Rider '$rname' has been added.";
        }
        $check->close();
    }
}
$msg4 = "";
$msg5 = "";
$openbox = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_cake') 
    {
    $openbox = "add";
    $cake_name = trim($_POST['cake_name']);
    if (empty($cake_name)) 
        {
        $msg4 = "Please write the cake's name.";
    } 
    else if (strlen($cake_name) > 100) 
        {
        $msg4 = "That name is too long (100 letters max).";
    } 
    else 
        {
        $check = $conn->prepare("SELECT id FROM cakes WHERE cake_name = ?");
        $check->bind_param("s", $cake_name);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) 
            {
            $msg4 = "'$cake_name' is already on the menu.";
        } 
        else 
            {
            $insert = $conn->prepare("INSERT INTO cakes (cake_name) VALUES (?)");
            $insert->bind_param("s", $cake_name);
            $insert->execute();
            $insert->close();
            $msg4 = "'$cake_name' has been added to the menu.";
        }
        $check->close();
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'remove_cake') 
    {
    $openbox = "remove";
    $cake_id = (int) $_POST['cake_id'];
    $check = $conn->prepare("SELECT cake_name FROM cakes WHERE id = ?");
    $check->bind_param("i", $cake_id);
    $check->execute();
    $found = $check->get_result();
    if ($found->num_rows > 0) 
        {
        $row = $found->fetch_assoc();
        $delete = $conn->prepare("DELETE FROM cakes WHERE id = ?");
        $delete->bind_param("i", $cake_id);
        $delete->execute();
        $delete->close();
        $msg5 = "'" . $row['cake_name'] . "' has been removed from the menu.";
    } 
    else 
        {
        $msg5 = "That cake is not on the menu anymore.";
    }
    $check->close();
}
$cakes_res = $conn->query("SELECT id, cake_name FROM cakes ORDER BY cake_name ASC");
$cakelist = array();
while ($c = $cakes_res->fetch_assoc()) 
    {
    $cakelist[] = $c;
}
$riders_res = $conn->query("SELECT id, id_name FROM riders ORDER BY id_name ASC");
$riderlist = array();
while ($r = $riders_res->fetch_assoc()) 
    {
    $riderlist[] = $r;
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
    <div class="editwrap">
        <button type="button" class="editbtn" onclick="togglemenu()">Edit Menu &#9662;</button>
        <div class="editdrop" id="editdrop">
            <button type="button" class="dropitem" onclick="showbox('add')">Add Cake</button>
            <button type="button" class="dropitem" onclick="showbox('remove')">Remove Cake</button>
        </div>
    </div>
    <div class="menuoverlay" id="addbox" onclick="if (event.target === this) closeboxes()">
        <div class="menubox">
            <button type="button" class="closebtn" onclick="closeboxes()">&#10005;</button>
            <h2>Add Cake</h2>
            <?php if (!empty($msg4)) 
                { ?>
                <p class="msgbox"><?php echo htmlspecialchars($msg4); ?></p>
            <?php } ?>
            <form action="admin.php" method="POST">
                <input type="hidden" name="action" value="add_cake">
                <label for="cake_name">Cake Name</label>
                <input type="text" id="cake_name" name="cake_name" maxlength="100" placeholder="e.g. Chocolate Truffle" required>
                <button type="submit" class="addbtn">Add to Menu</button>
            </form>
        </div>
    </div>
    <div class="menuoverlay" id="removebox" onclick="if (event.target === this) closeboxes()">
        <div class="menubox">
            <button type="button" class="closebtn" onclick="closeboxes()">&#10005;</button>
            <h2>Remove Cake</h2>
            <?php if (!empty($msg5)) 
                { ?>
                <p class="msgbox"><?php echo htmlspecialchars($msg5); ?></p>
            <?php } ?>
            <div class="itemlist">
                <?php if (count($cakelist) === 0) 
                    { ?>
                    <p class="formtext">The menu is empty right now.</p>
                <?php } 
                else 
                { ?>
                    <?php foreach ($cakelist as $c) 
                        { ?>
                        <div class="menuline">
                            <span><?php echo htmlspecialchars($c['cake_name']); ?></span>
                            <form action="admin.php" method="POST">
                                <input type="hidden" name="action" value="remove_cake">
                                <input type="hidden" name="cake_id" value="<?php echo $c['id']; ?>">
                                <button type="submit" class="nobtn deletebtn">Delete</button>
                            </form>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
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
            <div class="adminbox">
                <h2>Riders</h2>
                <?php if (!empty($msg3)) 
                    { ?>
                    <p class="msgbox"><?php echo htmlspecialchars($msg3); ?></p>
                <?php } ?>
                <form action="admin.php" method="POST" class="smallform">
                    <input type="hidden" name="action" value="add_rider">
                    <label for="rname">Rider ID Name</label>
                    <input type="text" id="rname" name="rname" placeholder="Choose a rider ID name" required>
                    <label for="rpass">Rider Password</label>
                    <input type="text" id="rpass" name="rpass" placeholder="Set a password for the rider" required>
                    <button type="submit" class="addbtn">Add Rider</button>
                </form>
                <div class="itemlist">
                    <?php if (count($riderlist) === 0) 
                        { ?>
                        <p class="formtext">No riders added yet.</p>
                    <?php } 
                    else 
                    { ?>
                        <?php foreach ($riderlist as $r) 
                            { ?>
                            <div class="itemline">
                                <span><?php echo htmlspecialchars($r['id_name']); ?></span>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        function togglemenu()
            {
            document.getElementById('editdrop').classList.toggle('show');
        }
        function closeboxes()
            {
            document.getElementById('addbox').classList.remove('show');
            document.getElementById('removebox').classList.remove('show');
        }
        function showbox(which)
            {
            closeboxes();
            document.getElementById('editdrop').classList.remove('show');
            if (which === 'add') 
                {
                document.getElementById('addbox').classList.add('show');
                document.getElementById('cake_name').focus();
            } 
            else 
                {
                document.getElementById('removebox').classList.add('show');
            }
        }
        document.addEventListener('click', function (e) 
            {
            if (!e.target.closest('.editwrap')) 
                {
                document.getElementById('editdrop').classList.remove('show');
            }
        });
        <?php if ($openbox !== "") 
            { ?>
        showbox('<?php echo $openbox; ?>');
        <?php } ?>
    </script>
</body>
</html>