<?php
namespace app\api\validate\face;

use think\Validate;
/**
 * 生成token参数验证器
 */
class Save extends Validate
{

    protected $rule = [
        'id'       =>  'require',
        'uri'       =>  'require',
    ];
    protected $message  =   [
        'id.require'    => 'id不能为空',
        'uri.require'    => 'uri不能为空',
    ];
}