# TP5-qiniu

# 感谢

- [thinkphp5-restfulapi](https://github.com/Leslin/thinkphp5-restfulapi "thinkphp5-restfulapi")

# 简介

> 基于 Thinkphp5-restfulapi 实现的七牛云存储接口。

# 数据库导入

> 数据库文件在项目根目录，名称为 api.sql，使用 phpMyadmin 或其他工具进行导入。

# 数据库配置

```
//数据库类型
'type'=>'mysql',
//服务器地址
'hostname'=>'',
//数据库名
'database'=>'',
//用户名
'username'=>'',
//密码
'password'=>'',
//端口
'hostport'=>'3306',
//连接dsn
'dsn'=>'mysql:dbname=;host=;port=3306',
```

> 修改 config/database.php，进行数据库配置。

# 七牛云配置

```
//应用名称
'accessKey'=>'',
//应用地址
'secretKey'=>'',
//七牛账号
'shared'=>'',
//默认仓库
'bucket'=>'',
//人脸图像库
'faceGroup'=>'',
//允许批量重命名的文件后缀
'mimeType'=>'image/jpeg',
//上传目录
'updir'=>'upload/',
```

> 修改 config/qiniu.php，进行七牛云配置。


# 启动

- 下载

```
$ git clone https://github.com/yuhal/thinkphp5-qiniu.git
```

- 进入项目

```
$ cd thinkphp5-qiniu
```

- 安装依赖包

```
$ composer install
```

# API文档 

[TP5-qiniu-document](https://www.showdoc.com.cn/471949144593097?page_id=2758354378180236 "TP5-qiniu-document")

# License 

[MIT](https://github.com/yuhal/ppt-convert/blob/master/LICENSE "MIT")