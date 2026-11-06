<?php
require "config.php";
if (!isset($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) 
    {
    header("Location: admin_login.php");
    exit;
}
$admin_id = (int) $_SESSION['admin_id'];
if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
    csrf_check();
}
$msg1 = "";
$msg2 = "";
$msg3 = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_item') 
    {
    $item_name = trim($_POST['item_name']);
    $quantity = (int) $_POST['quantity'];
    if ($item_name === '' || strlen($item_name) > 100 || $quantity < 1 || $quantity > 1000000) 
        {
        $msg1 = "Enter an item name (max 100 letters) and a quantity between 1 and 1,000,000.";
    } 
    else 
        {
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
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && ($_POST['action'] === 'accept' || $_POST['action'] === 'decline')) {
    $order_id = (int) $_POST['order_id'];
    if ($_POST['action'] === 'accept') 
        {
        $stat = 'accepted';
        $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND status = 'pending'");
        $update->bind_param("si", $stat, $order_id);
        $update->execute();
        $update->close();
        $msg2 = "Order #$order_id has been accepted. Now assign it to a rider.";
    } 
    else 
        {
        $stat = 'declined';
        $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND status = 'pending'");
        $update->bind_param("si", $stat, $order_id);
        $update->execute();
        $update->close();
        $msg2 = "Order #$order_id has been declined.";
    }
}
$msg9 = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'assign_rider') 
    {
    $assign_rider = (int) $_POST['assign_rider_id'];
    $assign_order = (int) $_POST['assign_order_id'];
    if ($assign_rider <= 0 || $assign_order <= 0) 
        {
        $msg9 = "Select a rider and an order first.";
    } 
    else 
        {
        $check = $conn->prepare("SELECT id_name FROM riders WHERE id = ?");
        $check->bind_param("i", $assign_rider);
        $check->execute();
        $found = $check->get_result();
        if ($found->num_rows === 0) 
            {
            $msg9 = "That rider does not exist anymore.";
        } 
        else 
            {
            $rrow = $found->fetch_assoc();
            $update = $conn->prepare("UPDATE orders SET rider_id = ?, rider_status = 'pending' WHERE id = ? AND status = 'accepted' AND (rider_status IS NULL OR rider_status <> 'accepted')");
            $update->bind_param("ii", $assign_rider, $assign_order);
            $update->execute();
            if ($update->affected_rows > 0) 
                {
                $msg9 = "Order #$assign_order has been assigned to " . $rrow['id_name'] . ". Waiting for the rider to accept.";
            } 
            else 
                {
                $msg9 = "Order #$assign_order cannot be assigned right now.";
            }
            $update->close();
        }
        $check->close();
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_rider') 
    {
    $rname = trim($_POST['rname']);
    $rpass = $_POST['rpass'];
    if ($rname === '' || $rpass === '') 
        {
        $msg3 = "Rider ID name and password are required.";
    } 
    else if (!valid_idname($rname)) 
        {
        $msg3 = "Rider ID name must be 3-50 characters: letters, numbers, dot, underscore or dash, with at least one letter.";
    } 
    else if (!valid_password($rpass)) 
        {
        $msg3 = "Rider password must be between 8 and 72 characters.";
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
$msg6 = "";
$msg7 = "";
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
            $msg4 = "'$cake_name' has been added to the menu. Set its price with Edit Price - customers cannot order it until its price is above 0 TK.";
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
$msg8 = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'edit_price') 
    {
    $openbox = "editprice";
    $target = trim($_POST['price_target']);
    $newprice = trim($_POST['new_price']);
    $parts = explode(":", $target);
    $kinds = array(
        'cake' => array('cakes', 'cake_name'),
        'filling' => array('fillings', 'filling_name'),
        'size' => array('sizes', 'size_name')
    );
    if (count($parts) !== 2 || !isset($kinds[$parts[0]]) || (int) $parts[1] <= 0) 
        {
        $msg8 = "Please choose a flavor, filling or size first.";
    } 
    else if ($newprice === "" || !is_numeric($newprice) || $newprice < 0) 
        {
        $msg8 = "Please type a valid price (0 or more).";
    } 
    else if ($newprice > 99999999) 
        {
        $msg8 = "That price is too big.";
    } 
    else 
        {
        $tbl = $kinds[$parts[0]][0];
        $col = $kinds[$parts[0]][1];
        $pid = (int) $parts[1];
        $pval = round((float) $newprice, 2);
        $check = $conn->prepare("SELECT $col AS nm FROM $tbl WHERE id = ?");
        $check->bind_param("i", $pid);
        $check->execute();
        $found = $check->get_result();
        if ($found->num_rows > 0) 
            {
            $row = $found->fetch_assoc();
            $update = $conn->prepare("UPDATE $tbl SET price = ? WHERE id = ?");
            $update->bind_param("di", $pval, $pid);
            $update->execute();
            $update->close();
            $msg8 = "Price of '" . $row['nm'] . "' is now " . tk($pval) . ".";
        } 
        else 
            {
            $msg8 = "That item is not available anymore.";
        }
        $check->close();
    }
}
$msg10 = "";
$msg10ok = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'edit_credentials') 
    {
    $openbox = "credentials";
    $new_user = trim(isset($_POST['new_username']) ? $_POST['new_username'] : '');
    $new_user2 = trim(isset($_POST['confirm_username']) ? $_POST['confirm_username'] : '');
    $new_pass = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $new_pass2 = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    if ($new_user === '' || $new_user2 === '' || $new_pass === '' || $new_pass2 === '') 
        {
        $msg10 = "Please fill in all four boxes.";
    } 
    else if ($new_user !== $new_user2) 
        {
        $msg10 = "Admin Profile and Confirm Admin Profile do not match.";
    } 
    else if (!valid_idname($new_user)) 
        {
        $msg10 = "Admin Profile must be 3-50 characters: letters, numbers, dot, underscore or dash, with at least one letter.";
    } 
    else if ($new_pass !== $new_pass2) 
        {
        $msg10 = "Admin Password and Confirm Admin Password do not match.";
    } 
    else if (!valid_password($new_pass)) 
        {
        $msg10 = "Admin Password must be between 8 and 72 characters.";
    } 
    else if ($new_pass === 'admin123') 
        {
        $msg10 = "Please choose a different password from the default one.";
    } 
    else 
        {
        $check = $conn->prepare("SELECT id FROM admins WHERE username = ? AND id <> ?");
        $check->bind_param("si", $new_user, $admin_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) 
            {
            $msg10 = "That Admin Profile is already taken.";
        } 
        else 
            {
            $newhash = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
            $update->bind_param("ssi", $new_user, $newhash, $admin_id);
            $update->execute();
            $update->close();
            session_regenerate_id(true);
            $_SESSION['admin_name'] = $new_user;
            $msg10 = "Credentials saved. Use the new Admin Profile and Admin Password the next time you log in.";
            $msg10ok = true;
        }
        $check->close();
    }
}
$cakes_res = $conn->query("SELECT id, cake_name, price FROM cakes ORDER BY cake_name ASC");
$cakelist = array();
while ($c = $cakes_res->fetch_assoc()) 
    {
    $cakelist[] = $c;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_filling') 
    {
    $openbox = "addfilling";
    $filling_name = trim($_POST['filling_name']);
    if (empty($filling_name)) 
        {
        $msg6 = "Please write the filling's name.";
    } 
    else if (strlen($filling_name) > 100) 
        {
        $msg6 = "That name is too long (100 letters max).";
    } 
    else 
        {
        $check = $conn->prepare("SELECT id FROM fillings WHERE filling_name = ?");
        $check->bind_param("s", $filling_name);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) 
            {
            $msg6 = "'$filling_name' is already in the fillings list.";
        } 
        else 
            {
            $insert = $conn->prepare("INSERT INTO fillings (filling_name) VALUES (?)");
            $insert->bind_param("s", $filling_name);
            $insert->execute();
            $insert->close();
            $msg6 = "'$filling_name' has been added to the fillings. Set its price with Edit Price - customers cannot choose it until its price is above 0 TK.";
        }
        $check->close();
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'remove_filling') 
    {
    $openbox = "removefilling";
    $filling_id = (int) $_POST['filling_id'];
    $check = $conn->prepare("SELECT filling_name FROM fillings WHERE id = ?");
    $check->bind_param("i", $filling_id);
    $check->execute();
    $found = $check->get_result();
    if ($found->num_rows > 0) 
        {
        $row = $found->fetch_assoc();
        $delete = $conn->prepare("DELETE FROM fillings WHERE id = ?");
        $delete->bind_param("i", $filling_id);
        $delete->execute();
        $delete->close();
        $msg7 = "'" . $row['filling_name'] . "' has been removed from the fillings.";
    } 
    else 
        {
        $msg7 = "That filling is not in the list anymore.";
    }
    $check->close();
}
$fillings_res = $conn->query("SELECT id, filling_name, price FROM fillings ORDER BY filling_name ASC");
$fillinglist = array();
while ($fl = $fillings_res->fetch_assoc()) 
    {
    $fillinglist[] = $fl;
}
$sizes_res = $conn->query("SELECT id, size_name, price FROM sizes ORDER BY id ASC");
$sizelist = array();
while ($z = $sizes_res->fetch_assoc()) 
    {
    $sizelist[] = $z;
}
$riders_res = $conn->query("SELECT id, id_name FROM riders ORDER BY id_name ASC");
$riderlist = array();
while ($r = $riders_res->fetch_assoc()) 
    {
    $riderlist[] = $r;
}
$items_res = $conn->query("SELECT * FROM items ORDER BY item_name ASC");
$orders_res = $conn->query("
    SELECT orders.id, orders.cake_details, orders.address, orders.total_price, orders.created_at, users.full_name
    FROM orders
    JOIN users ON orders.user_id = users.id
    WHERE orders.status = 'pending'
    ORDER BY orders.created_at ASC
");
$assignable_res = $conn->query("
    SELECT orders.id, orders.rider_status, riders.id_name AS rider_name
    FROM orders
    LEFT JOIN riders ON orders.rider_id = riders.id
    WHERE orders.status = 'accepted' AND (orders.rider_status IS NULL OR orders.rider_status <> 'accepted')
    ORDER BY orders.id ASC
");
$assignable = array();
while ($as = $assignable_res->fetch_assoc()) 
    {
    $assignable[] = $as;
}
$assigned_res = $conn->query("
    SELECT orders.id, orders.status, orders.rider_status, riders.id_name AS rider_name
    FROM orders
    JOIN riders ON orders.rider_id = riders.id
    WHERE orders.rider_status IS NOT NULL
    ORDER BY orders.id DESC
    LIMIT 15
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .credwrap { position: fixed; top: 18px; right: 232px; z-index: 999; }
        @media (max-width: 600px) { .credwrap { top: 52px; right: 12px; } }
    </style>
</head>
<body class="adminpage">
    <a href="admin_logout.php" class="devbtn logoutbtn">Logout</a>
    <div class="credwrap">
        <button type="button" class="editbtn" onclick="showbox('credentials')">Edit Credentials</button>
    </div>
    <div class="editwrap">
        <button type="button" class="editbtn" onclick="togglemenu()">Edit Menu &#9662;</button>
        <div class="editdrop" id="editdrop">
            <button type="button" class="dropitem" onclick="showbox('add')">Add Cake</button>
            <button type="button" class="dropitem" onclick="showbox('remove')">Remove Cake</button>
            <button type="button" class="dropitem" onclick="showbox('addfilling')">Add Filling</button>
            <button type="button" class="dropitem" onclick="showbox('removefilling')">Remove Filling</button>
            <button type="button" class="dropitem" onclick="showbox('editprice')">Edit Price</button>
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
                    <?php echo csrf_field(); ?>
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
                    <?php echo csrf_field(); ?>
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
    <div class="menuoverlay" id="addfillingbox" onclick="if (event.target === this) closeboxes()">
        <div class="menubox">
            <button type="button" class="closebtn" onclick="closeboxes()">&#10005;</button>
            <h2>Add Filling</h2>
            <?php if (!empty($msg6)) 
                { ?>
                <p class="msgbox"><?php echo htmlspecialchars($msg6); ?></p>
            <?php } ?>
            <form action="admin.php" method="POST">
                    <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add_filling">
                <label for="filling_name">Filling Name</label>
                <input type="text" id="filling_name" name="filling_name" maxlength="100" placeholder="e.g. Caramel" required>
                <button type="submit" class="addbtn">Add to Fillings</button>
            </form>
        </div>
    </div>
    <div class="menuoverlay" id="removefillingbox" onclick="if (event.target === this) closeboxes()">
        <div class="menubox">
            <button type="button" class="closebtn" onclick="closeboxes()">&#10005;</button>
            <h2>Remove Filling</h2>
            <?php if (!empty($msg7)) 
                { ?>
                <p class="msgbox"><?php echo htmlspecialchars($msg7); ?></p>
            <?php } ?>
            <div class="itemlist">
                <?php if (count($fillinglist) === 0) 
                    { ?>
                    <p class="formtext">There are no fillings right now.</p>
                <?php } 
                else 
                { ?>
                    <?php foreach ($fillinglist as $fl) 
                        { ?>
                        <div class="menuline">
                            <span><?php echo htmlspecialchars($fl['filling_name']); ?></span>
                            <form action="admin.php" method="POST">
                    <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="remove_filling">
                                <input type="hidden" name="filling_id" value="<?php echo $fl['id']; ?>">
                                <button type="submit" class="nobtn deletebtn">Delete</button>
                            </form>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="menuoverlay" id="editpricebox" onclick="if (event.target === this) closeboxes()">
        <div class="menubox">
            <button type="button" class="closebtn" onclick="closeboxes()">&#10005;</button>
            <h2>Edit Price</h2>
            <?php if (!empty($msg8)) 
                { ?>
                <p class="msgbox"><?php echo htmlspecialchars($msg8); ?></p>
            <?php } ?>
            <form action="admin.php" method="POST">
                    <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_price">
                <label for="price_target">Flavor, Filling or Size</label>
                <select id="price_target" name="price_target" required>
                    <option value="">Choose what to price</option>
                    <optgroup label="Flavors">
                        <?php foreach ($cakelist as $c) 
                            { ?>
                            <option value="cake:<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['cake_name']); ?> (now <?php echo tk($c['price']); ?>)</option>
                        <?php } ?>
                    </optgroup>
                    <optgroup label="Fillings">
                        <?php foreach ($fillinglist as $fl) 
                            { ?>
                            <option value="filling:<?php echo $fl['id']; ?>"><?php echo htmlspecialchars($fl['filling_name']); ?> (now <?php echo tk($fl['price']); ?>)</option>
                        <?php } ?>
                    </optgroup>
                    <optgroup label="Sizes">
                        <?php foreach ($sizelist as $z) 
                            { ?>
                            <option value="size:<?php echo $z['id']; ?>"><?php echo htmlspecialchars($z['size_name']); ?> (now <?php echo tk($z['price']); ?>)</option>
                        <?php } ?>
                    </optgroup>
                </select>
                <label for="new_price">New Price (TK)</label>
                <input type="number" id="new_price" name="new_price" min="0" step="0.01" placeholder="e.g. 750" required>
                <button type="submit" class="addbtn">Set Price</button>
            </form>
        </div>
    </div>
    <div class="menuoverlay" id="credbox" onclick="if (event.target === this) closeboxes()">
        <div class="menubox">
            <button type="button" class="closebtn" onclick="closeboxes()">&#10005;</button>
            <h2>Edit Credentials</h2>
            <?php if (!empty($msg10)) 
                { ?>
                <p class="msgbox"><?php echo e($msg10); ?></p>
            <?php } ?>
            <form action="admin.php" method="POST" autocomplete="off">
                    <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_credentials">
                <label for="new_username">Admin Profile</label>
                <input type="text" id="new_username" name="new_username" maxlength="50" autocomplete="off" required>
                <label for="confirm_username">Confirm Admin Profile</label>
                <input type="text" id="confirm_username" name="confirm_username" maxlength="50" autocomplete="off" required>
                <label for="new_password">Admin Password</label>
                <input type="password" id="new_password" name="new_password" minlength="8" maxlength="72" autocomplete="new-password" required>
                <label for="confirm_password">Confirm Admin Password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required>
                <button type="submit" class="addbtn">Save Credentials</button>
            </form>
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
                    <?php echo csrf_field(); ?>
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
                            <p class="ordernum">Order #<?php echo $order['id']; ?></p>
                            <p><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['cake_details']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                            <p><strong>Total:</strong> <?php echo tk($order['total_price']); ?></p>
                            <div class="orderbtns">
                                <form action="admin.php" method="POST">
                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="accept">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="okbtn">Accept</button>
                                </form>
                                <form action="admin.php" method="POST">
                    <?php echo csrf_field(); ?>
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
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add_rider">
                    <label for="rname">Rider ID Name</label>
                    <input type="text" id="rname" name="rname" placeholder="Choose a rider ID name" maxlength="50" required>
                    <label for="rpass">Rider Password</label>
                    <input type="text" id="rpass" name="rpass" placeholder="Set a password (min 8 characters)" minlength="8" maxlength="72" required>
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
            <div class="adminbox assignbox">
                <h2>Assign Order to Rider</h2>
                <?php if (!empty($msg9)) 
                    { ?>
                    <p class="msgbox"><?php echo htmlspecialchars($msg9); ?></p>
                <?php } ?>
                <?php if (count($riderlist) === 0) 
                    { ?>
                    <p class="formtext">Add a rider first.</p>
                <?php } 
                else if (count($assignable) === 0) 
                    { ?>
                    <p class="formtext">No accepted orders are waiting for a rider.</p>
                <?php } 
                else 
                    { ?>
                    <form action="admin.php" method="POST" class="smallform" id="assignform">
                    <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="assign_rider">
                        <input type="hidden" name="assign_rider_id" id="assign_rider_id" value="">
                        <div class="assignrow">
                            <select id="rider_pick">
                                <option value="">Choose a rider</option>
                                <?php foreach ($riderlist as $r) 
                                    { ?>
                                    <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['id_name']); ?></option>
                                <?php } ?>
                            </select>
                            <button type="button" class="addbtn assignbtn" id="selectbtn" onclick="pickrider()">Select</button>
                        </div>
                        <p class="pickedtxt" id="pickedtxt"></p>
                        <div class="assignrow">
                            <select id="assign_order_id" name="assign_order_id" disabled required>
                                <option value="">Choose an order</option>
                                <?php foreach ($assignable as $as) 
                                    { ?>
                                    <option value="<?php echo $as['id']; ?>">Order #<?php echo $as['id']; ?><?php if ($as['rider_status'] === 'declined') { echo " (declined by " . htmlspecialchars($as['rider_name']) . ")"; } else if ($as['rider_status'] === 'pending') { echo " (waiting for " . htmlspecialchars($as['rider_name']) . ")"; } ?></option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="addbtn assignbtn" id="assignbtn" disabled>Assign</button>
                        </div>
                    </form>
                <?php } ?>
                <div class="itemlist">
                    <?php if ($assigned_res->num_rows === 0) 
                        { ?>
                        <p class="formtext">No assignments yet.</p>
                    <?php } 
                    else 
                    { ?>
                        <?php while ($ar = $assigned_res->fetch_assoc()) 
                            { ?>
                            <div class="itemline">
                                <span>Order #<?php echo $ar['id']; ?> &rarr; <?php echo htmlspecialchars($ar['rider_name']); ?></span>
                                <span class="qtytxt"><?php
                                    if ($ar['status'] === 'delivered') { echo "Delivered"; }
                                    else if ($ar['rider_status'] === 'pending') { echo "Awaiting rider"; }
                                    else if ($ar['rider_status'] === 'accepted') { echo "Accepted"; }
                                    else { echo "Declined"; }
                                ?></span>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        function pickrider()
            {
            var sel = document.getElementById('rider_pick');
            if (sel.value === '') 
                {
                alert('Please choose a rider first.');
                return;
            }
            document.getElementById('assign_rider_id').value = sel.value;
            document.getElementById('pickedtxt').textContent = 'Selected rider: ' + sel.options[sel.selectedIndex].text;
            document.getElementById('assign_order_id').disabled = false;
            document.getElementById('assignbtn').disabled = false;
        }
        var rp = document.getElementById('rider_pick');
        if (rp) 
            {
            rp.addEventListener('change', function () 
                {
                document.getElementById('assign_rider_id').value = '';
                document.getElementById('pickedtxt').textContent = '';
                document.getElementById('assign_order_id').disabled = true;
                document.getElementById('assignbtn').disabled = true;
            });
        }
        function togglemenu()
            {
            document.getElementById('editdrop').classList.toggle('show');
        }
        function closeboxes()
            {
            document.getElementById('addbox').classList.remove('show');
            document.getElementById('removebox').classList.remove('show');
            document.getElementById('addfillingbox').classList.remove('show');
            document.getElementById('removefillingbox').classList.remove('show');
            document.getElementById('editpricebox').classList.remove('show');
            document.getElementById('credbox').classList.remove('show');
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
            else if (which === 'remove') 
                {
                document.getElementById('removebox').classList.add('show');
            } 
            else if (which === 'addfilling') 
                {
                document.getElementById('addfillingbox').classList.add('show');
                document.getElementById('filling_name').focus();
            } 
            else if (which === 'removefilling') 
                {
                document.getElementById('removefillingbox').classList.add('show');
            } 
            else if (which === 'credentials') 
                {
                document.getElementById('credbox').classList.add('show');
                document.getElementById('new_username').focus();
            } 
            else 
                {
                document.getElementById('editpricebox').classList.add('show');
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