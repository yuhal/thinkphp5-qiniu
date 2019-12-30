<?php
namespace app\api\validate\v2;

use think\Validate;

/**
 * 生成token参数验证器
 */
class Image extends Validate
{
    protected $rule = [
        'group_id'       =>  'require',
        'image_id'       =>  'require',
        'images'       =>  'require',
        'uri'       =>  'require',
        'id'    =>  'require',
    ];

    protected $scene = [
        'read'  =>  ['id'],
        'index'  =>  ['group_id'],
        'delete'  =>  ['images'],
    ];
}
