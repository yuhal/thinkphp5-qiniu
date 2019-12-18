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

<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 1c66084c165e2af191ad403b9a786af1dd651098
    // protected $scene = [
    //     'edit'  =>  ['limit'],
    //     'update'  =>  ['uri'],
    // ];

    // ListFile 验证场景定义
    public function sceneListFiles()
    {
        return $this->only(['limit']);
    }  
<<<<<<< HEAD
=======
=======
    protected $scene = [
        'read'  =>  ['limit'],
        'update'  =>  ['uri'],
    ];
>>>>>>> baf5a48637d8179d8b9881c3ba1f8ea1eb9c71cc
>>>>>>> 1c66084c165e2af191ad403b9a786af1dd651098
}