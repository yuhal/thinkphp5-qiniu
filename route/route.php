<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get(':version/address/:id','api/:version.user/address');  //一般路由规则，

Route::resource(':version/user','api/:version.user');       //资源路由
Route::post(':version/user','api/:version.user/index');
Route::post(':version/token/token','api/:version.token/token');  //生成access_token
Route::post(':version/token/refresh','api/:version.token/refresh');  //刷新access_token
// Route::resource(':version/token','api/:version.token/refresh');




// 查询Apiuser
Route::resource(':version/apiuser','api/:version.apiuser');       //资源路由
// Route::get(':version/apiuser','api/:version.apiuser/index');       //apiuser用户
Route::post(':version/apiuser/save','api/:version.apiuser/save');       //添加用户


// 获取authentication
Route::resource(':version/authentication/index', 'api/:version.Authentication');
// 获取sign
Route::resource(':version/sign/index', 'api/:version.Sign');
// 所有路由匹配不到情况下触发该路由
Route::miss('\app\api\controller\Exception::miss');

// Route::resource(':version/news/data','api/:version.News');
Route::resource(':version/news','api/:version.News');
Route::resource(':version/file','api/:version.File');
Route::resource(':version/bucket','api/:version.Bucket')->pattern(['id' => '[\w-]+']);
Route::resource(':version/face','api/:version.Face');

// 获取指定账号下所有的空间名。
Route::get(':version/buckets','api/:version.qiniu/buckets');

// 批量移动或重命名文件
Route::put(':version/buildBatchMove','api/:version.qiniu/buildBatchMove');

// 获取指定空间绑定的所有的域名
Route::get(':version/domains/:bucket','api/:version.qiniu/domains')->pattern(['bucket' => '[\w-]+']);

// 删除指定资源
Route::delete(':version/delete','api/:version.qiniu/delete');

// 上传文件到七牛
Route::post(':version/putFile','api/:version.qiniu/putFile');

// 给资源进行重命名
Route::put(':version/rename','api/:version.qiniu/rename');

// 测试资源路由
Route::resource(':version/test','api/:version.Test')->vars([
	':version/test' => 'id',
	':version/test' => 'group_id'
]);

// 获取指定空间的文件列表
Route::get(':version/listFiles','api/:version.qiniu/listFiles');

// 将资源从一个空间到另一个空间
Route::put(':version/move','api/:version.qiniu/move');

// 图像库资源路由
Route::resource(':version/image','api/:version.Image')->vars([
	':version/image' => 'id',
	':version/image' => 'group_id'
]);




