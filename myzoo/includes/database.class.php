<?php

class Database
{

    // mysql连接
    public mysqli $connection;
    // 上次查询结果
    public mysqli_result|bool|null $queryResult;

    private string $dbHost;
    private string $dbUser;
    private string $dbPasswd;
    private string $dbName;


    public function __construct($dbHost,$dbUser,$dbPasswd,$dbName)
    {
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPasswd = $dbPasswd;
        $this->dbName = $dbName;
        $this->connection = new mysqli($this->dbHost, $this->dbUser, $this->dbPasswd, $this->dbName);

        // 如果连接失败
        if (mysqli_connect_error()) {
            $this->oops("Could not connect to server: <b>$this->dbHost</b>.");
        }
    }

    // 执行sql
    function executeQuery($sql): mysqli_result|bool|int
    {

        $this->queryResult = $this->connection->query($sql);

        if (!$this->queryResult) {
            $this->oops("<b>MySQL Query fail:</b> $sql");
            return 0;
        }

        return $this->queryResult;
    }

    // 如果发生数据库查询错误，在页面中打印具体信息表格
    function oops($msg = '')
    {
        $this->error = $this->connection->error;
        $this->errno = $this->connection->errno;

        ?>
        <table  style="background:white;color:black;width:80%;">
            <tr>
                <th colspan=2>Database Error</th>
            </tr>
            <tr>
                <td  >Message:</td>
                <td><?php echo $msg; ?></td>
            </tr>
            <?php if (!empty($error)) echo '<tr><td  nowrap>MySQL Error:</td><td>' . $error . '</td></tr>'; ?>
            <tr>
                <td align="right">Date:</td>
                <td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td>
            </tr>
            <?php if (!empty($_SERVER['REQUEST_URI'])) echo '<tr><td >Script:</td><td><a href="' . $_SERVER['REQUEST_URI'] . '">' . $_SERVER['REQUEST_URI'] . '</a></td></tr>'; ?>
            <?php if (!empty($_SERVER['HTTP_REFERER'])) echo '<tr><td >Referer:</td><td><a href="' . $_SERVER['HTTP_REFERER'] . '">' . $_SERVER['HTTP_REFERER'] . '</a></td></tr>'; ?>
        </table>
        <?php
    }
}


