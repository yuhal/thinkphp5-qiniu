<?php

namespace app\api\controller\oauth;

use think\Controller;
use app\api\controller\Send;
use app\api\controller\Oauth;
use app\api\validate\oauth\Sign as SignValidate;

class Sign extends Controller
{
    use Send;

    /**
     * 获取签名
     * @param SignValidate $signValidate
     * @return \think\Response
     */
    public function index(SignValidate $signValidate)
    {
        $params = input('get.');
        //参数验证
        if (!$signValidate->check($params)) {
            return self::returnMsg(401, $signValidate->getError());
        }
        unset($params['appsercet']);
        $sign = Oauth::makeSign($params, input('get.appsercet'));
        return self::returnMsg(200, 'success', ['sign'=>$sign]);
    }
}
