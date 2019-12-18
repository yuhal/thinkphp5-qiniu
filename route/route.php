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

<<<<<<< HEAD
// 获取单个七牛仓库的文件列表
Route::get(':version/listFiles/:bucket','api/:version.qiniu/listFiles')->pattern(['bucket' => '[\w-]+']);

=======
<<<<<<< HEAD
Route::get(':version/listFiles/:bucket','api/:version.qiniu/listFiles')->pattern(['bucket' => '[\w-]+']);
=======
Route::get(':version/listFile/:bucket','api/:version.qiniu/listFile');
>>>>>>> baf5a48637d8179d8b9881c3ba1f8ea1eb9c71cc
>>>>>>> 1c66084c165e2af191ad403b9a786af1dd651098
