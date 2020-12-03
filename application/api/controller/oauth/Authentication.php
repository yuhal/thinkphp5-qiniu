<?php

namespace app\api\controller\oauth;

use think\Controller;
use app\api\controller\Send;
use app\api\validate\oauth\Authentication as AuthenticationValidate;

class Authentication extends Controller
{
    use Send;

    /**
     * 获取authorization
     * @param AuthenticationValidate $authenticationValidate
     * @return \think\Response
     */
    public function index(AuthenticationValidate $authenticationValidate)
    {
        $params = input('get.');
        //参数验证
        if (!$authenticationValidate->scene('index')->check($params)) {
            return self::returnMsg(401, $authenticationValidate->getError());
        }
        $base = $params['appid'].':'.$params['access_token'].':'.$params['uid'];
        $result['authorization'] = $params['uid']." ".base64_encode($base);
        return self::returnMsg(200, 'success', $result);
    }
}
