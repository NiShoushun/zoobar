# web 安全实践练习项目

web 安全实践实验项目（版本老的都包浆了），对其做了一些修改。

### 修改
#### 代码
* 支持php7.X 与 8.0.13；
* 修改部分代码格式；
* 对部分安全性内容进行修改；

### 运行

#### myzoo 主服务配置

##### php配置

修改`/etc/php.ini`文件：

* 添加 mysqli扩展。

  > 添加`extension mysqli`

##### httpd/apache服务器配置

以httpd服务器为例子（较新版本的apache服务器）。

> ```bash
> ➜  ~ httpd -v       
> Server version: Apache/2.4.51 (Unix)
> Server built:   Nov 13 2021 20:10:37
> ```

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

新建配置文件：

```htaccess
LoadModule php_module /usr/lib/httpd/modules/libphp.so

<IfModule ssl_module>

		<Directory /files>
			Options Indexes FollowSymLinks
		</Directory>
    
    AddType application/x-httpd-php .php

	<VirtualHost niss.com:443>
		ServerAdmin nishoushun@ustc.edu
		ServerName niss.com	

		DocumentRoot /public/www/myzoo

                <IfModule php_module>
                  DirectoryIndex index.html index.htm index.php
                  PHPIniDir /etc/php/php.ini
                </IfModule>

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

	<VirtualHost nissattack.com:80>
		ServerAdmin nishoushun@ustc.edu
		ServerName nissattack.com	

                DirectoryIndex index.html index.htm index.php

		DocumentRoot /public/www/attack

		ErrorLog  /public/www/attack/logs/error.log
		CustomLog /public/www/attack/logs/access.log combined
	</VirtualHost>
```

将以上配置文件的各项进行相应修改，同时将该配置文件通过`include`指令添加进`/etc/httpd/conf/httpd.conf`中。

> 注意：php8可能不自带`libphp.so`，需要自行安装，并导入该模块。

##### 数据库配置

1. 配置 mysql 或 mariaDB 数据库。
2. 执行`\sql\create.sql`。

#### 项目配置

配置项位于：`includes\config.php`，此文件包含本项目用到的全局变量。

##### 数据库连接部分：

修改数据库配置静态变量

```php
static $DEFAULT_DB_NAME = "your database name";
static $DEFAULT_DB_HOST = "your database server host";
static $DEFAULT_DB_PASSWD = "your database user password";
static $DEFAULT_DB_USER = "database user name";
```

##### 修改tag以及其他不安全字符过滤规则：

修改`includes\config.php`中的 `$allowd_tags` 以及 `$disallowed`，其中：

* `$allowd_tags`： 允许出现在profile中的tag；
* `$disallowd`：不允许出现的字符，会被替换为空格字符`" "`；

##### 开启Transfer安全检查：

修改：

```php
$ENABLE_HTTP_REFER_CHECK = true;
$ENABLE_TOKEN_CHECK = true;
```

* `$ENABLE_HTTP_REFERER_CHECK `：若为true，则检查请求来源是否为`/transfer.php`，不通过验证则php终止服务；
* `$ENABLE_TOKEN_CHECK `：若为true，用户访问transfer.php时进行token更新，并于用户提交的token相比较（token被写入到用户表单并不可见），不通过验证则php终止服务；

### attack服务

用于攻击演示，包含：

* `csrf.html`：包含一个`tansfer.php`的`iframe`，一个向该服务自动提交的表单。

* `xssCookieGetter.html`：用于存储至zoobar用户的profile中，解析为js后，自动向attack服务的`cooker.php`发送一条请求，包含用户的cookie。

* `cooker.php`：接受请求并打印cookie（实际上没用，要获取cookie，只需要查看配置问attack访问日志就可以）：

  ![image-20211221210534047](https://ni187note-pics.oss-cn-hangzhou.aliyuncs.com/notes-img/image-20211221210534047.png)

* `xssworm.html`：用于存储至zoobar用户的profile中，解析为js后，自动提交转交请求，并提交一个将用户profile 更新为本脚本内容的请求。

* `hijack.html`：貌似和csrf一样？要多点一下

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
