<?php
namespace app\api\validate\bucket;

use think\Validate;
/**
 * 生成token参数验证器
 */
class Update extends Validate
{

    protected $rule = [
        'uri'       =>  'require',
    ];
    protected $message  =   [
        'uri.require'    => 'uri不能为空',
    ];
}