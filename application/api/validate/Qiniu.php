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
        'source_bucket'       =>  'require',
        'key_pairs'       =>  'require',
        'target_bucket'       =>  'require',
        'limit'       =>  'number|min:1',
        'uri'       =>  'require',
    ];

    protected $message  =   [
        'bucket.require'    => 'bucket 不能为空',
        'source_bucket.require'    => 'source_bucket 不能为空',
        'key_pairs.require'    => 'key_pairs 不能为空',
        'target_bucket.require'    => 'target_bucket 不能为空',
        'uri.require'    => 'uri 不能为空',
    ];

    public function sceneListFiles()
    {
        return $this->only(['limit']);
    }

    public function sceneBuildBatchMove()
    {
        return $this->only(['source_bucket','target_bucket']);
    }
}
