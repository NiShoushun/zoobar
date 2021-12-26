<?php
// 数据库连接部分
$DEFAULT_DB_NAME = "myzoo";
$DEFAULT_DB_HOST = "localhost";
$DEFAULT_DB_PASSWD = "913913";
$DEFAULT_DB_USER = "niss";

/**
 * 正常的过滤
*/
//$allowed_tags =
//'<a><br><b><h1><h2><h3><h4><i><img><li><ol><p><strong><table>' .
//'<tr><td><th><u><ul><em><span>';
//$disallowed =
//'javascript:|window|eval|setTimeout|setInterval|target|' .
//'onAbort|onBlur|onChange|onClick|onDblClick|' .
//'onDragDrop|onError|onFocus|onKeyDown|onKeyPress|' .
//'onKeyUp|onLoad|onMouseDown|onMouseMove|onMouseOut|' .
//'onMouseOver|onMouseUp|onMove|onReset|onResize|' .
//'onSelect|onSubmit|onUnload';



///**
// * 不安全的过滤
// */
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
$REPLACE_SPACIAL_CHAR = false;
$ENABLE_HTTP_REFER_CHECK = false;
$ENABLE_TOKEN_CHECK = false;
