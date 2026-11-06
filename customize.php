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
    csrf_check();
    $flavor = trim(isset($_POST['flavor']) ? $_POST['flavor'] : '');
    $filling = trim(isset($_POST['filling']) ? $_POST['filling'] : '');
    $size = trim(isset($_POST['size']) ? $_POST['size'] : '');
    $address = trim(isset($_POST['address']) ? $_POST['address'] : '');
    $note = trim(isset($_POST['note']) ? $_POST['note'] : '');
    if ($flavor === '' || $filling === '' || $size === '' || $address === '') 
        {
        $msg = "Flavor, filling, size and address are required.";
    } 
    else if (mb_strlen($address) < 5 || mb_strlen($address) > 500) 
        {
        $msg = "Please enter a full delivery address (5 to 500 characters).";
    } 
    else if (mb_strlen($note) > 500) 
        {
        $msg = "Special instructions can be at most 500 characters.";
    } 
    else 
        {
        $p1 = $conn->prepare("SELECT price FROM cakes WHERE cake_name = ? AND price > 0");
        $p1->bind_param("s", $flavor);
        $p1->execute();
        $r1 = $p1->get_result();
        $p1->close();
        $p2 = $conn->prepare("SELECT price FROM fillings WHERE filling_name = ? AND price > 0");
        $p2->bind_param("s", $filling);
        $p2->execute();
        $r2 = $p2->get_result();
        $p2->close();
        $p3 = $conn->prepare("SELECT price FROM sizes WHERE size_name = ? AND price > 0");
        $p3->bind_param("s", $size);
        $p3->execute();
        $r3 = $p3->get_result();
        $p3->close();
        if ($r1->num_rows === 0 || $r2->num_rows === 0 || $r3->num_rows === 0) 
            {
            $msg = "Please choose a flavor, filling and size from the lists.";
        } 
        else 
            {
            $row1 = $r1->fetch_assoc();
            $row2 = $r2->fetch_assoc();
            $row3 = $r3->fetch_assoc();
            $total = $row1['price'] + $row2['price'] + $row3['price'];
            if (empty($note)) 
                {
                $notetxt = "None";
            } 
            else 
                {
                $notetxt = $note;
            }
            $details = "Flavor: $flavor | Filling: $filling | Size: $size | Note: " . $notetxt;
            $stmt = $conn->prepare("INSERT INTO orders (user_id, cake_details, address, total_price, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issd", $_SESSION['user_id'], $details, $address, $total);
            if ($stmt->execute()) 
                {
                $msg = "Order placed successfully! Total: " . tk($total) . ". Track it under 'My Orders'.";
            } 
            else 
                {
                error_log("CakeCraft order error: " . $stmt->error);
                $msg = "Sorry, we could not place your order. Please try again.";
            }
            $stmt->close();
        }
    }
}
$menu_res = $conn->query("SELECT cake_name, price FROM cakes WHERE price > 0 ORDER BY cake_name ASC");
$menulist = array();
while ($m = $menu_res->fetch_assoc()) 
    {
    $menulist[] = $m;
}
$fillings_res = $conn->query("SELECT filling_name, price FROM fillings WHERE price > 0 ORDER BY filling_name ASC");
$fillinglist = array();
while ($f = $fillings_res->fetch_assoc()) 
    {
    $fillinglist[] = $f;
}
$sizes_res = $conn->query("SELECT size_name, price FROM sizes WHERE price > 0 ORDER BY id ASC");
$sizelist = array();
while ($z = $sizes_res->fetch_assoc()) 
    {
    $sizelist[] = $z;
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
        <div class="customrow">
        <div class="form">
            <h2>Customize Your Cake</h2>
            <p class="formtext">Tell us what you'd like and we'll get baking!</p>
            <?php if (!empty($msg)) { ?>
                <p class="msgbox"><?php echo htmlspecialchars($msg); ?></p>
            <?php } ?>
            <form action="customize.php" method="POST">
                    <?php echo csrf_field(); ?>
                <label for="flavor">Cake Flavor</label>
                <select id="flavor" name="flavor" required>
                    <?php if (count($menulist) === 0) 
                        { ?>
                        <option value="">No cakes available right now</option>
                    <?php } 
                    else 
                    { ?>
                        <option value="">Choose a cake</option>
                        <?php foreach ($menulist as $m) 
                            { ?>
                            <option value="<?php echo htmlspecialchars($m['cake_name']); ?>" data-price="<?php echo $m['price']; ?>"><?php echo htmlspecialchars($m['cake_name']); ?> — <?php echo tk($m['price']); ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <label for="filling">Cake Filling</label>
                <select id="filling" name="filling" required>
                    <?php if (count($fillinglist) === 0) 
                        { ?>
                        <option value="">No fillings available right now</option>
                    <?php } 
                    else 
                    { ?>
                        <option value="">Choose a filling</option>
                        <?php foreach ($fillinglist as $f) 
                            { ?>
                            <option value="<?php echo htmlspecialchars($f['filling_name']); ?>" data-price="<?php echo $f['price']; ?>"><?php echo htmlspecialchars($f['filling_name']); ?> — <?php echo tk($f['price']); ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <label for="size">Cake Size</label>
                <select id="size" name="size" required>
                    <?php if (count($sizelist) === 0) 
                        { ?>
                        <option value="">No sizes available right now</option>
                    <?php } 
                    else 
                    { ?>
                        <option value="">Choose a size</option>
                        <?php foreach ($sizelist as $z) 
                            { ?>
                            <option value="<?php echo htmlspecialchars($z['size_name']); ?>" data-price="<?php echo $z['price']; ?>"><?php echo htmlspecialchars($z['size_name']); ?> — <?php echo tk($z['price']); ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <label for="address">Delivery Address</label>
                <textarea id="address" name="address" placeholder="Enter your full delivery address" rows="3" maxlength="500" required></textarea>
                <label for="note">Special Instructions</label>
                <textarea id="note" name="note" placeholder="Any message on the cake, allergies, etc. (optional)" rows="4" maxlength="500"></textarea>
                <div class="totalbox">
                    <p class="totalfinal">Total: <span id="ptotal"></span></p>
                </div>
                <button type="submit">Place Order</button>
            </form>
            <a href="dashboard.php" class="backlink">← Back to Dashboard</a>
        </div>
        </div>
    </div>
    <script>
        function money(n)
            {
            var s = n.toFixed(2);
            if (s.slice(-3) === '.00') 
                {
                s = s.slice(0, -3);
            }
            return s + ' TK';
        }
        function getprice(id)
            {
            var sel = document.getElementById(id);
            var opt = sel.options[sel.selectedIndex];
            return parseFloat(opt.getAttribute('data-price')) || 0;
        }
        function recalc()
            {
            var a = getprice('flavor');
            var b = getprice('filling');
            var c = getprice('size');
            document.getElementById('ptotal').textContent = money(a + b + c);
        }
        document.getElementById('flavor').addEventListener('change', recalc);
        document.getElementById('filling').addEventListener('change', recalc);
        document.getElementById('size').addEventListener('change', recalc);
        recalc();
    </script>
</body>
</html>