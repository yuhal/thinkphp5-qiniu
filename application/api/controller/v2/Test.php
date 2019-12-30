<?php

namespace app\api\controller\v2;

use think\Request;
use app\api\controller\Send;
use app\api\controller\Api;
use app\api\validate\v2\Face as Validate;

class Test extends Api
{

    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(
        Request $request,
        Validate $validate
    ){
        parent::__construct($request);
        
        $this->request = $request;
        $this->validate = $validate;
    }

    /**
     * 删除人脸
     *
     * @param  int  $group_id
     * @return \think\Response
     */
    public function delete($group_id)
    {
        // 参数验证
        if (!$this->validate->scene(request()->action())->check(input())) {
            return self::returnMsg(401, $this->validate->getError());
        }

        $url = "http://ai.qiniuapi.com/v1/face/group/".$group_id."/delete";
        $delete = qiniuPost($url, input());
        return self::returnMsg(200, 'success', $delete);
    }

    /**
     * 显示所有人脸     
     *
     * @return \think\Response
     */
    public function index()
    {
        // 参数验证
        if (!$this->validate->scene(request()->action())->check(input())) {
            return self::returnMsg(401, $this->validate->getError());
        }

        $url = "http://ai.qiniuapi.com/v1/face/group/".input('group_id');
        $group = qiniuGet($url,$this->request->only('marker,limit'));
        return self::returnMsg(200, 'success', $group);
    }

    /**
     * 显示指定人脸 信息 
     *
     * @param  string  $group_id 人像库的唯一标识
     * @return \think\Response
     */
    public function read($group_id)
    {   
        // 参数验证
        if (!$this->validate->scene(request()->action())->check(input())) {
            return self::returnMsg(401, $this->validate->getError());
        }

        $url = "http://ai.qiniuapi.com/v1/face/group/".$group_id."/face";
        $face = qiniuPost($url,$this->request->only('id'));
        return self::returnMsg(200, 'success', $face);
    }

    /**
     * 析构方法
     * @param Request $request Request对象
     */
    public function __destruct()
    {
        $this->request = null;
        $this->validate = null;
    }

}
