<?php
namespace app\api\validate\v1;

use think\Validate;

/**
 * 生成token参数验证器
 */
class Bucket extends Validate
{
    protected $rule = [
        'id'       =>  'require',
        'limit'       =>  'number|min:1',
        'uri'       =>  'require',
    ];

    protected $message  =   [
        'id.require'    => 'id不能为空',
        'uri.require'    => 'uri不能为空',
    ];

    protected $scene = [
        'read'  =>  ['limit'],
        'update'  =>  ['uri'],
    ];
}
