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
Route::delete('v1/delete','api/v1.qiniu/delete');

// 获取authentication
Route::resource('oauth/authentication/index', 'api/oauth.Authentication');

// 获取sign
Route::resource('oauth/sign/index', 'api/oauth.Sign');

// 生成access_token
Route::post('oauth/token/token','api/oauth.token/token');

// 刷新access_token  
Route::post('oauth/token/refresh','api/oauth.token/refresh');  

// 获取指定账号下所有的空间名。
Route::get('v1/buckets','api/v1.qiniu/buckets');

// 批量移动或重命名文件
Route::put('v1/buildBatchMove','api/v1.qiniu/buildBatchMove');

// 获取指定空间绑定的所有的域名
Route::get('v1/domains/:bucket','api/v1.qiniu/domains')->pattern([
	'bucket' => '[\w-]+'
]);

// 获取指定空间的文件列表
Route::get('v1/listFiles','api/v1.qiniu/listFiles');

// 添加用户
Route::post('v1/apiuser/save','api/v1.apiuser/save');       

// 上传文件到七牛
Route::post('v1/putFile','api/v1.qiniu/putFile');

// 给资源进行重命名
Route::put('v1/rename','api/v1.qiniu/rename');

// 将资源从一个空间到另一个空间
Route::put('v1/move','api/v1.qiniu/move');

// 人脸搜索
Route::post('v1/faceSearch','api/v1.qiniu/faceSearch');

// 人像资源路由
Route::resource(':version/face','api/:version.Face')->vars([
	':version/face' => 'id',
	':version/face' => 'group_id'
]);

// 图像库资源路由
Route::resource(':version/image','api/:version.Image')->vars([
	':version/image' => 'id',
	':version/image' => 'group_id'
]);

// 测试路由
Route::post('v1/test','api/v1.test/faceSearch');

// 测试资源路由
Route::resource(':version/test','api/:version.Test')->vars([
	':version/test' => 'id',
	':version/test' => 'group_id'
]);

// 所有路由匹配不到情况下触发该路由
Route::miss('\app\api\controller\Exception::miss');




