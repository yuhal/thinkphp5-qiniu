<?php

namespace app\api\controller\v2;

use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

class Qiniu 
{
    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(Request $request){
        parent::__construct($request);
        $this->Validate = new Validate();
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
        //参数验证
        if(!$this->Validate->scene(request()->action())->check(input('get.'))){
            return self::returnMsg(401,$this->Validate->getError());
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
        if(isset($listFiles[0]['items'])){
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
            $listFiles = object_to_array($listFiles[1]);
            return self::returnMsg(500,'fail',json_decode($listFiles['response']->body,true));
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
        //参数验证
        if(!$this->Validate->scene(request()->action())->check(input('put.'))){
            return self::returnMsg(401,$this->Validate->getError());
        }
        $config = array_merge(config('qiniu.'),['bucket'=>$id]);
        $qiniuSdk = new QiniuSdk($config);
        $imgBase64 = input('put.uri');
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/',$imgBase64,$res)) {
            //获取图片类型   
            $type = $res[2];
            //图片保存路径
            $fileDir = "/mnt/avatar/".date('Ymd',time()).'/';
            if (!file_exists($fileDir)) {
               mkdir($fileDir,0777,true);
            }
            $fileName = $id.'-'.time().'.'.$type;
            $filePath = $fileDir.$fileName;
            if (file_put_contents($filePath,base64_decode(str_replace($res[1],'', $imgBase64)))) {
                //图片名字
                $arguments['file'] = $fileName;
                $arguments['filepath'] = $filePath;
                $putFileRe = $qiniuSdk->putFile($arguments);
                if($putFileRe){
                    $result['fileName'] = $putFileRe;
                    //删除对应目录的文件
                    unlink($filePath);
                    return self::returnMsg(200,'success',$result);
                }
                return self::returnMsg(500,'fail','图片上传失败');
            }else{
                return self::returnMsg(500,'fail','图片地址不存在');
            }
        }else{
            return self::returnMsg(500,'fail','图片解析失败');
        }
        
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