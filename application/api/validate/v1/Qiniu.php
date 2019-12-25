<?php
namespace app\api\validate\v1;

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
        'oldname'   =>  'require',
        'newname'   =>  'require',
        'key'       =>  'require',
        'from_bucket'       =>  'require',
        'from_key'       =>  'require',
        'to_bucket'       =>  'require',
        'to_key'       =>  'require',
        'upToken'   =>  'require',
        'key'   =>  'require',
        'filePath'   =>  'require',
    ];

    protected $message  =   [
        
    ];

    public function sceneBuildBatchMove()
    {
        return $this->only(['source_bucket','target_bucket']);
    }

    public function sceneRename()
    {
        return $this->only(['bucket','oldname','newname']);
    }

    public function sceneDelete()
    {
        return $this->only(['bucket','key']);
    }

    public function sceneListFiles()
    {
        return $this->only(['limit']);
    }

    public function sceneMove()
    {
        return $this->only(['from_bucket','from_key','to_bucket','to_key']);
    }

    public function scenePutFile()
    {
        return $this->only(['upToken','key','filePath']);
    }
    
}
