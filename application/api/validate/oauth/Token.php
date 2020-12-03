<?php
namespace app\api\validate\oauth;

use think\Validate;

/**
 * 生成token参数验证器
 */
class Token extends Validate
{
    protected $rule = [
        'appid'       =>  'require',
        'mobile'      =>  'mobile|require',
        'nonce'       =>  'require',
        'timestamp'   =>  'number|require',
        'sign'        =>  'require'
    ];
}
