<?php
namespace app\api\validate\v1;

use think\Validate;

/**
 * 生成token参数验证器
 */
class Face extends Validate
{
    protected $rule = [
        'uri'       =>  'require',
        'groups'       =>  'require',
        'id'       =>  'require',
    ];
    
    protected $scene = [
        'read'  =>  ['uri','groups'],
        'save'  =>  ['id','uri'],
        'update'  =>  ['uri'],
        'index'  =>  ['uri'],
    ];
}
