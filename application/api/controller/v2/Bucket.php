<?php

namespace app\api\controller\v2;

use think\Controller;
use think\Db;
use think\Request;
use think\Cache;
use app\api\controller\Send;
use app\api\controller\Base;
use app\api\validate\ReadBucket as ValidateRead;
use app\api\validate\UpdateBucket as ValidateUpdate;
use qiniu\QiniuSdk;

class Bucket extends Base
{
    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(Request $request){
        parent::__construct($request);
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        echo 'index';
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        echo "create";
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        dump($this->uid);
        echo "save";
    }

    /**
     * 显示指定的资源
     *
     * @param  string  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $validate = new ValidateRead();
        //参数验证
        if(!$validate->check(input(''))){
            return self::returnMsg(401,$validate->getError());
        }
        $config = array_merge(config('qiniu.'),['bucket'=>$id]);
        $qiniuSdk = new QiniuSdk($config);
        // 列出该用户下所有的仓库
        $arguments['shared'] = $config['shared'];
        $buckets = $qiniuSdk->buckets();
        if(!in_array($id, $buckets[0])){
            self::returnMsg(401, '该仓库不存在！');
        }
        // 要列取文件的公共前缀
        $arguments['prefix'] = '';
        if(input('prefix')) $arguments['prefix'] = input('prefix');
        // 上次列举返回的位置标记，作为本次列举的起点信息。
        $arguments['marker'] = '';
        if(input('marker')) $arguments['marker'] = input('marker');
        // 本次列举的条目数
        $arguments['limit'] = '';
        if(input('limit')) $arguments['limit'] = input('limit');
        // 分隔符
        $arguments['delimiter'] = '';
        if(input('delimiter')) $arguments['delimiter'] = input('delimiter');

        $chcheKey = md5(json_encode($arguments));
        $listFiles = cache('BucketReadListFiles_'.$chcheKey);
        if(!$listFiles){
            $listFiles = $qiniuSdk->listFiles($arguments);
            cache('BucketReadListFiles_'.$chcheKey, $listFiles, 3600*24);
        }
        if($listFiles){
            // 排序
            switch (input('order')) {
                case 1:
                    // 按照添加时间正序
                    $items = arraySort($listFiles[0]['items'],'putTime');
                    break;
                case 2:
                    // 按照添加时间倒序
                    $items = arraySort($listFiles[0]['items'],'putTime','desc');
                    break;
                default:
                    // 按照添加时间倒序
                    $items = arraySort($listFiles[0]['items'],'putTime','desc');
                    break;
            }
            $listFiles[0]['items'] = arrayKeyAsc($items);
            return self::returnMsg(200,'success',$listFiles);
        }else{
            return self::returnMsg(500,'fail',$listFiles);
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        echo "edit";
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $config = array_merge(config('qiniu.'),['bucket'=>$id]);
        $qiniuSdk = new QiniuSdk($config);
        $validate = new ValidateUpdate();
        //参数验证
        if(!$validate->check(input('put.'))){
            return self::returnMsg(401,$validate->getError());
        }
        $imgBase64 = input('put.uri');
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/',$imgBase64,$res)) {
            //获取图片类型   
            $type = $res[2];
            //图片保存路径
            $new_file = "/mnt/avatar/".date('Ymd',time()).'/';
            if (!file_exists($new_file)) {
               mkdir($new_file,0777,true);
            }
            $new_file = $new_file.time().'.'.$type;
            if (file_put_contents($new_file,base64_decode(str_replace($res[1],'', $imgBase64)))) {
                //图片名字
                $arguments['file'] = uniqid().'.'.$type;
                $arguments['filepath'] = $new_file;
                $putFileRe = $qiniuSdk->putFile($arguments);
                if($putFileRe){
                    $result['fileName'] = $putFileRe;
                    return self::returnMsg(200,'success',$result);
                }
            }
        }
        return self::returnMsg(500,'fail');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        echo "delete";
    }
    
}