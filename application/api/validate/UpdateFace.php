<?php
namespace app\api\validate;

use think\Validate;
/**
 * 生成token参数验证器
 */
class UpdateFace extends Validate
{

    protected $rule = [
        'uri'       =>  'require',
    ];
    protected $message  =   [
        'uri.require'    => 'uri不能为空',
    ];
}