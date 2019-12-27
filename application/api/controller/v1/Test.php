<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Db;
use think\Request;
use think\Cache;
use app\api\controller\Send;
use app\api\controller\Api;
use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use app\api\validate\v1\Image as Validate;

class Test extends Api
{

    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        
        $this->validate = new Validate();
    }

    /**
     * 删除人像库
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        // 列出该用户下所有的人像库
        $url = "http://ai.qiniuapi.com/v1/face/group";
        $group = qiniuGet($url);
        if (!in_array($id, $group['result'])) {
            self::returnMsg(404, '该人像库不存在！');
        }

        qiniuPost("http://ai.qiniuapi.com/v1/face/group/".$id."/remove");
        return self::returnMsg(200, 'success', '删除人像库成功');
    }

    /**
     * 显示所有人像库  
     *
     * @return \think\Response
     */
    public function index()
    {
        $url = "http://ai.qiniuapi.com/v1/face/group";
        $group = qiniuGet($url);
        return self::returnMsg(200, 'success', $group);
    }

    /**
     * 显示指定人像库信息
     *
     * @param  string  $id 指定的人像库
     * @return \think\Response
     */
    public function read($id)
    {   
        $url = "http://ai.qiniuapi.com/v1/face/group/".$id."/info";
        $info = qiniuGet($url);
        return self::returnMsg(200, 'success', $info);
    }

    /**
     * 新建人像库
     *
     * @return \think\Response
     */
    public function save(Request $request)
    {
        // 参数验证
        if (!$this->validate->scene(request()->action())->check(input(''))) {
            return self::returnMsg(401, $this->validate->getError());
        }
        $url = "http://ai.qiniuapi.com/v1/face/group/".input('group_id')."/new";
        $arr = [];
        foreach (input('uri') as $k => $v) {
            $arr['data'][$k]['uri'] = $v;
            if(is_array(input('attribute'))){
                foreach (input('attribute') as $k2 => $v2) {
                    if ($k == $k2) {
                        $arr['data'][$k]['attribute'] = $v2;
                    }
                }   
            }
        }
        $new = qiniuPost($url,$arr);
        return self::returnMsg(200, 'success', $new);
    }

    /**
     * 添加人脸  
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        // 列出该用户下所有的人像库
        $url = "http://ai.qiniuapi.com/v1/face/group";
        $group = qiniuGet($url);
        if (!in_array($id, $group['result'])) {
            self::returnMsg(404, '该人像库不存在！');
        }

        // 参数验证
        if (!$this->validate->scene(request()->action())->check(input(''))) {
            return self::returnMsg(401, $this->validate->getError());
        }
        
        $url = "http://ai.qiniuapi.com/v1/face/group/".$id."/add";
        $arr = [];
        foreach (input('uri') as $k => $v) {
            $arr['data'][$k]['uri'] = $v;
            if(is_array(input('attribute'))){
                foreach (input('attribute') as $k2 => $v2) {
                    if ($k == $k2) {
                        $arr['data'][$k]['attribute'] = $v2;
                    }
                }   
            }
        }
        $add = qiniuPost($url,$arr);
        return self::returnMsg(200, 'success', $add);
    }

}
