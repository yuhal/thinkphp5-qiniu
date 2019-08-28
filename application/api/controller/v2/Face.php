<?php

namespace app\api\controller\v2;

use think\Controller;
use think\Db;
use think\Request;
use think\Cache;
use app\api\controller\Send;
use app\api\controller\Base;
use app\api\validate\face\Save;
use app\api\validate\face\Read;
use app\api\validate\face\Update;
use qiniu\QiniuSdk;

class Face extends Base
{
    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(Request $request){
        parent::__construct($request);
        $this->qiniuSdk = new QiniuSdk(config('qiniu.'));
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
         echo 'index';exit;
        $this->qiniu->listFaceGroup();
        return json_decode($response->body,true);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        echo 'create';exit;
        
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $validate = new Save();
        //参数验证
        if(!$validate->check(input('post.'))){
            return self::returnMsg(401,$validate->getError());
        }
        $arguments['group_id'] = input('post.id'); 
        $arguments['uri'] = input('post.uri'); 
        return self::returnMsg(200,'success',$this->qiniuSdk->newFaceGroup($arguments));
    }

    /**
     * 人脸搜索 
     *
     * @param  string  $id 指定的人脸图像库
     * @return \think\Response
     */
    public function read($id)
    {
        $validate = new Read();
        //参数验证
        if(!$validate->check(input('get.'))){
            return self::returnMsg(401,$validate->getError());
        }
        // 列出该用户下所有的图像库
        $faceGroupList = $this->qiniuSdk->listFaceGroup();
        if(isset($faceGroupList['result']) && !in_array($id, $faceGroupList['result'])){
            self::returnMsg(401, '该图像库不存在！');
        }
        // 要搜索的头像地址
        $arguments['uri'] = input('get.uri');
        // 要搜索的人脸仓库
        $arguments['groups'] = input('get.groups');
        $chcheKey = md5(json_encode($arguments));
        $faceGroupSearch = cache('FaceReadFaceGroupSearch_'.$chcheKey);
        if(!$faceGroupSearch){
            $faceGroupSearch = $this->qiniuSdk->faceGroupSearch($arguments);
            cache('FaceReadFaceGroupSearch_'.$chcheKey, $faceGroupSearch, 3600*24*7);
        }
        return self::returnMsg(200,'success',$faceGroupSearch);
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
        $validate = new Update();
        //参数验证
        if(!$validate->check(input('put.'))){
            return self::returnMsg(401,$validate->getError());
        }
        $arguments['group_id'] = $id; 
        $arguments['uri'] = input('put.uri'); 
        return self::returnMsg(200,'success',$this->qiniuSdk->updateFaceGroup($arguments));
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