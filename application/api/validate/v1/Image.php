<?php
namespace app\api\validate\v1;

use think\Validate;

/**
 * 生成token参数验证器
 */
class Image extends Validate
{
    protected $rule = [
        'group_id'       =>  'require',
        'uri'       =>  'require',
    ];

    protected $scene = [
        'read'  =>  ['uri','groups'],
        'save'  =>  ['group_id','uri'],
        'update'  =>  ['uri'],
        'index'  =>  ['uri'],
    ];
}
