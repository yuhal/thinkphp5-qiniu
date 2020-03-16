#!/bin/sh
PATH=/usr/local/php/bin:/opt/someApp/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin # 将php路径加入都临时变量中

cd /home/wwwroot/apl  # 进入项目的根目录下，保证可以运行php think的命令
php think Bbm # 执行在Bbm.php设定的名称

cd /home/wwwroot/upload  # 进入文件上传目录
rm -rf * # 清空上传的临时文件