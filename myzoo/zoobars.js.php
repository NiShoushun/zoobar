<?php


require_once("login.php");
require_once("includes/userAuth.php");
require_once("includes/navigation.php");
require_once("includes/database.class.php");
require_once("includes/common.php");

// Init global variables
$db = new Database();
$db->connection = new mysqli('localhost', 'niss', '913913', 'myzoo');
$user = new UserAuth($db);


if(validateUser($user)) {
?>

var myZoobars = <?php
    $sql = "SELECT Zoobars FROM Person WHERE PersonID=$user->id";
    $rs = $db->executeQuery($sql);
    // 获取zoobars值
    $balance = $rs->fetch_array()["Zoobars"];
    echo max($balance, 0);
    ?>;
var div = document.getElementById("myZoobars");

if (div != null) {
    div.innerHTML = myZoobars;
}
<?php
}
?>
