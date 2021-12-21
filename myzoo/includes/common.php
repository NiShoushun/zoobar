<?php
error_reporting(E_ALL & ~E_NOTICE);
set_include_path("../includes;..;.;");

require_once "database.class.php";
require_once "login.php";
require_once "userAuth.php";
require_once "navigation.php";

// Allow users to use the back button without reposting data
header("Cache-Control: private");

// 数据库连接部分
static $DEFAULT_DB_NAME = "myzoo";
static $DEFAULT_DB_HOST = "localhost";
static $DEFAULT_DB_PASSWD = "913913";
static $DEFAULT_DB_USER = "niss";

// 全局数据库连接
$db = new Database($DEFAULT_DB_HOST
    ,$DEFAULT_DB_USER
    ,$DEFAULT_DB_PASSWD
    ,$DEFAULT_DB_NAME
);
// 全局用户
$user = new UserAuth($db);

/**
 * 正常的过滤

    $allowed_tags =
        '<a><br><b><h1><h2><h3><h4><i><img><li><ol><p><strong><table>' .
        '<tr><td><th><u><ul><em><span>';
    $disallowed =
        'javascript:|window|eval|setTimeout|setInterval|target|' .
        'onAbort|onBlur|onChange|onClick|onDblClick|' .
        'onDragDrop|onError|onFocus|onKeyDown|onKeyPress|' .
        'onKeyUp|onLoad|onMouseDown|onMouseMove|onMouseOut|' .
        'onMouseOver|onMouseUp|onMove|onReset|onResize|' .
        'onSelect|onSubmit|onUnload';
*/


/**
 * 不安全的过滤
 *
 */
$allowed_tags =
    '<script><a><br><b><h1><h2><h3><h4>'.
    '<i><img><li><ol><p><strong><table>' .
    '<tr><td><th><u><ul><em><span>';

$disallowed =
    'eval|setTimeout|setInterval|target|'.
    'onAbort|onBlur|onChange|onClick|onDblClick|'.
    'onDragDrop|onFocus|onKeyDown|onKeyPress|'.
    'onKeyUp|onLoad|onMouseDown|onMouseMove|onMouseOut|'.
    'onMouseOver|onMouseUp|onMove|onReset|onResize|'.
    'onSelect|onSubmit|onUnload';
/**
 *
 */


$ENABLE_HTTP_REFER_CHECK = false;
$ENABLE_TOKEN_CHECK = false;






// Check for logout and maybe display login page
if ($_GET['action'] == 'logout') {
    $user->logout();
    display_login();
    exit();
}

// Validate user and maybe display login page
if (!validateUser($user)) {
    display_login();
    exit();
}


