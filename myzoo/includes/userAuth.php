<?php

// Cookie-based authentication logic

class UserAuth
{
    var Database|null $db = null;

    // 该次连接的用户id
    var int $id = 0;

    var string|null $username=null;

    // cookie name
    var string $cookieName = "ZoobarLogin";

    public function __construct($db)
    {
        $this->db = $db;
        // 在用户的cookie中设置为 array(username, token)
        if (isset($_COOKIE[$this->cookieName])) {
            $this->checkRemembered($_COOKIE[$this->cookieName]);
        }
    }

    // 校验登陆
    function checkLogin($username, $password): bool
    {
        $sql = "SELECT Salt FROM Person WHERE Username = '$username'";
        $rs = $this->db->executeQuery($sql);
        $rs = mysqli_fetch_array($rs);

        // 检验密码，通过salt与用户密码生成摘要并查询该用户
        $salt = $rs["Salt"];

        $hashedPassword = md5($password . $salt);
        $sql = "select * from Person where Username = '$username' and Password = '$hashedPassword'";

        $resultSet = $this->db->executeQuery($sql);
        $result = mysqli_fetch_array($resultSet);
        if ($result) {
            $this->setCookie($result);
            return true;
        } else {
            return false;
        }
    }

    // 添加一个注册
    function addRegistration($username, $password): bool
    {
        $sql = "SELECT PersonID FROM Person WHERE Username='$username'";
        $qrs = $this->db->executeQuery($sql);
        $rs = mysqli_fetch_array($qrs);
        // 如果查询到了，直接返回false
        if($rs){
            return false;
        }

        $salt = substr(md5(rand()), 0, 4);
        $hashedPassword = md5($password . $salt);
        $sql = "INSERT INTO Person (Username, Password, Salt) " .
            "VALUES ('$username', '$hashedPassword', '$salt')";
        $this->db->executeQuery($sql);
        return $this->checkLogin($username, $password);


    }

    // 登出
    function logout()
    {
        if (isset($_COOKIE[$this->cookieName])) {
            // 用户登出后将cookie设为空字符串
            setcookie($this->cookieName,"");
        }

        session_start();
        // 清空user token
        session_destroy();
        // 清空当前连接user信息
        $this->id = 0;
        $this->username = null;
    }

    function setCookie($values)
    {
        $this->id = $values["PersonID"];
        $this->username = $values["Username"];
        $token = md5($values["Password"] . mt_rand());

        $this->updateToken($token);
        // FIXME 这句有用？ 初始化 session ?
        $session = session_id();
        $sql = "UPDATE Person SET Token = '$token' " .
            "WHERE PersonID = $this->id";
        $this->db->executeQuery($sql);
    }

    // 将 username 与 token 保存至array，并序列化，将内容保存至cookie中
    function updateToken($token)
    {
        $arr = array($this->username, $token);
        $cookieData = base64_encode(serialize($arr));
        // cookie 到期时间点： time() + 31104000
        setcookie($this->cookieName, $cookieData, time() + 31104000);
    }

    // 检查用户提交的cookie是否有效，若有效则设置 id 与 username
    function checkRemembered($cookie)
    {
        // 反序列化 cookie 为 array
        $arr = unserialize(base64_decode($cookie));

        // 从反序列化得到的 array 中获取 username 与 token
        list($username, $token) = $arr;
        if (!$username or !$token) {
            return;
        }

        // 用户cookie 通过校验后，根据 username 与 token查询用户信息 PersonID 与 Username
        $sql = "SELECT * FROM Person WHERE " .
            "(Username = '$username') AND (Token = '$token')";
        $resultSet = $this->db->executeQuery($sql);
        $rs = mysqli_fetch_array($resultSet);
        if ($rs) {
            $this->id = $rs["PersonID"];
            $this->username = $rs["Username"];
        }
    }
}

