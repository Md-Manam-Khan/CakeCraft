<?php
require "config.php";
if (!isset($_SESSION['user_id'])) 
    {
    header("Location: signin.php");
    exit;
}
$uname = $_SESSION['user_name'];
require "search.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CakeCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashpage">
    <form class="searchwrap" id="foodsearch" action="dashboard.php" method="get" autocomplete="off" role="search">
        <input type="search" id="foodq" name="q" value="<?php echo e($searchQ); ?>" placeholder="Search for food" maxlength="100" aria-label="Search for food">
        <?php if ($searchMsg !== '') { ?>
        <div class="searchresult show <?php echo $searchClass; ?>" id="searchresult" role="status" aria-live="polite"><?php echo e($searchMsg); ?></div>
        <?php } ?>
    </form>
    <a href="logout.php" class="devbtn logoutbtn">Logout</a>
    <div class="dashwrap">
        <h1 class="dashtitle">Welcome, <?php echo htmlspecialchars($uname); ?>!</h1>
        <p class="dashsub">What would you like to do today?</p>
        <div class="boxrow">
            <a href="customize.php" class="box2">
                <h3>Customize Your Cake</h3>
                <p>Design your dream cake — flavor, size, message and more — and place your order.</p>
            </a>
            <a href="orders.php" class="box2">
                <h3>Track My Orders</h3>
                <p>See the status of your current and past cake orders in one place.</p>
            </a>
        </div>
    </div>
</body>
</html>
