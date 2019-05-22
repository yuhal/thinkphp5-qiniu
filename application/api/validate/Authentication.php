<?php
namespace app\api\validate;

use think\Validate;
/**
 * 生成token参数验证器
 */
class Authentication extends Validate
{

    protected $rule = [
        'appid'       =>  'require',
        'uid'      =>  'require',
        'accesstoken'      =>  'require',
    ];
    protected $message  =   [
        'appid.require'    => 'appid不能为空',
        'uid.require'    => 'uid不能为空',
        'accesstoken.require'    => 'accesstoken不能为空',

    ];
}