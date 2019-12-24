<?php

namespace app\api\controller\v2;

use think\Controller;
use think\Db;
use think\Request;
use think\Cache;
use app\api\controller\Send;
use app\api\controller\Api;
use app\api\validate\Face as Validate;
use qiniu\QiniuSdk;

class Face extends Api
{
    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->qiniuSdk = new QiniuSdk(config('qiniu.'));
        $this->Validate = new Validate();
    }
    /**
     * 人脸搜索
     *
     * @return \think\Response
     */
    public function index()
    {
        //参数验证
        if (!$this->Validate->scene(request()->action())->check(input('get.'))) {
            return self::returnMsg(401, $this->Validate->getError());
        }
        // 列出该用户下所有的图像库
        $faceGroupList = $this->qiniuSdk->listFaceGroup();
        if (!isset($faceGroupList['result'])) {
            return self::returnMsg(500, 'fail', $faceGroupList);
        }
        // 要搜索的头像地址
        $arguments['uri'] = input('get.uri');
        // 要搜索的人脸仓库
        $arguments['groups'] = $faceGroupList['result'];
        $chcheKey = md5(json_encode($arguments));
        $faceGroupSearch = cache('FaceIndexFaceGroupSearch_'.$chcheKey);
        if (!$faceGroupSearch) {
            $faceGroupSearch = $this->qiniuSdk->faceGroupSearch($arguments);
            cache('FaceIndexFaceGroupSearch_'.$chcheKey, $faceGroupSearch, 3600*24*7);
        }
        $maxScore = 0;
        if (isset($faceGroupSearch['result']['faces'][0]['faces']) && is_array($faceGroupSearch['result']['faces'][0]['faces'])) {
            $maxScore = intval(max(array_column($faceGroupSearch['result']['faces'][0]['faces'], 'score'))*100);
        }
        return self::returnMsg(200, 'success', $maxScore);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        echo 'create';
        exit;
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //参数验证
        if (!$this->Validate->scene(request()->action())->check(input('post.'))) {
            return self::returnMsg(401, $this->Validate->getError());
        }
        $arguments['group_id'] = input('post.id');
        $arguments['uri'] = input('post.uri');
        return self::returnMsg(200, 'success', $this->qiniuSdk->newFaceGroup($arguments));
    }

    /**
     * 显示指定人像库信息
     *
     * @param  string  $id 指定的人脸图像库
     * @return \think\Response
     */
    public function read($id)
    {
        $arguments['group_id'] = $id;
        $faceGroupInfo = $this->qiniuSdk->faceGroupInfo($arguments);
        return self::returnMsg(200, 'success', $faceGroupInfo);
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
        if (!$this->Validate->scene(request()->action())->check(input('put.'))) {
            return self::returnMsg(401, $this->Validate->getError());
        }
        $arguments['group_id'] = $id;
        $arguments['uri'] = input('put.uri');
        return self::returnMsg(200, 'success', $this->qiniuSdk->updateFaceGroup($arguments));
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
