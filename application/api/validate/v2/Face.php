<?php
namespace app\api\validate\v2;

use think\Validate;

/**
 * 生成token参数验证器
 */
class Face extends Validate
{
    protected $rule = [
        'group_id'       =>  'require',
        'groups'       =>  'require',
        'faces'       =>  'require',
        'id'       =>  'require',
    ];

    protected $scene = [
        'read'  =>  ['id'],
        'index'  =>  ['group_id'],
        'delete'  =>  ['faces'],
    ];
}
