<?php
// 获取请求的cookie
$cookie = $_GET['cookie'];
if(isset($cookie)){
    echo 'get cookie: '.$cookie;
}else{
    echo 'not get cookie';
}
