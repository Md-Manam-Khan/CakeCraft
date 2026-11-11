<?php
// Food search logic. Works in two ways:
//  1) Included by dashboard.php (after config.php): sets $searchQ, $searchMsg, $searchClass.
//  2) Called directly by index.html as search.php?q=... : answers with JSON.
$searchDirect = (basename($_SERVER['SCRIPT_FILENAME']) === 'search.php');
if ($searchDirect) 
    {
    require "config.php";
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
}
$searchQ = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$searchQ = mb_substr($searchQ, 0, 100);
$searchMsg = '';
$searchClass = '';
if ($searchQ !== '') 
    {
    // Split the search into words; ignore filler words such as "cake"
    $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($searchQ), -1, PREG_SPLIT_NO_EMPTY);
    $skip = array('cake', 'cakes', 'a', 'an', 'the', 'of', 'and', 'with');
    $terms = array();
    foreach ($words as $w) 
        {
        if (!in_array($w, $skip) && count($terms) < 5) 
            {
            $terms[] = $w;
        }
    }
    // Only cakes that are on the menu with a real price count as available
    $sql = "SELECT id FROM cakes WHERE price > 0";
    $types = '';
    $params = array();
    foreach ($terms as $t) 
        {
        $sql .= " AND cake_name LIKE ?";
        $params[] = '%' . addcslashes($t, '%_\\') . '%';
        $types .= 's';
    }
    $sql .= " LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($types !== '') 
        {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $stmt->store_result();
    $available = ($stmt->num_rows > 0);
    $stmt->close();
    if ($available) 
        {
        $searchMsg = 'Available';
        $searchClass = 'ok';
    } 
    else 
        {
        $searchMsg = 'Not available';
        $searchClass = 'no';
    }
}
if ($searchDirect) 
    {
    echo json_encode(array('available' => ($searchMsg === 'Available')));
    $conn->close();
}
?>
