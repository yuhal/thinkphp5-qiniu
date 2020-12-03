<?php
namespace app\api\validate\oauth;

use think\Validate;

/**
 * 生成token参数验证器
 */
class Authentication extends Validate
{
    protected $rule = [
        'appid'       =>  'require',
        'uid'      =>  'require',
        'access_token'      =>  'require',
        'refresh_token' =>  'require'
    ];

    protected $scene = [
        'index'  =>  ['appid','uid','access_token'],
        'refresh'  =>  ['appid','refresh_token'],
    ];
}
