<?php
require_once "includes/common.php";
nav_start_outer("Users");
nav_start_inner();
session_start()
?>

<form name="profile-form" method="GET"
      action="users.php">
    <nobr>User:
        <input type="text" name="user" value="<?php
            // Beware: Stripping slashes is equivalent
            // to running PHP with magic_quotes_gpc off.
            echo stripslashes($_GET['user']);
            ?>" size=10>
        <input type="submit" value="View"></nobr>
</form>


<div id="profile-header"><!-- user data appears here --></div>
<?php
// 查询特定用户的信息
$selectedUser = $_GET['user'];
$sql = "SELECT Profile, Username, Zoobars FROM Person " .
    "WHERE Username='$selectedUser'";
global $db;
$rs = $db->executeQuery($sql);
$rs = mysqli_fetch_array($rs);
$zoobars = 0;

if ($rs) {
    $profile = $rs["Profile"];
    $username = $rs["Username"];
    $zoobars = $rs["Zoobars"];

    $zoobars = ($zoobars > 0) ? $zoobars : 0;
    echo "<span id='zoobars'>"."zoobars: ".$zoobars."</span>";
    echo "<div class=profile-container><b>Profile</b>";
    global $disallowed;
    global $allowed_tags;

    // 仅允许列出的html tag
    $profile = strip_tags($profile, $allowed_tags);
    // 对出现的事件、函数名等进行替换，替换为空格
    $profile = preg_replace("/$disallowed/i", " ", $profile);
    global $REPLACE_SPACIAL_CHAR;

    // 转义特殊字符
    if ($REPLACE_SPACIAL_CHAR){
        $profile = htmlentities($profile);
    }

    echo "<p id=profile>$profile</p></div>";
} else if ($selectedUser) {  // user parameter present but user not found
    echo '<p class="warning" id="bad-user">Cannot find that user.</p>';
}
?>

<script type="text/javascript">
    const total = eval(document.getElementById('zoobars').className);

    function showZoobars(zoobars) {
        document.getElementById("profile-header").innerHTML =
            "<?php echo $selectedUser ?>'s zoobars:" + zoobars;
        if (zoobars < total) {
            setTimeout("showZoobars(" + (zoobars + 1) + ")", 100);
        }
    }

    if (total > 0) showZoobars(0);  // count up to total
</script>

<?php
nav_end_inner();
nav_end_outer();
?>
