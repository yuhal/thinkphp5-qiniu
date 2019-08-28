<?php
namespace app\api\validate\face;

use think\Validate;
/**
 * 生成token参数验证器
 */
class Read extends Validate
{

    protected $rule = [
        'uri'       =>  'require',
        'groups'       =>  'require',
    ];
    protected $message  =   [
        'uri.require'    => 'uri不能为空',
        'groups.require'    => 'groups不能为空',
    ];
}