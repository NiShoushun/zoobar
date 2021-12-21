<?php
require_once("includes/common.php");
nav_start_outer("Home");
nav_start_inner();
?>
<b>Balance:</b>
<?php
    global $user;
    $sql = "SELECT Zoobars FROM Person WHERE PersonID=$user->id";
    global $db;
    $rs = $db->executeQuery($sql);
    $rs = mysqli_fetch_array($rs);
    $balance = $rs["Zoobars"];
    echo max($balance, 0);
?>
zoobars<br/>
<b>Your profile:</b>
<form method="POST" name=profile-form
      action="<?php echo $_SERVER['PHP_SELF'] ?>">
    <textarea name="profile_update">
            <?php
                if ($_POST['profile_submit']) {  // Check for profile submission
                    $profile = $_POST['profile_update'];
                    $sql = "UPDATE Person SET Profile='$profile' " .
                        "WHERE PersonID=$user->id";
                    $db->executeQuery($sql);  // Overwrite profile in database
                }

                // 获取profile
                $sql = "SELECT Profile FROM Person WHERE PersonID=$user->id";
                $rs = $db->executeQuery($sql);
                $fetched_arr = mysqli_fetch_array($rs);
                echo $fetched_arr["Profile"];
            ?>
    </textarea>
    <br/>
    <input type=submit name="profile_submit" value="Save">
</form>
<?php
nav_end_inner();
nav_end_outer();

?>
