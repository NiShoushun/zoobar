<?php

require_once("includes/common.php");
require_once("includes/database.class.php");

nav_start_outer("Transfer");
nav_start_inner();


// 转移结果提示信息
$result = "nothing to do";

global $db;
global $user;
global $ENABLE_HTTP_REFER_CHECK;
global $ENABLE_TOKEN_CHECK;


function setToken(){
    /**
     *  开启一个 session 并添加一个随机 token
     * session 并不会在不同的php页面中共享，需要 session_start() 检索已有的session
     * Fixme 只能在第一次登陆验证时，才会设置 session
     */

    if (!$_SESSION["user_token"] or $_SESSION["user_token"]==""){
        $_SESSION["user_token"] = md5(uniqid(mt_rand(), true));
    }
}

session_start();
setToken();

/**
 * @return void 检查请求来源是否为transfer.php
 */
function checkHttpReferer(){
    $request_from = "https://niss.com/transfer.php";
    $fromPage = $_SERVER['HTTP_REFERER'];
    if($fromPage == $request_from ){
        return;
    }
    $result = "Transfer failed: post from wrong page.";
    echo $result."<br>";
    exit(1);
}


/** token 校验
 *  如果用户提交的 token 与 session 中保存的token不一致，则直接 return
 */

function checkToken(){
    $result = "Transfer failed: wrong user token.";
    $userToken = $_SESSION["user_token"];

    if ($_POST['user_token'] != $userToken) {
        echo $result."<br>";
        echo "   "."<br>";
        exit(1);
    }
}


// 处理submission post提交表单
if ($_POST['submission']) {

    if($ENABLE_HTTP_REFER_CHECK){
        checkHttpReferer();
    }
    if($ENABLE_TOKEN_CHECK){
        checkToken();
    }

    // 通过校验后
    $recipient = $_POST['recipient'];
    $zoobars = (int)$_POST['zoobars'];
    $sql = "SELECT Zoobars FROM Person WHERE PersonID=$user->id";
    $rs = $db->executeQuery($sql);
    $rs = mysqli_fetch_array($rs);
    $sender_balance = $rs["Zoobars"] - $zoobars;
    $sql = "SELECT PersonID FROM Person WHERE Username='$recipient'";
    $rs = $db->executeQuery($sql);
    $rs = mysqli_fetch_array($rs);
    $recipient_exists = $rs["PersonID"];
    if ($zoobars > 0 && $sender_balance >= 0 && $recipient_exists) {
        $sql = "UPDATE Person SET Zoobars = $sender_balance " .
            "WHERE PersonID=$user->id";
        $db->executeQuery($sql);
        $sql = "SELECT Zoobars FROM Person WHERE Username='$recipient'";
        $rs = $db->executeQuery($sql);
        if (!$rs) {
            $result = "user query error!";
            return;
        }
        $rsArr = mysqli_fetch_array($rs);
        $recipient_balance = $rsArr["Zoobars"] + $zoobars;
        $sql = "UPDATE Person SET Zoobars = $recipient_balance " .
            "WHERE Username='$recipient'";
        $db->executeQuery($sql);
        $result = $_POST["user_token"];
    } else {
        $result = "Transfer to failed.";
    }
}
?>

<p>
    <b>Balance:</b>
    <span id="myZoobars">  <?php
        $sql = "SELECT Zoobars FROM Person WHERE PersonID=$user->id";
        $rs = $db->executeQuery($sql);
        $rsArr = mysqli_fetch_array($rs);
        $balance = $rsArr["Zoobars"];
        echo max($balance, 0);
        ?>
    </span> zoobars
</p>

<!--    zoobar 转移提交表单-->
<form method=POST name=transfer-form
      action="<?php echo $_SERVER['PHP_SELF'] ?>">

    <!--    zoobar 数量-->
    <p>Send
        <input name=zoobars type=text value="<?php
        echo $_POST['zoobars'];
        ?>" size=5>zoobars
    </p>

    <!--  默认包含 user_token，且不显示，token从该用户本次连接session中获取-->
    <p>
        <input style="display:none" name="user_token" value="<?php echo $_SESSION['user_token']; ?>">
    </p>

    <!--    接收者-->
    <p>to
        <input name=recipient type=text value="<?php echo $_POST['recipient']; ?>">
    </p>

    <input type=submit name=submission value="Send">
</form>
<!--<span class=warning>--><?php //echo "from:".$_SERVER["HTTP_REFERER"]; ?><!--</span><br/>-->
<!--<span class=warning>--><?php //echo $_SESSION["user_token"]; ?><!--</span><br/>-->
<!--<span class=warning>--><?php //echo "your token:".$userToken; ?><!--</span><br/>-->
<!--<span class=warning>--><?php //echo $result; ?><!--</span><br/>-->
<?php
nav_end_inner();
?>
<script type="text/javascript" src="zoobars.js.php"></script>
<?php
nav_end_outer();
?>
