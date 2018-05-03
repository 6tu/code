# non-repeat
用 MD5 对比的方法去掉指定目录中重复文件的一个 PHP 脚本

-->特点
1. 用对比 MD5 值的方法去掉本机重复文件
2. 对非重复文件另行复制到指定的文件夹
3. 为了节省时间，大于 100 M 的文件不复制
4. log 保存保存于当前目录的 phplog/ 中
5. 要处理的文件默认目录是 D:\doc ，输出目录在  D:\doc-update
6. 在 PHP CLI 模式和 apache2handler 方式都可以
7. 检查文件无误后自行删除原文件，这一点很重要

-->版本要求
1. 这个脚本运行在PHP5.0及以后的PHP版本中，如果不能很好的显示中文，则更改 php.ini 中的 default_charset 值为 "GB2312"
2. 在PHP5.0中，可能要注释掉 date_default_timezone_set('Asia/Shanghai'); 这一行，PHP5.2以后的版本无需做任何更改
3. 无需扩展模块

-->使用方法
1. CLI 模式运行时，至少需要 php.exe 和 php5ts.dll 这两个文件，PHP7 可能用到 php.ini 设置  default_charset 。
   相关文件从 http://php.net/downloads.php 中提取
   
   执行这样的命令  /path/to/php.exe -f /path/to/non-repeat.php 
   按需要自行设置即可
   
2. apache2handler 方式运行时，需要在本机配置支持 PHP 的 WEB 服务器。修改 non-repeat.php 中 $source_path = 'D:/doc'; 和 $maxlenth = '104857600';    两个参数后，放在WEB服务器的目录中，然后在浏览器中以 HTTP:// 的方式访问这个文件，直到它执行完毕。

-->效率测试
   毕竟它是 PHP 的脚本，运行效率低下，不够灵活，处理 1 万个文件大约 1 分钟。
