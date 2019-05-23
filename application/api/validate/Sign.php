<?php
namespace app\api\validate;

use think\Validate;
/**
 * 生成token参数验证器
 */
class Sign extends Validate
{

    protected $rule = [
        'appid'       =>  'require',
        'appsercet'       =>  'require',
        'mobile'      =>  'mobile|require',
        'timestamp'      =>  'number|require',
        'nonce'      =>  'number|require',
    ];
    protected $message  =   [
        'appid.require'    => 'appid不能为空',
        'mobile.mobile'    => '手机格式错误',
    ];
}