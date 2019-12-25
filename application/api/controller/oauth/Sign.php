<?php

namespace app\api\controller\oauth;

use app\api\controller\Send;
use app\api\validate\oauth\Sign as validate;
use app\api\controller\Oauth;
use think\Controller;
use think\Request;

class Sign extends Controller
{
    use Send;
    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct()
    {
        parent::__construct();
        $this->validate = new validate();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //参数验证
        if (!$this->validate->check(input(''))) {
            return self::returnMsg(401, $this->validate->getError());
        }
        $params = input('');
        unset($params['appsercet']);
        $sign = Oauth::makeSign($params, input('appsercet'));
        return self::returnMsg(200, 'success', $sign);
    }
}
