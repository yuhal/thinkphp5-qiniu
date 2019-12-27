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

// 删除指定资源
Route::delete(':version/delete','api/:version.qiniu/delete');

// 查询Apiuser
Route::resource(':version/apiuser','api/:version.apiuser');       //资源路由

// 获取authentication
Route::resource(':version/authentication/index', 'api/:version.Authentication');
// 获取sign
Route::resource(':version/sign/index', 'api/:version.Sign');

// 人像资源路由
Route::resource(':version/face','api/:version.Face')->vars([
	':version/face' => 'id',
	':version/face' => 'group_id'
]);

// 测试资源路由
// Route::resource(':version/test','api/:version.Test')->vars([
// 	':version/test' => 'id',
// 	':version/test' => 'group_id'
// ]);
Route::resource('v1/test','api/v1.Test')->vars(['v1/test' => 'id']);
Route::resource('v2/test','api/v2.Test')->vars(['v2/test' => 'group_id']);

// 图像库资源路由
Route::resource(':version/image','api/:version.Image')->vars([
	':version/image' => 'id',
	':version/image' => 'group_id'
]);

// 获取指定账号下所有的空间名。
Route::get(':version/buckets','api/:version.qiniu/buckets');

// 批量移动或重命名文件
Route::put(':version/buildBatchMove','api/:version.qiniu/buildBatchMove');

// 获取指定空间绑定的所有的域名
Route::get(':version/domains/:bucket','api/:version.qiniu/domains')->pattern(['bucket' => '[\w-]+']);

// 获取指定空间的文件列表
Route::get(':version/listFiles','api/:version.qiniu/listFiles');

//生成access_token
Route::post(':version/token/token','api/:version.token/token');

//刷新access_token  
Route::post(':version/token/refresh','api/:version.token/refresh');  

//添加用户
Route::post(':version/apiuser/save','api/:version.apiuser/save');       

// 上传文件到七牛
Route::post(':version/putFile','api/:version.qiniu/putFile');

// 给资源进行重命名
Route::put(':version/rename','api/:version.qiniu/rename');

// 将资源从一个空间到另一个空间
Route::put(':version/move','api/:version.qiniu/move');

// 所有路由匹配不到情况下触发该路由
Route::miss('\app\api\controller\Exception::miss');




