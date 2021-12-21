<?php
require_once "includes/userAuth.php";

// login.php: Functions for checking auth and displaying login page

// Return true on registration success, otherwise set $login_error
function validateRegistration($user): bool
{
    global $login_error;
    $success = false;
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];
    if (!$username) {
        $login_error = "You must supply a username to register.";
    } else if (!$password) {
        $login_error = "You must supply a password to register.";
    } else if (!$user->addRegistration($username, $password)) {
        // 注册失败（查询到一个重名用户 或 sql执行失败）
        $login_error = "Registration failed.";
    } else {
        $success = true;
    }
    return $success;
}

// Return true on login success, otherwise set $login_error
function validateLogin($user): bool
{
    global $login_error;
    $success = false;
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];
    if (!$username) {
        $login_error = "You must supply a username to logs in.";
    } else if (!$password) {
        $login_error = "You must supply a password to logs in.";
    } else if (!$user->checkLogin($username, $password)) {
        $login_error = "Invalid username or password.";
    } else {
        $success = true;
    }

    return $success;
}

// Return true if the user is valid, otherwise return false
function validateUser($user): bool
{
    if (isset($_POST['submit_registration']) &&
        validateRegistration($user)) {
        return true;  // Successful registration
    } else if ((isset($_POST['submit_login']) && validateLogin($user)) or $user->id > 0) { // 第一次登陆 or 已经登陆过了
        return true;  // Successful login
    } else {
        return false;  // Request credentials
    }
}



// 展示 login 与 registration 界面
function display_login()
{
    nav_start_outer("Login");
    ?>
    <!--        用户登陆界面-->
    <div id="login" class="centerpiece">
        <form name=loginform method=POST action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <table>
                <tr>
                    <td>Username:</td>
                    <td>
                        <input type=text name=login_username size=30 autocomplete=no value=<?php
                            echo htmlspecialchars($_POST['login_username']); ?>>
                    </td>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td colspan=2>
                        <input type=password name=login_password size=30 autocomplete=no>
                        <input type=submit name=submit_login value="Log in">
                        <input type=submit name=submit_registration value="Register">
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div class="footer warning">
        <?php global $login_error;
        echo $login_error; ?>
    </div>
    <script>
        document.loginform.login_username.focus();
    </script>
    <?php
    nav_end_outer();
} ?>
