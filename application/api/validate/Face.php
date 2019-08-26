<?php
namespace app\api\validate;

use think\Validate;
/**
 * 生成token参数验证器
 */
class Face extends Validate
{

    protected $rule = [
        'id'       =>  'require',
    ];
    protected $message  =   [
        'id.require'    => 'id不能为空',
    ];
}