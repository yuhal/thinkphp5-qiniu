<?php
namespace app\api\validate;

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

    protected $message  =   [
        'uri.require'    => 'uri不能为空',
        'groups.require'    => 'groups不能为空',
        'id.require'    => 'id不能为空',
    ];

    protected $scene = [
        'read'  =>  ['uri','groups'],
        'save'  =>  ['id','uri'],
        'update'  =>  ['uri'],
        'index'  =>  ['uri'],
    ];
}