<?php
namespace app\api\validate;

use think\Validate;
/**
 * 生成token参数验证器
 */
class Qiniu extends Validate
{

    protected $rule = [
        'bucket'       =>  'require',
        'limit'       =>  'number|min:1',
        'uri'       =>  'require',
    ];

    protected $message  =   [
        'bucket.require'    => 'bucket不能为空',
        'uri.require'    => 'uri不能为空',
    ];

    protected $scene = [
        'read'  =>  ['limit'],
        'update'  =>  ['uri'],
    ];
}