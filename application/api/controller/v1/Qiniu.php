<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Request;
use app\api\controller\Send;
use app\api\controller\Api;
use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use app\api\validate\v1\Qiniu as Validate;

class Qiniu extends Api
{
    protected $auth = null;

    protected $bucketMgr = null;

    protected $client = null;

    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(
        Request $request,
        Client $client,
        Validate $validate
    ){
        parent::__construct($request);
        
        $this->auth = new Auth(config('qiniu.accessKey'), config('qiniu.secretKey'));
        $this->bucketMgr = new BucketManager($this->auth);
        $this->client = $client;
        $this->validate = $validate;
    }

    /**
     * 获取指定账号下所有的空间名。
     *
     * @return \think\Response
     */
    public function buckets()
    {
        $buckets = $this->bucketMgr->buckets(config('qiniu.shared'));
        return self::returnMsg(200, 'success', $buckets);
    }

    /**
     * 批量移动或重命名文件
     *
     * @return \think\Response
     */
    public function buildBatchMove()
    {
        //参数验证
        if (!$this->validate->sceneBuildBatchMove()->check(input(''))) {
            return self::returnMsg(401, $this->validate->getError());
        }

        // 列出该用户下所有的空间
        $buckets = $this->bucketMgr->buckets(config('qiniu.shared'));
        if (!in_array(input('source_bucket'), $buckets[0])) {
            self::returnMsg(404, '原空间不存在！');
        }
        if (!in_array(input('target_bucket'), $buckets[0])) {
            self::returnMsg(404, '目标空间不存在！');
        }

        // 列出原空间的文件列表
        $listFiles = $this->bucketMgr->listFiles(input('source_bucket'));
        if (isset($listFiles[0]['items'])) {
            $keys = array_column($listFiles[0]['items'], 'key');
            $keyPairs = array();
            foreach ($keys as $key) {
                $keyPairs[$key] = $key;
            }
        }
        $ops = $this->bucketMgr->buildBatchMove(input('source_bucket'), $keyPairs, input('target_bucket'), true);
        list($ret, $err) = $this->bucketMgr->batch($ops);
        if ($err) {
            return self::returnMsg(500, 'fail', '批量操作失败');
        } else {
            return self::returnMsg(200, 'success', '批量操作成功');
        }
    }

    /**
     * 获取指定空间绑定的所有的域名
     * 
     * @param  string  $bucket
     * @return \think\Response
     */
    public function domains($bucket)
    {
        // 获取指定账号下所有的空间名。
        $buckets = $this->bucketMgr->buckets(config('qiniu.shared'));
        if (!in_array($bucket, $buckets[0])) {
            self::returnMsg(404, '该空间不存在！');
        }

        $domains = $this->bucketMgr->domains($bucket);
        return self::returnMsg(200, 'success', $domains);
    }

    /**
     * 删除指定资源
     * 
     * @return \think\Response
     */
    public function delete()
    {
        //参数验证
        if (!$this->validate->sceneDelete()->check(input(''))) {
            return self::returnMsg(401, $this->validate->getError());
        }

        // 获取指定账号下所有的空间名。
        $buckets = $this->bucketMgr->buckets(config('qiniu.shared'));
        if (!in_array(input('bucket'), $buckets[0])) {
            self::returnMsg(404, '该空间不存在！');
        }

        $delete = $this->bucketMgr->delete(input('bucket'), input('key'));
        return self::returnMsg(200, 'success', $delete);
    }

    /**
     * 给资源进行重命名
     * 
     * @return \think\Response
     */
    public function rename()
    {
        // 参数验证
        if (!$this->validate->sceneRename()->check(input(''))) {
            return self::returnMsg(401, $this->validate->getError());
        }

         // 获取指定账号下所有的空间名。
        $buckets = $this->bucketMgr->buckets(config('qiniu.shared'));
        if (!in_array(input('bucket'), $buckets[0])) {
            self::returnMsg(404, '该空间不存在！');
        }
        
        $rename = $this->bucketMgr->rename(input('bucket'), input('oldname'), input('newname'));
        return self::returnMsg(200, 'success', $rename);
    }

    /**
     * 获取指定空间的文件列表。
     *
     * @param  string  $bucket
     * @return \think\Response
     */
    public function listFiles()
    {
        // 参数验证
        if (!$this->validate->sceneListFiles()->check(input(''))) {
            return self::returnMsg(401, $this->validate->getError());
        }

        // 指定的空间名
        $arguments['bucket'] = (input('?bucket') == true) ? input('bucket') : config('qiniu.bucket');
  
        // 获取指定账号下所有的空间名。
        $buckets = $this->bucketMgr->buckets(config('qiniu.shared'));
        if (!in_array($arguments['bucket'], $buckets[0])) {
            self::returnMsg(404, '该空间不存在！');
        }
        
        // 要列取文件的公共前缀
        $arguments['prefix'] = '';
        if (input('prefix')) {
            $arguments['prefix'] = input('prefix');
        }
        // 上次列举返回的位置标记，作为本次列举的起点信息。
        $arguments['marker'] = '';
        if (input('marker')) {
            $arguments['marker'] = input('marker');
        }
        // 本次列举的条目数
        $arguments['limit'] = '';
        if (input('limit')) {
            $arguments['limit'] = input('limit');
        }
        // 分隔符
        $arguments['delimiter'] = '';
        if (input('delimiter')) {
            $arguments['delimiter'] = input('delimiter');
        }

        $chcheKey = md5(json_encode($arguments));
        $listFiles = cache('BucketReadListFiles_'.$chcheKey);
        if (!$listFiles) {
            $listFiles = $this->bucketMgr->listFiles($arguments['bucket'], $arguments['prefix'], $arguments['marker'], $arguments['limit'], $arguments['delimiter']);
            cache('BucketReadListFiles_'.$chcheKey, $listFiles, 3600*24);
        }
        if (isset($listFiles[0]['items'])) {
            // 排序
            switch (input('order')) {
                case 1:
                    // 按照添加时间正序
                    $items = arraySort($listFiles[0]['items'], 'putTime');
                    break;
                case 2:
                    // 按照添加时间倒序
                    $items = arraySort($listFiles[0]['items'], 'putTime', 'desc');
                    break;
                default:
                    // 按照添加时间倒序
                    $items = arraySort($listFiles[0]['items'], 'putTime', 'desc');
                    break;
            }
            foreach ($items as $key => $value) {
                $items[$key]['putDate'] = date('Y-m-d',substr($value['putTime'], 0, 10));
            }
            $listFiles[0]['items'] = arrayKeyAsc($items);
            return self::returnMsg(200, 'success', $listFiles);
        } else {
            $listFiles = object_to_array($listFiles[1]);
            return self::returnMsg(500, 'fail', json_decode($listFiles['response']->body, true));
        }
    }

    /**
     * 将资源从一个空间到另一个空间
     *
     * @return \think\Response
     */
    public function move()
    {
        // 参数验证
        if (!$this->validate->sceneMove()->check(input(''))) {
            return self::returnMsg(401, $this->validate->getError());
        }

        // 获取指定账号下所有的空间名。
        $buckets = $this->bucketMgr->buckets(config('qiniu.shared'));
        if (!in_array(input('from_bucket'), $buckets[0])) {
            self::returnMsg(404, '待操作资源所在空间不存在！');
        }
        if (!in_array(input('to_bucket'), $buckets[0])) {
            self::returnMsg(404, '目标资源空间名不存在！');
        }

        $move = $this->bucketMgr->move(input('from_bucket'), input('from_key'), input('to_bucket'), input('to_key'));
        return self::returnMsg(200, 'success', $move);
    } 

    /**
     * 上传文件到七牛
     *
     * @return \think\Response
     */
    public function putFile()
    { 

        $bucket = (input('?bucket') == true) ? input('bucket') : config('qiniu.bucket');
        // 获取指定账号下所有的空间名。
        $buckets = $this->bucketMgr->buckets(config('qiniu.shared'));
        if (!in_array($bucket, $buckets[0])) {
            self::returnMsg(404, '该资源空间不存在！');
        }

        $arguments['upToken'] = $this->auth->uploadToken($bucket);
        // 参数验证
        if ($this->validate->scenePutFile()->check(request()->file())) {
            $file = request()->file('file');
            $fileInfo = $file->move(config('qiniu.updir'));
            $arguments['key'] = $bucket.'-'.uniqid().'.'.$fileInfo->getExtension();
            $arguments['filePath'] = config('qiniu.updir').$fileInfo->getSaveName();
        }elseif ($this->validate->scenePutFile()->check(input())) {
            $imgBase64 = input('file');
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $imgBase64, $res)) {
                // 图片类型
                $type = $res[2];
                // 图片保存路径
                $arguments['key'] = $bucket.'-'.uniqid().'.'.$type;
                $arguments['filePath'] = config('qiniu.updir').$arguments['key'];
                if (!file_put_contents($arguments['filePath'], base64_decode(str_replace($res[1], '', $imgBase64)))) {
                    return self::returnMsg(401, 'fail', '图片上传失败');
                } 
            }else{
                return self::returnMsg(401, 'fail', '图片解析失败');
            }
        }else{
            return self::returnMsg(401, $this->validate->getError());
        }
          
        if(file_exists($arguments['filePath'])){
            $uploadMgr = new UploadManager();
            $putFile = $uploadMgr->putFile($arguments['upToken'], $arguments['key'], $arguments['filePath']);
            return self::returnMsg(200, 'success', $putFile);
        }else{
            return self::returnMsg(404, 'fail', '图片地址不存在');
        }
    } 

    /**
     * 析构方法
     * @param Request $request Request对象
     */
    public function __destruct()
    {
        $this->auth = null;
        $this->bucketMgr = null;
        $this->client = null;
        $this->validate = null;
    }
}
